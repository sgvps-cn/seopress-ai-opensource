<?php
/**
 * 文章卡片模板
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_featured = get_query_var( 'is_featured', false );
$card_class = 'post-card';
if ( $is_featured ) {
    $card_class .= ' featured';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $card_class ); ?>>
    <!-- 缩略图 -->
    <div class="post-thumbnail">
        <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail( $is_featured ? 'large' : 'medium_large' ); ?>
            </a>
            <div class="post-thumbnail-overlay"></div>
        <?php else : ?>
            <a href="<?php the_permalink(); ?>" class="post-thumbnail-placeholder" aria-label="<?php the_title_attribute(); ?>">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </a>
            <div class="post-thumbnail-overlay"></div>
        <?php endif; ?>
    </div>

    <!-- 内容 -->
    <div class="post-card-content">
        <!-- 分类 -->
        <?php
        $categories = get_the_category();
        if ( ! empty( $categories ) ) :
        ?>
            <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="post-category">
                <?php echo esc_html( $categories[0]->name ); ?>
            </a>
        <?php endif; ?>

        <!-- 标题 -->
        <h2 class="post-card-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>

        <!-- Meta信息 -->
        <div class="post-meta">
            <span class="post-meta-item post-date">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>
            </span>
            
            <span class="post-meta-item post-reading-time">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

            <?php if ( comments_open() || get_comments_number() ) : ?>
            <span class="post-meta-item post-comments">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <?php comments_number( '0', '1', '%' ); ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- 摘要 -->
        <div class="post-excerpt">
            <?php
            if ( has_excerpt() ) {
                the_excerpt();
            } else {
                echo wp_trim_words( get_the_content(), $is_featured ? 80 : 40, '...' );
            }
            ?>
        </div>

        <!-- 阅读更多 -->
        <a href="<?php the_permalink(); ?>" class="read-more">
            <?php esc_html_e( '阅读全文', 'seopress-ai' ); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</article>
