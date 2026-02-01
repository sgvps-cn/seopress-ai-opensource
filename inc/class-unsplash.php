<?php
/**
 * 自动缩略图获取类
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Unsplash {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 获取单例实例
     */
    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {}
    
    /**
     * 根据关键词获取图片并设置为文章特色图片
     * 
     * @param int    $post_id  文章ID
     * @param string $keyword  关键词
     * @param int    $width    图片宽度
     * @param int    $height   图片高度
     * @return bool|int 成功返回附件ID，失败返回false
     */
    public function set_featured_image( int $post_id, string $keyword, int $width = 1200, int $height = 630 ) {
        // 尝试多个图片源
        $providers = array(
            array( $this, 'get_picsum_image' ),
            array( $this, 'get_placeholder_image' ),
        );
        
        foreach ( $providers as $provider ) {
            $image_url = call_user_func( $provider, $width, $height );
            
            if ( $image_url ) {
                $attachment_id = $this->download_and_attach( $image_url, $post_id, $keyword );
                
                if ( $attachment_id ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                    return $attachment_id;
                }
            }
        }
        
        return false;
    }
    
    /**
     * 使用 Lorem Picsum 获取随机图片
     */
    private function get_picsum_image( int $width, int $height ): string {
        // 使用随机种子避免重复
        $seed = time() . rand( 1000, 9999 );
        return "https://picsum.photos/seed/{$seed}/{$width}/{$height}";
    }
    
    /**
     * 使用 placeholder.com 生成占位图
     */
    private function get_placeholder_image( int $width, int $height ): string {
        // 随机颜色
        $colors = array( '3498db', '2ecc71', '9b59b6', 'e74c3c', 'f39c12', '1abc9c' );
        $bg = $colors[ array_rand( $colors ) ];
        return "https://via.placeholder.com/{$width}x{$height}/{$bg}/ffffff.jpg";
    }
    
    /**
     * 下载图片并创建附件
     */
    private function download_and_attach( string $image_url, int $post_id, string $keyword ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        // 生成文件名
        $filename = sanitize_file_name( $keyword ) . '-' . time() . '.jpg';
        
        // 下载图片
        $temp_file = download_url( $image_url, 30 );
        
        if ( is_wp_error( $temp_file ) ) {
            error_log( 'SeoPress AI: Failed to download image - ' . $temp_file->get_error_message() );
            return false;
        }
        
        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $temp_file,
        );
        
        $attachment_id = media_handle_sideload( $file_array, $post_id, $keyword );
        
        if ( file_exists( $temp_file ) ) {
            @unlink( $temp_file );
        }
        
        if ( is_wp_error( $attachment_id ) ) {
            error_log( 'SeoPress AI: Failed to create attachment - ' . $attachment_id->get_error_message() );
            return false;
        }
        
        update_post_meta( $attachment_id, '_seopress_auto_thumbnail', true );
        
        return $attachment_id;
    }
}
