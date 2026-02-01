<?php
/**
 * SeoPress AI Theme Functions
 * 
 * 主题功能入口文件
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 定义主题常量
 */
define( 'SEOPRESS_AI_VERSION', '1.0.1' );
define( 'SEOPRESS_AI_PATH', get_template_directory() . '/' );
define( 'SEOPRESS_AI_URL', get_template_directory_uri() . '/' );

/**
 * 加载核心类文件
 */
function seopress_ai_load_classes() {
    $classes = array(
        'inc/ai-providers/interface-ai-provider.php',
        'inc/ai-providers/class-deepseek.php',
        'inc/ai-providers/class-qwen.php',
        'inc/ai-providers/class-ernie.php',
        'inc/ai-providers/class-kimi.php',
        'inc/ai-providers/class-zhipu.php',
        'inc/ai-providers/class-siliconflow.php',
        'inc/ai-providers/class-groq.php',
        'inc/class-ai-manager.php',
        'inc/class-seo-manager.php',
        'inc/class-baidu-push.php',
        'inc/class-settings.php',
        'inc/class-auto-publish.php',
        'inc/class-unsplash.php',
        'inc/breadcrumb.php',
        'inc/sitemap-enhancer.php',
        'inc/image-seo.php',
        'inc/auto-links.php',
        'admin/class-admin-page.php',
        // 'admin/class-auto-publish-page.php',
    );
    
    foreach ( $classes as $class ) {
        $file = SEOPRESS_AI_PATH . $class;
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
add_action( 'after_setup_theme', 'seopress_ai_load_classes', 5 );

/**
 * 主题初始化
 */
function seopress_ai_setup() {
    // 加载文本域
    load_theme_textdomain( 'seopress-ai', SEOPRESS_AI_PATH . 'languages' );
    
    // 添加主题支持
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ) );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'editor-styles' );
    
    // 注册导航菜单
    register_nav_menus( array(
        'primary' => __( '主导航', 'seopress-ai' ),
        'footer'  => __( '页脚导航', 'seopress-ai' ),
    ) );
    
    // 设置内容宽度
    global $content_width;
    if ( ! isset( $content_width ) ) {
        $content_width = 720;
    }
}
add_action( 'after_setup_theme', 'seopress_ai_setup' );

/**
 * 注册侧边栏
 */
function seopress_ai_widgets_init() {
    register_sidebar( array(
        'name'          => __( '主侧边栏', 'seopress-ai' ),
        'id'            => 'sidebar-main',
        'description'   => __( '添加小工具到主侧边栏', 'seopress-ai' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( '页脚区域', 'seopress-ai' ),
        'id'            => 'footer-widgets',
        'description'   => __( '添加小工具到页脚', 'seopress-ai' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'seopress_ai_widgets_init' );

/**
 * 加载前端样式和脚本
 */
function seopress_ai_enqueue_scripts() {
    // 主样式
    wp_enqueue_style(
        'seopress-ai-style',
        get_stylesheet_uri(),
        array(),
        SEOPRESS_AI_VERSION
    );
    
    // 响应式样式
    if ( file_exists( SEOPRESS_AI_PATH . 'assets/css/responsive.css' ) ) {
        wp_enqueue_style(
            'seopress-ai-responsive',
            SEOPRESS_AI_URL . 'assets/css/responsive.css',
            array( 'seopress-ai-style' ),
            SEOPRESS_AI_VERSION
        );
    }
    
    // 主脚本
    if ( file_exists( SEOPRESS_AI_PATH . 'assets/js/main.js' ) ) {
        wp_enqueue_script(
            'seopress-ai-main',
            SEOPRESS_AI_URL . 'assets/js/main.js',
            array(),
            SEOPRESS_AI_VERSION,
            true
        );
        
        // 传递 AJAX URL
        wp_localize_script( 'seopress-ai-main', 'seopressAI', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'seopress_ajax_nonce' ),
        ) );
    }
    
    // 评论回复脚本
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'seopress_ai_enqueue_scripts' );

/**
 * 加载后台样式和脚本
 */
function seopress_ai_admin_enqueue_scripts( $hook ) {
    // 只在主题设置页面加载
    if ( strpos( $hook, 'seopress-ai' ) === false ) {
        return;
    }
    
    wp_enqueue_style(
        'seopress-ai-admin',
        SEOPRESS_AI_URL . 'admin/css/admin-style.css',
        array(),
        SEOPRESS_AI_VERSION
    );
    
    wp_enqueue_script(
        'seopress-ai-admin',
        SEOPRESS_AI_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        SEOPRESS_AI_VERSION,
        true
    );
    
    wp_localize_script( 'seopress-ai-admin', 'seopressAdmin', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'seopress_admin_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'seopress_ai_admin_enqueue_scripts' );

/**
 * 初始化各个模块
 */
function seopress_ai_init_modules() {
    // 初始化 SEO 管理器
    if ( class_exists( 'SeoPress_AI_SEO_Manager' ) ) {
        SeoPress_AI_SEO_Manager::get_instance();
    }
    
    // 初始化百度推送
    if ( class_exists( 'SeoPress_AI_Baidu_Push' ) ) {
        SeoPress_AI_Baidu_Push::get_instance();
    }
    
    // 初始化 AI 管理器
    if ( class_exists( 'SeoPress_AI_Manager' ) ) {
        SeoPress_AI_Manager::get_instance();
    }
    
    // 初始化后台设置（仅在后台）
    if ( is_admin() ) {
        if ( class_exists( 'SeoPress_AI_Admin_Page' ) ) {
            new SeoPress_AI_Admin_Page();
        }
    }
}
add_action( 'init', 'seopress_ai_init_modules' );

/**
 * 输出百度统计代码
 */
function seopress_ai_output_analytics() {
    $options = get_option( 'seopress_ai_options', array() );
    $analytics_code = isset( $options['analytics_code'] ) ? $options['analytics_code'] : '';
    
    if ( ! empty( $analytics_code ) ) {
        echo $analytics_code;
    }
}
add_action( 'wp_footer', 'seopress_ai_output_analytics', 100 );

/**
 * 辅助函数：获取主题选项
 * 
 * @param string $key 选项键名
 * @param mixed  $default 默认值
 * @return mixed
 */
function seopress_ai_get_option( $key, $default = '' ) {
    $options = get_option( 'seopress_ai_options', array() );
    return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}

/**
 * 辅助函数：获取文章摘要
 * 
 * @param int $length 字数限制
 * @return string
 */
function seopress_ai_get_excerpt( $length = 120 ) {
    $post = get_post();
    if ( ! $post ) {
        return '';
    }
    
    $excerpt = $post->post_excerpt;
    if ( empty( $excerpt ) ) {
        $content = wp_strip_all_tags( $post->post_content );
        $content = str_replace( array( "\n", "\r", "\t" ), ' ', $content );
        $excerpt = mb_substr( $content, 0, $length, 'UTF-8' );
        if ( mb_strlen( $content, 'UTF-8' ) > $length ) {
            $excerpt .= '...';
        }
    }
    
    return $excerpt;
}

/**
 * 辅助函数：获取阅读时间
 * 
 * @return int 分钟数
 */
function seopress_ai_reading_time() {
    $content = get_post_field( 'post_content', get_the_ID() );
    $word_count = mb_strlen( wp_strip_all_tags( $content ), 'UTF-8' );
    $reading_time = ceil( $word_count / 300 ); // 假设每分钟阅读300字
    return max( 1, $reading_time );
}

/**
 * 自定义评论回调函数
 * 
 * @param WP_Comment $comment 评论对象
 * @param array      $args    参数
 * @param int        $depth   嵌套深度
 */
function seopress_ai_comment_callback( $comment, $args, $depth ) {
    $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
    $comment_class = comment_class( '', $comment, null, false );
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php echo $comment_class; ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <div class="comment-author">
                <?php echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'class' => 'avatar' ) ); ?>
            </div>
            
            <div class="comment-content-wrapper">
                <div class="comment-meta">
                    <div class="comment-author-info">
                        <span class="fn"><?php echo get_comment_author_link( $comment ); ?></span>
                        <?php if ( $comment->user_id === get_post_field( 'post_author', get_the_ID() ) ) : ?>
                            <span class="comment-author-badge"><?php esc_html_e( '作者', 'seopress-ai' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="comment-metadata">
                        <a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php
                                printf(
                                    /* translators: 1: 日期, 2: 时间 */
                                    esc_html__( '%1$s %2$s', 'seopress-ai' ),
                                    get_comment_date( '', $comment ),
                                    get_comment_time()
                                );
                                ?>
                            </time>
                        </a>
                        <?php edit_comment_link( esc_html__( '编辑', 'seopress-ai' ), '<span class="edit-link">', '</span>' ); ?>
                    </div>
                </div>

                <?php if ( '0' == $comment->comment_approved ) : ?>
                    <div class="comment-awaiting-moderation">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?php esc_html_e( '您的评论正在等待审核。', 'seopress-ai' ); ?>
                    </div>
                <?php endif; ?>

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>

                <div class="reply">
                    <?php
                    comment_reply_link( array_merge( $args, array(
                        'add_below' => 'div-comment',
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '',
                        'after'     => '',
                    ) ) );
                    ?>
                </div>
            </div>
        </article>
    <?php
}


/**
 * 添加默认favicon支持
 */
function seopress_ai_add_favicon() {
    // 如果用户没有通过WordPress自定义器设置站点图标，则使用主题默认图标
    if ( ! has_site_icon() ) {
        $favicon_url = get_template_directory_uri() . '/assets/images/favicon.svg';
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( $favicon_url ) . '">' . "\n";
        echo '<link rel="apple-touch-icon" href="' . esc_url( $favicon_url ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'seopress_ai_add_favicon', 1 );

/**
 * 优化：强制确保内容图片开启Lazy Loading
 */
add_filter( 'wp_lazy_loading_enabled', '__return_true' );


/**
 * Auto generate thumbnail on save if missing
 */
function seopress_ai_auto_generate_thumbnail_on_save( $post_id, $post, $update ) {
    // Skip revisions and autosaves
    if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
        return;
    }
    
    // Check post type
    if ( get_post_type( $post_id ) !== 'post' ) {
        return;
    }

    // Check if already has thumbnail
    if ( has_post_thumbnail( $post_id ) ) {
        return;
    }
    
    // Check if generation is already processed to avoid infinite loops
    if ( get_post_meta( $post_id, '_seopress_auto_thumbnail_processed', true ) ) {
        return;
    }
    
    // Use theme's generation class
    if ( class_exists( 'SeoPress_AI_Unsplash' ) ) {
        $unsplash = SeoPress_AI_Unsplash::get_instance();
        // Use post title as keyword
        $unsplash->set_featured_image( $post_id, $post->post_title );
        // Mark as processed
        update_post_meta( $post_id, '_seopress_auto_thumbnail_processed', true );
    }
}
add_action( 'save_post', 'seopress_ai_auto_generate_thumbnail_on_save', 10, 3 );
