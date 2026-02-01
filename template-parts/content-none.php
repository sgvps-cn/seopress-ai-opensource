<?php
/**
 * 无内容时显示的模板
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( '没有找到内容', 'seopress-ai' ); ?></h1>
    </header>

    <div class="page-content">
        <?php if ( is_search() ) : ?>
            <p><?php esc_html_e( '抱歉，没有找到与您搜索相关的内容。请尝试使用其他关键词。', 'seopress-ai' ); ?></p>
            <?php get_search_form(); ?>
        <?php else : ?>
            <p><?php esc_html_e( '这里似乎什么都没有。也许可以尝试搜索？', 'seopress-ai' ); ?></p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </div>
</section>
