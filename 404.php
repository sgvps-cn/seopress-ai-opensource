<?php
/**
 * 404 错误页面模板
 *
 * @package SeoPress_AI
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('页面未找到', 'seopress-ai'); ?></h1>
            </header>

            <div class="page-content">
                <div class="error-icon">
                    <svg viewBox="0 0 24 24" width="120" height="120" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" opacity="0.3"/>
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-5h2v2h-2zm0-8h2v6h-2z"/>
                    </svg>
                </div>

                <p class="error-message">
                    <?php esc_html_e('抱歉，您访问的页面不存在或已被移除。', 'seopress-ai'); ?>
                </p>

                <div class="error-suggestions">
                    <h2><?php esc_html_e('您可以尝试：', 'seopress-ai'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('检查网址是否输入正确', 'seopress-ai'); ?></li>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('返回首页', 'seopress-ai'); ?></a></li>
                        <li><?php esc_html_e('使用下方搜索功能查找内容', 'seopress-ai'); ?></li>
                    </ul>
                </div>

                <div class="error-search">
                    <h3><?php esc_html_e('搜索站点', 'seopress-ai'); ?></h3>
                    <?php get_search_form(); ?>
                </div>

                <div class="error-recent-posts">
                    <h3><?php esc_html_e('最新文章', 'seopress-ai'); ?></h3>
                    <ul>
                        <?php
                        $recent_posts = wp_get_recent_posts(array(
                            'numberposts' => 5,
                            'post_status' => 'publish',
                        ));
                        foreach ($recent_posts as $post) :
                            ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($post['ID'])); ?>">
                                    <?php echo esc_html($post['post_title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="error-categories">
                    <h3><?php esc_html_e('分类目录', 'seopress-ai'); ?></h3>
                    <ul>
                        <?php
                        wp_list_categories(array(
                            'orderby'    => 'count',
                            'order'      => 'DESC',
                            'show_count' => true,
                            'title_li'   => '',
                            'number'     => 10,
                        ));
                        ?>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.error-404 {
    text-align: center;
    padding: 60px 20px;
}

.error-icon {
    color: var(--color-primary);
    margin-bottom: 30px;
}

.error-404 .page-title {
    font-size: 48px;
    margin-bottom: 20px;
}

.error-message {
    font-size: 18px;
    color: var(--color-text-light);
    margin-bottom: 40px;
}

.error-suggestions,
.error-search,
.error-recent-posts,
.error-categories {
    max-width: 600px;
    margin: 0 auto 40px;
    text-align: left;
}

.error-suggestions h2,
.error-search h3,
.error-recent-posts h3,
.error-categories h3 {
    font-size: 20px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--color-border);
}

.error-suggestions ul,
.error-recent-posts ul,
.error-categories ul {
    list-style: disc;
    padding-left: 20px;
}

.error-suggestions li,
.error-recent-posts li,
.error-categories li {
    margin-bottom: 10px;
}

.error-search .search-form {
    display: flex;
    gap: 10px;
}

.error-search .search-field {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius);
}

.error-search .search-submit {
    padding: 12px 24px;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
}

.error-search .search-submit:hover {
    background: var(--color-primary-dark);
}

@media (max-width: 768px) {
    .error-404 .page-title {
        font-size: 32px;
    }
    
    .error-search .search-form {
        flex-direction: column;
    }
}
</style>

<?php
get_footer();
