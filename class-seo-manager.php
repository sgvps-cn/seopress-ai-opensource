<?php
/**
 * SEO 管理器
 * 
 * 处理所有 SEO 相关功能：Meta 标签、JSON-LD、Open Graph 等
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_SEO_Manager {
    
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
        // 移除 WordPress 默认的 title 标签
        remove_action( 'wp_head', '_wp_render_title_tag', 1 );
        
        // 添加自定义 SEO Meta 标签
        add_action( 'wp_head', array( $this, 'output_seo_meta' ), 1 );
        
        // 添加 JSON-LD 结构化数据
        add_action( 'wp_head', array( $this, 'output_jsonld' ), 2 );
        
        // Sitemap rewrite rules
        add_action( 'init', array( $this, 'add_sitemap_rewrite_rule' ) );
        add_filter( 'query_vars', array( $this, 'add_sitemap_query_var' ) );
        add_action( 'template_redirect', array( $this, 'render_sitemap' ) );
    }
    
    /**
     * 添加 Sitemap 重写规则
     */
    public function add_sitemap_rewrite_rule() {
        add_rewrite_rule( '^seopress-sitemap\.xml$', 'index.php?seopress_sitemap=1', 'top' );
    }

    /**
     * 添加 Sitemap 查询变量
     */
    public function add_sitemap_query_var( $vars ) {
        $vars[] = 'seopress_sitemap';
        return $vars;
    }

    /**
     * 渲染 Sitemap
     */
    public function render_sitemap() {
        if ( get_query_var( 'seopress_sitemap' ) ) {
            header( 'Content-Type: application/xml; charset=UTF-8' );
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            
            // 首页
            echo "\t<url>\n";
            echo "\t\t<loc>" . esc_url( home_url( '/' ) ) . "</loc>\n";
            echo "\t\t<lastmod>" . date( 'Y-m-d' ) . "</lastmod>\n";
            echo "\t\t<changefreq>daily</changefreq>\n";
            echo "\t\t<priority>1.0</priority>\n";
            echo "\t</url>\n";
            
            // 文章
            $posts = get_posts( array(
                'numberposts' => 1000,
                'orderby'     => 'post_date',
                'order'       => 'DESC',
                'post_status' => 'publish',
                'post_type'   => 'post'
            ) );
            
            foreach ( $posts as $post ) {
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url( get_permalink( $post->ID ) ) . "</loc>\n";
                echo "\t\t<lastmod>" . get_the_modified_date( 'Y-m-d', $post->ID ) . "</lastmod>\n";
                echo "\t\t<changefreq>weekly</changefreq>\n";
                echo "\t\t<priority>0.8</priority>\n";
                echo "\t</url>\n";
            }
            
            // 页面
            $pages = get_posts( array(
                'numberposts' => 100,
                'post_status' => 'publish',
                'post_type'   => 'page'
            ) );
            
            foreach ( $pages as $page ) {
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url( get_permalink( $page->ID ) ) . "</loc>\n";
                echo "\t\t<lastmod>" . get_the_modified_date( 'Y-m-d', $page->ID ) . "</lastmod>\n";
                echo "\t\t<changefreq>monthly</changefreq>\n";
                echo "\t\t<priority>0.6</priority>\n";
                echo "\t</url>\n";
            }
            
            echo '</urlset>';
            exit;
        }
    }
    
    /**
     * 输出 SEO Meta 标签
     */
    public function output_seo_meta(): void {
        $options = get_option( 'seopress_ai_options', array() );
        $auto_meta = isset( $options['auto_meta_enabled'] ) ? $options['auto_meta_enabled'] : true;
        
        if ( ! $auto_meta ) {
            // 如果禁用自动 Meta，恢复默认 title
            _wp_render_title_tag();
            return;
        }
        
        // 输出 charset
        echo '<meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">' . "\n";
        
        // 输出 viewport（移动端适配）
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";
        
        // 输出 title
        echo '<title>' . esc_html( $this->get_title() ) . '</title>' . "\n";
        
        // 输出 description
        $description = $this->get_description();
        if ( $description ) {
            echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        
        // 输出 keywords
        $keywords = $this->get_keywords();
        if ( $keywords ) {
            echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
        }
        
        // 输出 canonical URL
        echo '<link rel="canonical" href="' . esc_url( $this->get_canonical_url() ) . '">' . "\n";
        
        // 输出 Open Graph 标签
        $this->output_open_graph();
        
        // 输出 robots 标签
        echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
        
        // 百度验证码（如果有）
        $baidu_verify = isset( $options['baidu_verify_code'] ) ? $options['baidu_verify_code'] : '';
        if ( $baidu_verify ) {
            echo '<meta name="baidu-site-verification" content="' . esc_attr( $baidu_verify ) . '">' . "\n";
        }
    }
    
    /**
     * 获取页面标题
     */
    public function get_title(): string {
        $site_name = get_bloginfo( 'name' );
        $separator = ' - ';
        
        if ( is_front_page() ) {
            $tagline = get_bloginfo( 'description' );
            return $tagline ? $site_name . $separator . $tagline : $site_name;
        }
        
        if ( is_singular() ) {
            return get_the_title() . $separator . $site_name;
        }
        
        if ( is_category() ) {
            return single_cat_title( '', false ) . $separator . $site_name;
        }
        
        if ( is_tag() ) {
            return single_tag_title( '', false ) . $separator . $site_name;
        }
        
        if ( is_search() ) {
            return '"' . get_search_query() . '" 的搜索结果' . $separator . $site_name;
        }
        
        if ( is_404() ) {
            return '页面未找到' . $separator . $site_name;
        }
        
        if ( is_archive() ) {
            return get_the_archive_title() . $separator . $site_name;
        }
        
        return $site_name;
    }
    
    /**
     * 获取页面描述
     */
    public function get_description(): string {
        if ( is_front_page() ) {
            return get_bloginfo( 'description' );
        }
        
        if ( is_singular() ) {
            $post = get_post();
            if ( $post ) {
                // 优先使用摘要
                $excerpt = $post->post_excerpt;
                if ( empty( $excerpt ) ) {
                    // 从内容中提取
                    $content = wp_strip_all_tags( $post->post_content );
                    $content = str_replace( array( "\n", "\r", "\t" ), ' ', $content );
                    $excerpt = mb_substr( $content, 0, 160, 'UTF-8' );
                }
                return wp_trim_words( $excerpt, 30, '...' );
            }
        }
        
        if ( is_category() || is_tag() ) {
            $term_description = term_description();
            if ( $term_description ) {
                return wp_strip_all_tags( $term_description );
            }
        }
        
        return get_bloginfo( 'description' );
    }
    
    /**
     * 获取关键词
     */
    public function get_keywords(): string {
        if ( ! is_singular() ) {
            return '';
        }
        
        $keywords = array();
        $post = get_post();
        
        if ( ! $post ) {
            return '';
        }
        
        // 从分类获取关键词
        $categories = get_the_category( $post->ID );
        if ( $categories ) {
            foreach ( $categories as $category ) {
                $keywords[] = $category->name;
            }
        }
        
        // 从标签获取关键词
        $tags = get_the_tags( $post->ID );
        if ( $tags ) {
            foreach ( $tags as $tag ) {
                $keywords[] = $tag->name;
            }
        }
        
        // 限制关键词数量
        $keywords = array_slice( array_unique( $keywords ), 0, 10 );
        
        return implode( ',', $keywords );
    }
    
    /**
     * 获取规范 URL
     */
    public function get_canonical_url(): string {
        if ( is_front_page() ) {
            return home_url( '/' );
        }
        
        if ( is_singular() ) {
            return get_permalink();
        }
        
        if ( is_category() || is_tag() || is_tax() ) {
            return get_term_link( get_queried_object() );
        }
        
        if ( is_author() ) {
            return get_author_posts_url( get_queried_object_id() );
        }
        
        if ( is_archive() ) {
            if ( is_date() ) {
                if ( is_year() ) {
                    return get_year_link( get_the_date( 'Y' ) );
                }
                if ( is_month() ) {
                    return get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) );
                }
                if ( is_day() ) {
                    return get_day_link( get_the_date( 'Y' ), get_the_date( 'm' ), get_the_date( 'd' ) );
                }
            }
        }
        
        return home_url( $_SERVER['REQUEST_URI'] );
    }
    
    /**
     * 输出 Open Graph 标签
     */
    private function output_open_graph(): void {
        $site_name = get_bloginfo( 'name' );
        $locale = get_locale();
        
        echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $this->get_title() ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $this->get_description() ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $this->get_canonical_url() ) . '">' . "\n";
        
        if ( is_singular() ) {
            echo '<meta property="og:type" content="article">' . "\n";
            
            // 文章发布时间
            echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
            
            // 文章特色图片
            if ( has_post_thumbnail() ) {
                $thumbnail_url = get_the_post_thumbnail_url( null, 'large' );
                echo '<meta property="og:image" content="' . esc_url( $thumbnail_url ) . '">' . "\n";
            }
        } else {
            echo '<meta property="og:type" content="website">' . "\n";
        }
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $this->get_title() ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $this->get_description() ) . '">' . "\n";
    }
    
    /**
     * 输出 JSON-LD 结构化数据
     */
    public function output_jsonld(): void {
        $options = get_option( 'seopress_ai_options', array() );
        $jsonld_enabled = isset( $options['jsonld_enabled'] ) ? $options['jsonld_enabled'] : true;
        
        if ( ! $jsonld_enabled ) {
            return;
        }
        
        $schemas = array();
        
        // 网站 Schema
        $schemas[] = $this->get_website_schema();
        
        // 面包屑 Schema
        $breadcrumb = $this->get_breadcrumb_schema();
        if ( $breadcrumb ) {
            $schemas[] = $breadcrumb;
        }
        
        // 文章 Schema
        if ( is_singular( 'post' ) ) {
            $schemas[] = $this->get_article_schema();
        }
        
        foreach ( $schemas as $schema ) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
            echo "\n</script>\n";
        }
    }
    
    /**
     * 获取网站 Schema
     */
    private function get_website_schema(): array {
        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url( '/' ),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        );
    }
    
    /**
     * 获取面包屑 Schema
     */
    private function get_breadcrumb_schema(): ?array {
        if ( is_front_page() ) {
            return null;
        }
        
        $items = array();
        $position = 1;
        
        // 首页
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => '首页',
            'item'     => home_url( '/' ),
        );
        
        if ( is_singular() ) {
            // 分类
            $categories = get_the_category();
            if ( $categories ) {
                $category = $categories[0];
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $category->name,
                    'item'     => get_category_link( $category->term_id ),
                );
            }
            
            // 当前文章
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => get_the_title(),
                'item'     => get_permalink(),
            );
        } elseif ( is_category() ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => single_cat_title( '', false ),
                'item'     => get_category_link( get_queried_object_id() ),
            );
        }
        
        return array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        );
    }
    
    /**
     * 获取文章 Schema
     */
    private function get_article_schema(): array {
        $post = get_post();
        $author = get_the_author();
        
        $schema = array(
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => get_the_title(),
            'description'      => $this->get_description(),
            'datePublished'    => get_the_date( 'c' ),
            'dateModified'     => get_the_modified_date( 'c' ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => get_permalink(),
            ),
            'author'           => array(
                '@type' => 'Person',
                'name'  => $author,
            ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
            ),
        );
        
        // 添加特色图片
        if ( has_post_thumbnail() ) {
            $thumbnail_id = get_post_thumbnail_id();
            $thumbnail_data = wp_get_attachment_image_src( $thumbnail_id, 'full' );
            if ( $thumbnail_data ) {
                $schema['image'] = array(
                    '@type'  => 'ImageObject',
                    'url'    => $thumbnail_data[0],
                    'width'  => $thumbnail_data[1],
                    'height' => $thumbnail_data[2],
                );
            }
        }
        
        return $schema;
    }
}
