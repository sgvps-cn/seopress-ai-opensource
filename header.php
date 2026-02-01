<?php
/**
 * 页头模板 - 现代毛玻璃设计
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="baidu-site-verification" content="codeva-HmpD12K23C" />
    <?php wp_head(); ?>
    <script>
        // 在页面加载前应用主题，防止闪烁
        (function() {
            var theme = localStorage.getItem('sp-theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link sr-only" href="#content"><?php esc_html_e( '跳至内容', 'seopress-ai' ); ?></a>

<!-- 阅读进度条 -->
<?php if ( is_singular( 'post' ) ) : ?>
<div class="reading-progress" id="reading-progress"></div>
<?php endif; ?>

<header class="site-header" id="site-header" role="banner">
    <div class="sp-container">
        <div class="header-inner">
            <!-- Logo / 品牌 -->
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="site-identity">
                    <?php if ( is_front_page() && is_home() ) : ?>
                        <h1 class="site-title">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                                <?php bloginfo( 'name' ); ?>
                            </a>
                        </h1>
                    <?php else : ?>
                        <p class="site-title">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                                <?php bloginfo( 'name' ); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description ) :
                    ?>
                        <p class="site-description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 主导航 -->
            <nav class="main-navigation" id="main-navigation" role="navigation" aria-label="<?php esc_attr_e( '主导航', 'seopress-ai' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'nav-menu',
                    'container'      => false,
                    'fallback_cb'    => 'seopress_ai_fallback_menu',
                    'depth'          => 2,
                ) );
                ?>
            </nav>

            <!-- Header 操作区 -->
            <div class="header-actions">
                <!-- 搜索按钮 -->
                <button class="header-search-toggle" id="search-toggle" aria-label="<?php esc_attr_e( '搜索', 'seopress-ai' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                
                <!-- 暗色模式切换 -->
                <button class="theme-toggle" id="theme-toggle" aria-label="<?php esc_attr_e( '切换主题', 'seopress-ai' ); ?>">
                    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                    </svg>
                    <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                
                <!-- 移动端菜单按钮 -->
                <button class="menu-toggle" id="menu-toggle" aria-label="<?php esc_attr_e( '菜单', 'seopress-ai' ); ?>" aria-expanded="false">
                    <span class="menu-toggle-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- 搜索弹窗 -->
<div class="search-overlay" id="search-overlay">
    <div class="search-overlay-inner">
        <button class="search-close" id="search-close" aria-label="<?php esc_attr_e( '关闭搜索', 'seopress-ai' ); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18M6 6l12 12"></path>
            </svg>
        </button>
        <form role="search" method="get" class="search-form-overlay" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" class="search-field-overlay" placeholder="<?php esc_attr_e( '搜索文章...', 'seopress-ai' ); ?>" value="<?php echo get_search_query(); ?>" name="s" autofocus>
            <button type="submit" class="search-submit-overlay">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </form>
        <p class="search-hint"><?php esc_html_e( '按 ESC 关闭', 'seopress-ai' ); ?></p>
    </div>
</div>

<main id="content" class="site-main" role="main">
    <div class="sp-container">

<?php
/**
 * 默认菜单回调
 */
function seopress_ai_fallback_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'seopress-ai' ) . '</a></li>';
    
    // 获取分类
    $categories = get_categories( array( 'number' => 5, 'hide_empty' => true ) );
    foreach ( $categories as $category ) {
        echo '<li><a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a></li>';
    }
    
    echo '</ul>';
}
?>
