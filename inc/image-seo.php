<?php
/**
 * 图片SEO优化器
 * 
 * 自动为缺少ALT属性的图片添加ALT
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Image_SEO {
    
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
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * 初始化钩子
     */
    private function init_hooks(): void {
        // 过滤文章内容中的图片
        add_filter( 'the_content', array( $this, 'auto_add_alt' ), 20 );
        
        // 过滤缩略图
        add_filter( 'post_thumbnail_html', array( $this, 'auto_add_thumbnail_alt' ), 10, 5 );
        
        // 上传图片时自动设置ALT
        add_action( 'add_attachment', array( $this, 'auto_set_attachment_alt' ) );
        
        // 添加懒加载
        add_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_lazy_loading' ), 10, 3 );
    }
    
    /**
     * 自动为内容中的图片添加ALT
     */
    public function auto_add_alt( string $content ): string {
        if ( empty( $content ) ) {
            return $content;
        }
        
        // 获取当前文章标题作为默认ALT
        $post_title = get_the_title();
        
        // 匹配所有img标签
        $content = preg_replace_callback(
            '/<img([^>]*?)>/i',
            function( $matches ) use ( $post_title ) {
                $img_tag = $matches[0];
                $attributes = $matches[1];
                
                // 检查是否已有非空ALT
                if ( preg_match( '/alt\s*=\s*["\']([^"\']+)["\']/i', $attributes, $alt_match ) ) {
                    // 已有非空ALT，不修改
                    return $img_tag;
                }
                
                // 尝试从文件名提取ALT
                $alt = $post_title;
                if ( preg_match( '/src\s*=\s*["\']([^"\']+)["\']/i', $attributes, $src_match ) ) {
                    $filename = pathinfo( $src_match[1], PATHINFO_FILENAME );
                    // 清理文件名（去除尺寸后缀如 -300x200）
                    $filename = preg_replace( '/-\d+x\d+$/', '', $filename );
                    // 将连字符和下划线替换为空格
                    $filename = str_replace( array( '-', '_' ), ' ', $filename );
                    if ( ! empty( $filename ) && strlen( $filename ) > 3 ) {
                        $alt = ucwords( $filename );
                    }
                }
                
                // 检查是否有空ALT
                if ( preg_match( '/alt\s*=\s*["\']["\']\s*/i', $attributes ) ) {
                    // 替换空ALT
                    $img_tag = preg_replace(
                        '/alt\s*=\s*["\']["\']\s*/i',
                        'alt="' . esc_attr( $alt ) . '"',
                        $img_tag
                    );
                } else {
                    // 添加ALT
                    $img_tag = str_replace( '<img', '<img alt="' . esc_attr( $alt ) . '"', $img_tag );
                }
                
                // 添加title属性（如果没有）
                if ( ! preg_match( '/title\s*=/i', $img_tag ) ) {
                    $img_tag = str_replace( 'alt="', 'title="' . esc_attr( $alt ) . '" alt="', $img_tag );
                }
                
                return $img_tag;
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * 自动为缩略图添加ALT
     */
    public function auto_add_thumbnail_alt( string $html, int $post_id, int $thumbnail_id, $size, $attr ): string {
        if ( empty( $html ) ) {
            return $html;
        }
        
        // 检查是否已有非空ALT
        if ( preg_match( '/alt\s*=\s*["\']([^"\']+)["\']/i', $html ) ) {
            return $html;
        }
        
        // 使用文章标题作为ALT
        $post_title = get_the_title( $post_id );
        $alt = $post_title . ' - 特色图片';
        
        // 替换或添加ALT
        if ( preg_match( '/alt\s*=\s*["\']["\']\s*/i', $html ) ) {
            $html = preg_replace(
                '/alt\s*=\s*["\']["\']\s*/i',
                'alt="' . esc_attr( $alt ) . '"',
                $html
            );
        } else {
            $html = str_replace( '<img', '<img alt="' . esc_attr( $alt ) . '"', $html );
        }
        
        return $html;
    }
    
    /**
     * 上传时自动设置附件ALT
     */
    public function auto_set_attachment_alt( int $attachment_id ): void {
        // 获取附件信息
        $attachment = get_post( $attachment_id );
        if ( ! $attachment || ! wp_attachment_is_image( $attachment_id ) ) {
            return;
        }
        
        // 检查是否已有ALT
        $existing_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        if ( ! empty( $existing_alt ) ) {
            return;
        }
        
        // 从文件名生成ALT
        $filename = pathinfo( get_attached_file( $attachment_id ), PATHINFO_FILENAME );
        $filename = preg_replace( '/-\d+x\d+$/', '', $filename );
        $filename = str_replace( array( '-', '_' ), ' ', $filename );
        $alt = ucwords( $filename );
        
        // 如果有标题，优先使用标题
        if ( ! empty( $attachment->post_title ) && $attachment->post_title !== $filename ) {
            $alt = $attachment->post_title;
        }
        
        // 设置ALT
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
    }
    
    /**
     * 添加懒加载属性
     */
    public function add_lazy_loading( array $attr, WP_Post $attachment, $size ): array {
        // 添加loading="lazy"
        if ( ! isset( $attr['loading'] ) ) {
            $attr['loading'] = 'lazy';
        }
        
        // 添加decoding="async"
        if ( ! isset( $attr['decoding'] ) ) {
            $attr['decoding'] = 'async';
        }
        
        return $attr;
    }
}

// 初始化
SeoPress_AI_Image_SEO::get_instance();
