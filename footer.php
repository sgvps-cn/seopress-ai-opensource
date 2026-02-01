<?php
/**
 * 页脚模板 - 现代精美设计
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
    </div><!-- .sp-container -->
</main><!-- .site-main -->

<footer class="site-footer" role="contentinfo">
    <div class="sp-container">
        <!-- 页脚主体 -->
        <div class="footer-main">
            <!-- 品牌区域 -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <?php 
                        $site_name = get_bloginfo( 'name' );
                        echo esc_html( mb_substr( $site_name, 0, 1 ) );
                        ?>
                    </div>
                    <span class="footer-logo-text"><?php bloginfo( 'name' ); ?></span>
                </div>
                <p class="footer-description">
                    <?php 
                    $description = get_bloginfo( 'description' );
                    if ( $description ) {
                        echo esc_html( $description );
                    } else {
                        esc_html_e( '专注于分享技术、生活与思考的个人博客。', 'seopress-ai' );
                    }
                    ?>
                </p>
                <?php
                $settings = SeoPress_AI_Settings::get_instance();
                $github_link = $settings->get('github_link');
                $qq_link = $settings->get('qq_link');
                $email_address = $settings->get('email_address');
                ?>
                <div class="footer-social">
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
                    <a href="#" aria-label="微信" title="微信">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.49.49 0 0 1 .177-.554C23.016 18.264 24 16.585 24 14.71c0-3.376-3.135-6.028-7.062-5.852zm-2.384 2.677c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982z"/>
                        </svg>
                    </a>
                    <a href="<?php echo esc_url( get_feed_link() ); ?>" aria-label="RSS" title="RSS订阅">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 快速链接 -->
            <div class="footer-section">
                <h4><?php esc_html_e( '快速链接', 'seopress-ai' ); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'seopress-ai' ); ?></a></li>
                    <?php
                    $categories = get_categories( array( 'number' => 4, 'hide_empty' => true ) );
                    foreach ( $categories as $category ) :
                    ?>
                        <li><a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- 最新文章 -->
            <div class="footer-section">
                <h4><?php esc_html_e( '最新文章', 'seopress-ai' ); ?></h4>
                <ul class="footer-links">
                    <?php
                    $recent_posts = wp_get_recent_posts( array(
                        'numberposts' => 4,
                        'post_status' => 'publish',
                    ) );
                    foreach ( $recent_posts as $post ) :
                    ?>
                        <li><a href="<?php echo esc_url( get_permalink( $post['ID'] ) ); ?>"><?php echo esc_html( wp_trim_words( $post['post_title'], 10 ) ); ?></a></li>
                    <?php endforeach; wp_reset_postdata(); ?>
                </ul>
            </div>

            <!-- 联系方式 -->
            <div class="footer-section">
                <h4><?php esc_html_e( '联系我们', 'seopress-ai' ); ?></h4>
                <ul class="footer-links">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <a href="mailto:<?php echo esc_attr( $email_address ? $email_address : 'admin@example.com' ); ?>"><?php echo esc_html( $email_address ? $email_address : 'admin@example.com' ); ?></a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?php esc_html_e( '中国', 'seopress-ai' ); ?>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 页脚底部 -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                © <?php echo esc_html( date( 'Y' ) ); ?> 
                <a href="https://github.com/sgvps-cn/seopress-ai-opensource" target="_blank"><?php bloginfo( 'name' ); ?></a>
                <?php esc_html_e( ' · 保留所有权利', 'seopress-ai' ); ?>
            </div>
            <div class="footer-meta">
                <a href="#"><?php esc_html_e( '隐私政策', 'seopress-ai' ); ?></a>
                <a href="#"><?php esc_html_e( '使用条款', 'seopress-ai' ); ?></a>
                <a href="<?php echo esc_url( get_feed_link() ); ?>">RSS</a>
            </div>
        </div>
    </div>
</footer>

<!-- 回到顶部按钮 -->
<button class="back-to-top" id="back-to-top" aria-label="<?php esc_attr_e( '回到顶部', 'seopress-ai' ); ?>">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m18 15-6-6-6 6"/>
    </svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
