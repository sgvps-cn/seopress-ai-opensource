<?php
/**
 * 评论模板
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 如果文章受密码保护且未输入密码，则不显示评论
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if ( have_comments() ) : ?>
        <h3 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            printf(
                /* translators: %s: 评论数量 */
                esc_html( _n( '%s 条评论', '%s 条评论', $comments_number, 'seopress-ai' ) ),
                number_format_i18n( $comments_number )
            );
            ?>
        </h3>

        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => 'seopress_ai_comment_callback',
            ) );
            ?>
        </ol>

        <?php
        // 评论分页导航
        the_comments_navigation( array(
            'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg> ' . esc_html__( '较早的评论', 'seopress-ai' ),
            'next_text' => esc_html__( '较新的评论', 'seopress-ai' ) . ' <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>',
        ) );
        ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="comments-closed">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <?php esc_html_e( '评论已关闭。', 'seopress-ai' ); ?>
        </p>
    <?php endif; ?>

    <?php
    // 自定义评论表单
    $commenter = wp_get_current_commenter();
    $req       = get_option( 'require_name_email' );
    $aria_req  = ( $req ? " aria-required='true' required" : '' );

    $comment_form_args = array(
        'title_reply'          => esc_html__( '发表评论', 'seopress-ai' ),
        'title_reply_to'       => esc_html__( '回复 %s', 'seopress-ai' ),
        'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
        'title_reply_after'    => '</h3>',
        'cancel_reply_before'  => '',
        'cancel_reply_after'   => '',
        'cancel_reply_link'    => esc_html__( '取消回复', 'seopress-ai' ),
        'label_submit'         => esc_html__( '发表评论', 'seopress-ai' ),
        'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
        'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
        'comment_notes_before' => '<p class="comment-notes">' . esc_html__( '您的邮箱地址不会被公开。', 'seopress-ai' ) . ( $req ? ' <span class="required-field-message"><span class="required">*</span> ' . esc_html__( '表示必填项', 'seopress-ai' ) . '</span>' : '' ) . '</p>',
        'comment_field'        => '<div class="comment-form-comment"><label for="comment">' . esc_html__( '评论内容', 'seopress-ai' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" aria-required="true" required placeholder="' . esc_attr__( '写下你的想法...', 'seopress-ai' ) . '"></textarea></div>',
        'fields'               => array(
            'author' => '<div class="comment-form-author"><label for="author">' . esc_html__( '昵称', 'seopress-ai' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' placeholder="' . esc_attr__( '你的名字', 'seopress-ai' ) . '" /></div>',
            'email'  => '<div class="comment-form-email"><label for="email">' . esc_html__( '邮箱', 'seopress-ai' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' placeholder="' . esc_attr__( 'your@email.com', 'seopress-ai' ) . '" /></div>',
            'url'    => '<div class="comment-form-url"><label for="url">' . esc_html__( '网站', 'seopress-ai' ) . '</label><input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" placeholder="' . esc_attr__( 'https://yoursite.com', 'seopress-ai' ) . '" /></div>',
            'cookies' => '<div class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' ) . ' /><label for="wp-comment-cookies-consent">' . esc_html__( '保存我的信息，以便下次评论时使用', 'seopress-ai' ) . '</label></div>',
        ),
    );

    comment_form( $comment_form_args );
    ?>
</div>
