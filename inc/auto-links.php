<?php
/**
 * 自动内链功能
 * 
 * 自动将文章中的关键词链接到相关文章
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Auto_Links {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 每篇文章最大内链数
     */
    private const MAX_LINKS_PER_POST = 5;
    
    /**
     * 同一关键词最大链接次数
     */
    private const MAX_SAME_KEYWORD_LINKS = 1;
    
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
        // 在内容中自动添加内链
        add_filter( 'the_content', array( $this, 'auto_add_internal_links' ), 15 );
    }
    
    /**
     * 获取内链关键词库
     * 
     * 从分类、标签和文章标题中提取关键词
     */
    private function get_keywords_map(): array {
        // 使用缓存
        $cache_key = 'seopress_ai_keywords_map';
        $keywords_map = wp_cache_get( $cache_key );
        
        if ( false !== $keywords_map ) {
            return $keywords_map;
        }
        
        $keywords_map = array();
        $current_post_id = get_the_ID();
        
        // 从分类获取关键词
        $categories = get_categories( array( 'hide_empty' => true ) );
        foreach ( $categories as $category ) {
            if ( mb_strlen( $category->name, 'UTF-8' ) >= 2 ) {
                $keywords_map[ $category->name ] = array(
                    'url'   => get_category_link( $category->term_id ),
                    'title' => $category->name . ' - 分类',
                    'type'  => 'category',
                );
            }
        }
        
        // 从标签获取关键词
        $tags = get_tags( array( 'hide_empty' => true ) );
        foreach ( $tags as $tag ) {
            if ( mb_strlen( $tag->name, 'UTF-8' ) >= 2 ) {
                $keywords_map[ $tag->name ] = array(
                    'url'   => get_tag_link( $tag->term_id ),
                    'title' => $tag->name . ' - 标签',
                    'type'  => 'tag',
                );
            }
        }
        
        // 从其他文章标题获取关键词（排除当前文章）
        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'post__not_in'   => $current_post_id ? array( $current_post_id ) : array(),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        
        foreach ( $posts as $post ) {
            $title = $post->post_title;
            // 只使用较短的标题作为关键词（避免匹配整个长标题）
            if ( mb_strlen( $title, 'UTF-8' ) >= 4 && mb_strlen( $title, 'UTF-8' ) <= 20 ) {
                $keywords_map[ $title ] = array(
                    'url'   => get_permalink( $post->ID ),
                    'title' => $title,
                    'type'  => 'post',
                );
            }
            
            // 提取标题中的关键短语（冒号前的部分）
            if ( strpos( $title, '：' ) !== false ) {
                $parts = explode( '：', $title );
                $keyword = trim( $parts[0] );
                if ( mb_strlen( $keyword, 'UTF-8' ) >= 4 && mb_strlen( $keyword, 'UTF-8' ) <= 15 ) {
                    $keywords_map[ $keyword ] = array(
                        'url'   => get_permalink( $post->ID ),
                        'title' => $title,
                        'type'  => 'post',
                    );
                }
            }
        }
        
        // 按关键词长度降序排序（优先匹配长关键词）
        uksort( $keywords_map, function( $a, $b ) {
            return mb_strlen( $b, 'UTF-8' ) - mb_strlen( $a, 'UTF-8' );
        } );
        
        // 缓存1小时
        wp_cache_set( $cache_key, $keywords_map, '', HOUR_IN_SECONDS );
        
        return $keywords_map;
    }
    
    /**
     * 自动添加内链
     */
    public function auto_add_internal_links( string $content ): string {
        if ( empty( $content ) || ! is_singular( 'post' ) ) {
            return $content;
        }
        
        $keywords_map = $this->get_keywords_map();
        if ( empty( $keywords_map ) ) {
            return $content;
        }
        
        $links_added = 0;
        $linked_keywords = array();
        
        foreach ( $keywords_map as $keyword => $data ) {
            // 检查是否达到最大链接数
            if ( $links_added >= self::MAX_LINKS_PER_POST ) {
                break;
            }
            
            // 检查关键词是否已链接
            if ( isset( $linked_keywords[ $keyword ] ) && $linked_keywords[ $keyword ] >= self::MAX_SAME_KEYWORD_LINKS ) {
                continue;
            }
            
            // 跳过当前文章的URL
            if ( $data['url'] === get_permalink() ) {
                continue;
            }
            
            // 构建正则表达式（避免匹配已有链接内的文字）
            $pattern = '/(?<!["\'>])(' . preg_quote( $keyword, '/' ) . ')(?![^<]*<\/a>)(?![^<]*>)/u';
            
            // 检查内容中是否存在该关键词
            if ( ! preg_match( $pattern, $content ) ) {
                continue;
            }
            
            // 替换第一个匹配（只链接一次）
            $replacement = '<a href="' . esc_url( $data['url'] ) . '" title="' . esc_attr( $data['title'] ) . '" class="auto-internal-link">$1</a>';
            
            $content = preg_replace( $pattern, $replacement, $content, 1, $count );
            
            if ( $count > 0 ) {
                $links_added++;
                $linked_keywords[ $keyword ] = isset( $linked_keywords[ $keyword ] ) ? $linked_keywords[ $keyword ] + 1 : 1;
            }
        }
        
        return $content;
    }
}

// 初始化
SeoPress_AI_Auto_Links::get_instance();
