<?php
/**
 * 首页模板 - 博客文章列表
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php if ( is_home() && ! is_paged() ) : ?>
<!-- 首页英雄区 -->
<section class="hero-section">
    <div class="hero-avatar">
        <?php echo get_avatar( get_option( 'admin_email' ), 200 ); ?>
    </div>
    <h1 class="hero-title gradient-text"><?php bloginfo( 'name' ); ?></h1>
    <p class="hero-subtitle">
        <?php 
        $description = get_bloginfo( 'description' );
        echo esc_html( $description ? $description : '分享技术、生活与思考' );
        ?>
    </p>
    <?php
    $settings = SeoPress_AI_Settings::get_instance();
    $github_link = $settings->get('github_link');
    $qq_link = $settings->get('qq_link');
    $email_address = $settings->get('email_address');
    ?>
    <div class="hero-social">
        <?php if ( $github_link ) : ?>
        <a href="<?php echo esc_url( $github_link ); ?>" aria-label="GitHub" title="GitHub" target="_blank" rel="nofollow noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
        </a>
        <?php endif; ?>
        <?php if ( $qq_link ) : ?>
        <a href="<?php echo esc_url( is_numeric($qq_link) ? 'http://wpa.qq.com/msgrd?v=3&uin=' . $qq_link . '&site=qq&menu=yes' : $qq_link ); ?>" aria-label="QQ" title="联系QQ" target="_blank" rel="nofollow noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.003 2c-5.523 0-10 4.477-10 10 0 5.522 4.477 10 10 10 5.522 0 10-4.478 10-10 0-5.523-4.478-10-10-10zm0 1.24c4.832 0 8.76 3.928 8.76 8.76 0 4.831-3.928 8.76-8.76 8.76-4.832 0-8.76-3.929-8.76-8.76 0-4.832 3.928-8.76 8.76-8.76zm.806 3.635c-2.315-.125-4.414 1.096-4.414 3.75 0 2.235 1.574 3.535 3.336 3.926-.062.296-.062.607.125.812.188.188.547.25.938.25.375 0 .734-.062.922-.25.187-.188.187-.5.125-.813 1.765-.39 3.328-1.706 3.328-3.922 0-2.656-2.094-3.875-4.36-3.753zm.047 1.094c1.172-.032 2.453.64 2.453 2.656 0 1.547-1.125 2.531-2.484 2.89h-.032c-1.343-.36-2.484-1.343-2.484-2.89 0-2.031 1.344-2.704 2.547-2.657z" fill-rule="evenodd" clip-rule="evenodd"/>
            </svg>
        </a>
        <?php endif; ?>
        <a href="<?php echo esc_url( get_feed_link() ); ?>" aria-label="RSS" title="RSS订阅">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
            </svg>
        </a>
    </div>
</section>
<?php endif; ?>

<div class="site-content">
    <div class="content-area">
        <?php if ( ! is_home() || is_paged() ) : ?>
        <h2 class="posts-section-title">
            <?php
            if ( is_category() ) {
                single_cat_title();
            } elseif ( is_tag() ) {
                single_tag_title();
            } elseif ( is_author() ) {
                the_author();
            } elseif ( is_paged() ) {
                printf( esc_html__( '第 %d 页', 'seopress-ai' ), get_query_var( 'paged' ) );
            } else {
                esc_html_e( '最新文章', 'seopress-ai' );
            }
            ?>
        </h2>
        <?php else : ?>
        <h2 class="posts-section-title"><?php esc_html_e( '最新文章', 'seopress-ai' ); ?></h2>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>
            <div class="post-list">
                <?php
                $post_count = 0;
                while ( have_posts() ) :
                    the_post();
                    $post_count++;
                    
                    // 第一篇文章使用特色样式
                    if ( $post_count === 1 && is_home() && ! is_paged() ) {
                        set_query_var( 'is_featured', true );
                    } else {
                        set_query_var( 'is_featured', false );
                    }
                    
                    get_template_part( 'template-parts/content', get_post_type() );
                endwhile;
                ?>
            </div>

            <?php
            // 分页
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>',
                'next_text' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>',
            ) );
            ?>

        <?php else : ?>
            <?php get_template_part( 'template-parts/content', 'none' ); ?>
        <?php endif; ?>
    </div>

    <!-- 侧边栏 -->
    <aside class="sidebar">
        <!-- 博主信息 -->
        <div class="widget widget-author">
            <div class="author-avatar">
                <?php echo get_avatar( get_option( 'admin_email' ), 150 ); ?>
            </div>
            <h3 class="author-name"><?php echo esc_html( get_option( 'blogname' ) ); ?></h3>
            <p class="author-bio">
                <?php 
                $description = get_bloginfo( 'description' );
                echo esc_html( $description ? $description : '热爱技术，热爱生活' );
                ?>
            </p>
            <div class="author-social">
                <?php if ( $github_link ) : ?>
                <a href="<?php echo esc_url( $github_link ); ?>" aria-label="GitHub" target="_blank" rel="nofollow noopener">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </a>
                <?php endif; ?>
                <a href="mailto:<?php echo esc_attr( $email_address ? $email_address : get_option('admin_email') ); ?>" aria-label="Email">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </a>
                <a href="<?php echo esc_url( get_feed_link() ); ?>" aria-label="RSS">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- 分类 -->
        <div class="widget widget-categories">
            <h3 class="widget-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <?php esc_html_e( '分类目录', 'seopress-ai' ); ?>
            </h3>
            <ul>
                <?php
                $categories = get_categories( array(
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'number'     => 8,
                    'hide_empty' => true,
                ) );
                foreach ( $categories as $category ) :
                ?>
                    <li>
                        <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
                            <span><?php echo esc_html( $category->name ); ?></span>
                            <span class="count"><?php echo esc_html( $category->count ); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- 标签云 -->
        <div class="widget widget-tags">
            <h3 class="widget-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                <?php esc_html_e( '热门标签', 'seopress-ai' ); ?>
            </h3>
            <div class="tagcloud">
                <?php
                $tags = get_tags( array(
                    'orderby' => 'count',
                    'order'   => 'DESC',
                    'number'  => 15,
                ) );
                foreach ( $tags as $tag ) :
                ?>
                    <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><?php echo esc_html( $tag->name ); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php dynamic_sidebar( 'sidebar-1' ); ?>
    </aside>
</div>

<?php get_footer(); ?>
