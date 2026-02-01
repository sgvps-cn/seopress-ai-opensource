<?php
/**
 * 文章详情页模板
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) :
    the_post();
?>

<div class="single-post-wrapper">
    <?php seopress_ai_breadcrumb(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>
        <!-- 文章头部 -->
        <header class="entry-header">
            <?php
            $categories = get_the_category();
            if ( ! empty( $categories ) ) :
            ?>
                <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="entry-category">
                    <?php echo esc_html( $categories[0]->name ); ?>
                </a>
            <?php endif; ?>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="entry-meta">
                <span class="entry-meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <?php the_author(); ?>
                </span>
                <span class="entry-meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </time>
                </span>
                <span class="entry-meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <?php
                    $content = get_the_content();
                    $word_count = mb_strlen( strip_tags( $content ), 'UTF-8' );
                    $reading_time = max( 1, ceil( $word_count / 400 ) );
                    printf( esc_html__( '%d 分钟阅读', 'seopress-ai' ), $reading_time );
                    ?>
                </span>
                <span class="entry-meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <?php printf( esc_html__( '%d 字', 'seopress-ai' ), $word_count ); ?>
                </span>
            </div>
        </header>

        <!-- 特色图片 -->
        <?php if ( has_post_thumbnail() ) : ?>
        <figure class="entry-thumbnail">
            <?php the_post_thumbnail( 'full' ); ?>
        </figure>
        <?php endif; ?>

        <!-- 文章内容 -->
        <div class="entry-content" id="entry-content">
            <?php
            the_content();

            wp_link_pages( array(
                'before' => '<div class="page-links">' . esc_html__( '页面:', 'seopress-ai' ),
                'after'  => '</div>',
            ) );
            ?>
        </div>

        <!-- 文章标签 -->
        <?php
        $tags = get_the_tags();
        if ( $tags ) :
        ?>
        <div class="entry-tags">
            <?php foreach ( $tags as $tag ) : ?>
                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
                    <?php echo esc_html( $tag->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- 分享按钮 -->
        <div class="entry-share">
            <span class="share-label"><?php esc_html_e( '分享文章：', 'seopress-ai' ); ?></span>
            <div class="share-buttons">
                <a href="https://service.weibo.com/share/share.php?url=<?php echo urlencode( get_permalink() ); ?>&title=<?php echo urlencode( get_the_title() ); ?>" 
                   class="share-btn weibo" target="_blank" rel="noopener" aria-label="分享到微博">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.194.573zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.405-.649.375-1.018.415-1.9.003-2.525-.77-1.167-2.887-1.105-5.312-.03 0 0-.762.331-.566-.271.37-1.205.313-2.212-.262-2.792-1.307-1.313-4.78.047-7.763 3.036C1.318 10.889 0 13.471 0 15.665c0 4.199 5.408 6.755 10.697 6.755 6.929 0 11.544-4.016 11.544-7.2 0-1.928-1.627-3.025-3.752-3.571zm1.348-6.674c-.919-1.018-2.274-1.498-3.625-1.349-.393.043-.682.394-.639.788.043.393.394.682.788.639.886-.098 1.781.225 2.39.893.609.667.855 1.586.666 2.457-.089.386.151.772.538.86.386.088.773-.15.86-.537.285-1.313-.085-2.733-1.004-3.751h.026zm.689-3.391c-1.662-1.845-4.118-2.711-6.567-2.423-.391.046-.675.401-.629.792.046.391.401.675.792.629 2.009-.235 4.021.474 5.385 1.987 1.363 1.512 1.907 3.556 1.452 5.461-.086.388.158.771.545.857.069.015.137.022.204.022.312 0 .595-.212.675-.529.554-2.32-.108-4.799-1.764-6.795l-.093-.001z"/>
                    </svg>
                </a>
                <button class="share-btn wechat" id="wechat-share" aria-label="分享到微信">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348z"/>
                    </svg>
                </button>
                <a href="https://connect.qq.com/widget/shareqq/index.html?url=<?php echo urlencode( get_permalink() ); ?>&title=<?php echo urlencode( get_the_title() ); ?>" 
                   class="share-btn qq" target="_blank" rel="noopener" aria-label="分享到QQ">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.003 2c-2.265 0-6.29 1.364-6.29 7.325v1.195S3.55 14.96 3.55 17.474c0 .665.17 1.025.281 1.025.114 0 .902-.484 1.748-2.072 0 0-.18 2.197 1.904 3.967 0 0-1.77.495-1.77 1.182 0 .686 4.078.43 6.29.43 2.212 0 6.29.256 6.29-.43 0-.687-1.77-1.182-1.77-1.182 2.085-1.77 1.905-3.967 1.905-3.967.846 1.588 1.634 2.072 1.746 2.072.111 0 .283-.36.283-1.025 0-2.514-2.166-6.954-2.166-6.954V9.325C18.29 3.364 14.268 2 12.003 2z"/>
                    </svg>
                </a>
                <button class="share-btn link" id="copy-link" aria-label="复制链接">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                    </svg>
                </button>
            </div>
        </div>
    </article>

    <!-- 上一篇/下一篇 -->
    <nav class="post-navigation">
        <?php
        $prev_post = get_previous_post();
        $next_post = get_next_post();
        ?>
        
        <?php if ( $prev_post ) : ?>
        <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="nav-previous">
            <span class="nav-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <?php esc_html_e( '上一篇', 'seopress-ai' ); ?>
            </span>
            <span class="nav-title"><?php echo esc_html( wp_trim_words( $prev_post->post_title, 15 ) ); ?></span>
        </a>
        <?php endif; ?>
        
        <?php if ( $next_post ) : ?>
        <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="nav-next">
            <span class="nav-label">
                <?php esc_html_e( '下一篇', 'seopress-ai' ); ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </span>
            <span class="nav-title"><?php echo esc_html( wp_trim_words( $next_post->post_title, 15 ) ); ?></span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- 相关文章 -->
    <?php
    $related_posts = new WP_Query( array(
        'category__in'   => wp_get_post_categories( get_the_ID() ),
        'post__not_in'   => array( get_the_ID() ),
        'posts_per_page' => 3,
        'orderby'        => 'rand',
    ) );

    if ( $related_posts->have_posts() ) :
    ?>
    <section class="related-posts">
        <h3 class="related-posts-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <?php esc_html_e( '相关文章', 'seopress-ai' ); ?>
        </h3>
        <div class="related-posts-grid">
            <?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="related-post-card">
                <div class="related-post-thumbnail">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'medium' ); ?>
                    <?php else : ?>
                        <div class="related-post-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="related-post-title"><?php the_title(); ?></h4>
                <span class="related-post-date"><?php echo esc_html( get_the_date() ); ?></span>
            </a>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    endif;
    ?>

    <!-- 评论 -->
    <?php
    if ( comments_open() || get_comments_number() ) :
        comments_template();
    endif;
    ?>
</div>

<?php
endwhile;

get_footer();
?>
