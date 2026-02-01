<?php
/**
 * 百度SEO优化Sitemap增强
 * 
 * 添加lastmod、changefreq、priority等百度需要的字段
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Sitemap_Enhancer {
    
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
        // 增强WordPress默认sitemap
        add_filter( 'wp_sitemaps_posts_entry', array( $this, 'enhance_post_entry' ), 10, 3 );
        add_filter( 'wp_sitemaps_taxonomies_entry', array( $this, 'enhance_taxonomy_entry' ), 10, 3 );
        
        // 添加自定义百度sitemap路由
        add_action( 'init', array( $this, 'add_baidu_sitemap_rewrite' ) );
        add_action( 'template_redirect', array( $this, 'render_baidu_sitemap' ) );
        
        // 在robots.txt中添加百度sitemap
        add_filter( 'robots_txt', array( $this, 'add_baidu_sitemap_to_robots' ), 10, 2 );
    }
    
    /**
     * 增强文章sitemap条目
     */
    public function enhance_post_entry( array $entry, WP_Post $post, string $post_type ): array {
        // 添加最后修改时间
        $entry['lastmod'] = get_the_modified_date( 'c', $post );
        
        return $entry;
    }
    
    /**
     * 增强分类sitemap条目
     */
    public function enhance_taxonomy_entry( array $entry, $term, string $taxonomy ): array {
        // 获取分类下最新文章的修改时间
        $recent_post = get_posts( array(
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'tax_query'      => array(
                array(
                    'taxonomy' => $taxonomy,
                    'terms'    => $term->term_id,
                ),
            ),
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ) );
        
        if ( ! empty( $recent_post ) ) {
            $entry['lastmod'] = get_the_modified_date( 'c', $recent_post[0] );
        }
        
        return $entry;
    }
    
    /**
     * 添加百度专用sitemap重写规则
     */
    public function add_baidu_sitemap_rewrite(): void {
        add_rewrite_rule( '^baidu-sitemap\.xml$', 'index.php?baidu_sitemap=1', 'top' );
        add_rewrite_tag( '%baidu_sitemap%', '([0-9]+)' );
    }
    
    /**
     * 渲染百度专用sitemap
     */
    public function render_baidu_sitemap(): void {
        if ( ! get_query_var( 'baidu_sitemap' ) ) {
            return;
        }
        
        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex, follow' );
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // 首页
        echo $this->format_url_entry(
            home_url( '/' ),
            current_time( 'c' ),
            'daily',
            '1.0'
        );
        
        // 文章
        $posts = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1000,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ) );
        
        foreach ( $posts as $post ) {
            $days_since_modified = ( time() - strtotime( $post->post_modified ) ) / DAY_IN_SECONDS;
            
            // 根据修改时间动态计算changefreq
            if ( $days_since_modified < 1 ) {
                $changefreq = 'hourly';
            } elseif ( $days_since_modified < 7 ) {
                $changefreq = 'daily';
            } elseif ( $days_since_modified < 30 ) {
                $changefreq = 'weekly';
            } else {
                $changefreq = 'monthly';
            }
            
            // 根据文章新旧程度计算priority
            $days_since_publish = ( time() - strtotime( $post->post_date ) ) / DAY_IN_SECONDS;
            if ( $days_since_publish < 7 ) {
                $priority = '0.9';
            } elseif ( $days_since_publish < 30 ) {
                $priority = '0.8';
            } elseif ( $days_since_publish < 90 ) {
                $priority = '0.6';
            } else {
                $priority = '0.5';
            }
            
            echo $this->format_url_entry(
                get_permalink( $post ),
                get_the_modified_date( 'c', $post ),
                $changefreq,
                $priority
            );
        }
        
        // 分类
        $categories = get_categories( array( 'hide_empty' => true ) );
        foreach ( $categories as $category ) {
            echo $this->format_url_entry(
                get_category_link( $category->term_id ),
                '',
                'weekly',
                '0.7'
            );
        }
        
        // 标签
        $tags = get_tags( array( 'hide_empty' => true ) );
        foreach ( $tags as $tag ) {
            echo $this->format_url_entry(
                get_tag_link( $tag->term_id ),
                '',
                'weekly',
                '0.5'
            );
        }
        
        echo '</urlset>';
        exit;
    }
    
    /**
     * 格式化URL条目
     */
    private function format_url_entry( string $loc, string $lastmod = '', string $changefreq = '', string $priority = '' ): string {
        $output = "  <url>\n";
        $output .= "    <loc>" . esc_url( $loc ) . "</loc>\n";
        
        if ( $lastmod ) {
            $output .= "    <lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
        }
        
        if ( $changefreq ) {
            $output .= "    <changefreq>" . esc_html( $changefreq ) . "</changefreq>\n";
        }
        
        if ( $priority ) {
            $output .= "    <priority>" . esc_html( $priority ) . "</priority>\n";
        }
        
        $output .= "  </url>\n";
        
        return $output;
    }
    
    /**
     * 在robots.txt中添加百度sitemap
     */
    public function add_baidu_sitemap_to_robots( string $output, bool $public ): string {
        if ( $public ) {
            $output .= "\n# Baidu optimized sitemap\n";
            $output .= "Sitemap: " . home_url( '/baidu-sitemap.xml' ) . "\n";
        }
        return $output;
    }
}

// 初始化
SeoPress_AI_Sitemap_Enhancer::get_instance();
