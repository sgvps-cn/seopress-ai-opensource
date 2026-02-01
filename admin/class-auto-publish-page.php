<?php
/**
 * 关键词自动发布管理页面
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Auto_Publish_Page {
    
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
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // AJAX 处理
        add_action( 'wp_ajax_seopress_generate_by_keyword', array( $this, 'ajax_generate_by_keyword' ) );
        add_action( 'wp_ajax_seopress_batch_generate', array( $this, 'ajax_batch_generate' ) );
        add_action( 'wp_ajax_seopress_save_keywords', array( $this, 'ajax_save_keywords' ) );
        add_action( 'wp_ajax_seopress_get_publish_status', array( $this, 'ajax_get_publish_status' ) );
        add_action( 'wp_ajax_seopress_save_auto_publish_settings', array( $this, 'ajax_save_settings' ) );
    }
    
    /**
     * 添加菜单页面
     */
    public function add_menu_page(): void {
        add_menu_page(
            __( '关键词发布', 'seopress-ai' ),
            __( '关键词发布', 'seopress-ai' ),
            'publish_posts',
            'seopress-auto-publish',
            array( $this, 'render_page' ),
            'dashicons-edit-page',
            26
        );
    }
    
    /**
     * 加载脚本
     */
    public function enqueue_scripts( $hook ): void {
        if ( $hook !== 'toplevel_page_seopress-auto-publish' ) {
            return;
        }
        
        wp_enqueue_style( 'seopress-auto-publish', get_template_directory_uri() . '/admin/css/auto-publish.css', array(), '1.0.0' );
    }
    
    /**
     * 渲染页面
     */
    public function render_page(): void {
        if ( ! current_user_can( 'publish_posts' ) ) {
            return;
        }
        
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        $keywords = isset( $settings['keywords'] ) ? $settings['keywords'] : '';
        $keywords_array = array_filter( array_map( 'trim', explode( "\n", $keywords ) ) );
        $publish_log = get_option( 'seopress_publish_log', array() );
        $publish_log = array_reverse( array_slice( $publish_log, -20 ) );
        
        // 获取已发布的关键词
        $published_keywords = $this->get_published_keywords();
        
        ?>
        <div class="wrap seopress-auto-publish-wrap">
            <h1>
                <span class="dashicons dashicons-edit-page" style="font-size: 30px; width: 30px; height: 30px; margin-right: 10px;"></span>
                <?php esc_html_e( '关键词自动发布', 'seopress-ai' ); ?>
            </h1>
            
            <div class="seopress-auto-publish-container">
                <!-- 左侧：关键词管理 -->
                <div class="seopress-panel seopress-keywords-panel">
                    <h2>
                        <span class="dashicons dashicons-tag"></span>
                        <?php esc_html_e( '关键词管理', 'seopress-ai' ); ?>
                    </h2>
                    
                    <form id="keywords-form">
                        <?php wp_nonce_field( 'seopress_admin', 'seopress_nonce' ); ?>
                        
                        <div class="seopress-field">
                            <label for="keywords"><?php esc_html_e( '关键词列表（每行一个）', 'seopress-ai' ); ?></label>
                            <textarea id="keywords" name="keywords" rows="10" placeholder="WordPress SEO优化&#10;如何提高网站排名&#10;网站内容营销策略&#10;..."><?php echo esc_textarea( $keywords ); ?></textarea>
                            <p class="description">
                                <?php printf( 
                                    esc_html__( '共 %d 个关键词，已发布 %d 个', 'seopress-ai' ),
                                    count( $keywords_array ),
                                    count( $published_keywords )
                                ); ?>
                            </p>
                        </div>
                        
                        <div class="seopress-field">
                            <label for="default_category"><?php esc_html_e( '默认发布分类', 'seopress-ai' ); ?></label>
                            <?php
                            wp_dropdown_categories( array(
                                'name'             => 'default_category',
                                'id'               => 'default_category',
                                'selected'         => isset( $settings['default_category'] ) ? $settings['default_category'] : 0,
                                'show_option_none' => __( '选择分类', 'seopress-ai' ),
                                'hide_empty'       => false,
                            ) );
                            ?>
                        </div>
                        
                        <div class="seopress-actions">
                            <button type="submit" class="button button-primary">
                                <span class="dashicons dashicons-saved"></span>
                                <?php esc_html_e( '保存关键词', 'seopress-ai' ); ?>
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <h3>
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php esc_html_e( '快速生成', 'seopress-ai' ); ?>
                    </h3>
                    
                    <div class="seopress-field">
                        <label for="single_keyword"><?php esc_html_e( '单个关键词快速生成', 'seopress-ai' ); ?></label>
                        <div class="seopress-input-group">
                            <input type="text" id="single_keyword" placeholder="输入关键词...">
                            <button type="button" id="generate-single-btn" class="button button-primary">
                                <span class="dashicons dashicons-admin-post"></span>
                                <?php esc_html_e( '生成并发布', 'seopress-ai' ); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="seopress-field">
                        <label><?php esc_html_e( '批量生成（从关键词列表）', 'seopress-ai' ); ?></label>
                        <div class="seopress-batch-controls">
                            <input type="number" id="batch_count" min="1" max="10" value="1" style="width: 80px;">
                            <span><?php esc_html_e( '篇文章', 'seopress-ai' ); ?></span>
                            <button type="button" id="generate-batch-btn" class="button">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e( '开始批量生成', 'seopress-ai' ); ?>
                            </button>
                        </div>
                        <p class="description"><?php esc_html_e( '将从未发布的关键词中随机选择生成', 'seopress-ai' ); ?></p>
                    </div>
                </div>
                
                <!-- 右侧：状态和日志 -->
                <div class="seopress-panel seopress-status-panel">
                    <h2>
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php esc_html_e( '发布状态', 'seopress-ai' ); ?>
                    </h2>
                    
                    <!-- 进度显示 -->
                    <div id="publish-progress" class="seopress-progress" style="display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%;"></div>
                        </div>
                        <div class="progress-text">准备中...</div>
                    </div>
                    
                    <!-- 当前任务状态 -->
                    <div id="current-task" class="seopress-current-task" style="display: none;">
                        <div class="task-icon">
                            <span class="dashicons dashicons-update spinning"></span>
                        </div>
                        <div class="task-info">
                            <div class="task-title">正在生成...</div>
                            <div class="task-keyword"></div>
                        </div>
                    </div>
                    
                    <!-- 统计信息 -->
                    <div class="seopress-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count( $keywords_array ); ?></span>
                            <span class="stat-label"><?php esc_html_e( '总关键词', 'seopress-ai' ); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count( $published_keywords ); ?></span>
                            <span class="stat-label"><?php esc_html_e( '已发布', 'seopress-ai' ); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo max( 0, count( $keywords_array ) - count( $published_keywords ) ); ?></span>
                            <span class="stat-label"><?php esc_html_e( '待发布', 'seopress-ai' ); ?></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h3>
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e( '最近发布记录', 'seopress-ai' ); ?>
                    </h3>
                    
                    <div id="publish-log" class="seopress-publish-log">
                        <?php if ( empty( $publish_log ) ) : ?>
                            <p class="no-records"><?php esc_html_e( '暂无发布记录', 'seopress-ai' ); ?></p>
                        <?php else : ?>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( '时间', 'seopress-ai' ); ?></th>
                                        <th><?php esc_html_e( '关键词', 'seopress-ai' ); ?></th>
                                        <th><?php esc_html_e( '标题', 'seopress-ai' ); ?></th>
                                        <th><?php esc_html_e( '状态', 'seopress-ai' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $publish_log as $log ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $log['time'] ); ?></td>
                                            <td><?php echo esc_html( $log['keyword'] ?? '-' ); ?></td>
                                            <td><?php echo esc_html( mb_substr( $log['title'] ?? '-', 0, 30 ) ); ?></td>
                                            <td>
                                                <?php if ( $log['status'] === 'success' ) : ?>
                                                    <span class="status-success">✓ 成功</span>
                                                <?php else : ?>
                                                    <span class="status-error">✗ 失败</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 定时发布设置 -->
            <div class="seopress-panel seopress-schedule-panel">
                <h2>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e( '定时自动发布', 'seopress-ai' ); ?>
                </h2>
                
                <form id="schedule-form">
                    <?php wp_nonce_field( 'seopress_admin', 'seopress_schedule_nonce' ); ?>
                    
                    <div class="seopress-schedule-fields">
                        <div class="seopress-field">
                            <label>
                                <input type="checkbox" name="auto_enabled" id="auto_enabled" value="1" 
                                    <?php checked( ! empty( $settings['enabled'] ) ); ?>>
                                <?php esc_html_e( '启用定时自动发布', 'seopress-ai' ); ?>
                            </label>
                        </div>
                        
                        <div class="seopress-field">
                            <label for="publish_interval"><?php esc_html_e( '发布间隔', 'seopress-ai' ); ?></label>
                            <select name="interval" id="publish_interval">
                                <option value="hourly" <?php selected( $settings['interval'] ?? '', 'hourly' ); ?>><?php esc_html_e( '每小时', 'seopress-ai' ); ?></option>
                                <option value="every_6_hours" <?php selected( $settings['interval'] ?? '', 'every_6_hours' ); ?>><?php esc_html_e( '每6小时', 'seopress-ai' ); ?></option>
                                <option value="twicedaily" <?php selected( $settings['interval'] ?? '', 'twicedaily' ); ?>><?php esc_html_e( '每天两次', 'seopress-ai' ); ?></option>
                                <option value="daily" <?php selected( $settings['interval'] ?? 'daily', 'daily' ); ?>><?php esc_html_e( '每天一次', 'seopress-ai' ); ?></option>
                            </select>
                        </div>
                        
                        <div class="seopress-field">
                            <label for="auto_count"><?php esc_html_e( '每次发布篇数', 'seopress-ai' ); ?></label>
                            <input type="number" name="auto_count" id="auto_count" min="1" max="5" 
                                value="<?php echo esc_attr( $settings['auto_count'] ?? 1 ); ?>" style="width: 80px;">
                            <p class="description"><?php esc_html_e( '定时执行时从待发布关键词中随机选择生成', 'seopress-ai' ); ?></p>
                        </div>
                        
                        <div class="seopress-field">
                            <label>
                                <input type="checkbox" name="auto_push_baidu" id="auto_push_baidu" value="1" 
                                    <?php checked( ! empty( $settings['auto_push_baidu'] ) ); ?>>
                                <?php esc_html_e( '发布后自动推送到百度', 'seopress-ai' ); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="seopress-actions">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e( '保存定时设置', 'seopress-ai' ); ?>
                        </button>
                        
                        <?php 
                        $next_run = wp_next_scheduled( 'seopress_auto_publish' );
                        if ( $next_run ) :
                        ?>
                            <span class="next-run-info">
                                <?php printf( 
                                    esc_html__( '下次执行：%s', 'seopress-ai' ),
                                    date_i18n( 'Y-m-d H:i:s', $next_run )
                                ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- 提示词模板 -->
            <div class="seopress-panel seopress-template-panel">
                <h2>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e( '提示词模板', 'seopress-ai' ); ?>
                </h2>
                
                <form id="template-form">
                    <?php wp_nonce_field( 'seopress_admin', 'seopress_template_nonce' ); ?>
                    
                    <div class="seopress-field">
                        <label for="prompt_template"><?php esc_html_e( '文章生成提示词', 'seopress-ai' ); ?></label>
                        <textarea id="prompt_template" name="prompt_template" rows="8"><?php 
                            echo esc_textarea( $settings['prompt_template'] ?? '请围绕关键词"{keyword}"撰写一篇原创的SEO优化文章。

要求：
1. 文章字数不少于1500字
2. 包含合理的H2、H3标题结构
3. 自然融入关键词，密度控制在2%-3%
4. 内容具有实用价值，语言流畅自然
5. 结尾包含总结和行动建议

请直接输出文章内容，第一行为标题。' ); 
                        ?></textarea>
                        <p class="description"><?php esc_html_e( '使用 {keyword} 作为关键词占位符', 'seopress-ai' ); ?></p>
                    </div>
                    
                    <div class="seopress-actions">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e( '保存模板', 'seopress-ai' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var isGenerating = false;
            
            // 保存关键词
            $('#keywords-form').on('submit', function(e) {
                e.preventDefault();
                
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> 保存中...');
                
                $.post(ajaxurl, {
                    action: 'seopress_save_keywords',
                    nonce: $('#seopress_nonce').val(),
                    keywords: $('#keywords').val(),
                    default_category: $('#default_category').val()
                }, function(response) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> 保存关键词');
                    
                    if (response.success) {
                        showNotice('关键词已保存', 'success');
                    } else {
                        showNotice(response.data.message || '保存失败', 'error');
                    }
                });
            });
            
            // 单个关键词生成
            $('#generate-single-btn').on('click', function() {
                var keyword = $('#single_keyword').val().trim();
                
                if (!keyword) {
                    showNotice('请输入关键词', 'error');
                    return;
                }
                
                if (isGenerating) {
                    showNotice('正在生成中，请稍候...', 'error');
                    return;
                }
                
                generateArticle(keyword);
            });
            
            // 批量生成
            $('#generate-batch-btn').on('click', function() {
                var count = parseInt($('#batch_count').val()) || 1;
                
                if (isGenerating) {
                    showNotice('正在生成中，请稍候...', 'error');
                    return;
                }
                
                batchGenerate(count);
            });
            
            // 保存定时设置
            $('#schedule-form').on('submit', function(e) {
                e.preventDefault();
                
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('保存中...');
                
                $.post(ajaxurl, {
                    action: 'seopress_save_auto_publish_settings',
                    nonce: $('#seopress_schedule_nonce').val(),
                    enabled: $('#auto_enabled').is(':checked') ? 1 : 0,
                    interval: $('#publish_interval').val(),
                    auto_count: $('#auto_count').val(),
                    auto_push_baidu: $('#auto_push_baidu').is(':checked') ? 1 : 0
                }, function(response) {
                    $btn.prop('disabled', false).text('保存定时设置');
                    
                    if (response.success) {
                        showNotice('设置已保存', 'success');
                    } else {
                        showNotice(response.data.message || '保存失败', 'error');
                    }
                });
            });
            
            // 保存模板
            $('#template-form').on('submit', function(e) {
                e.preventDefault();
                
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('保存中...');
                
                $.post(ajaxurl, {
                    action: 'seopress_save_auto_publish_settings',
                    nonce: $('#seopress_template_nonce').val(),
                    prompt_template: $('#prompt_template').val()
                }, function(response) {
                    $btn.prop('disabled', false).text('保存模板');
                    
                    if (response.success) {
                        showNotice('模板已保存', 'success');
                    } else {
                        showNotice(response.data.message || '保存失败', 'error');
                    }
                });
            });
            
            // 生成单篇文章
            function generateArticle(keyword) {
                isGenerating = true;
                
                showProgress(true);
                updateProgress(0, '正在生成: ' + keyword);
                showCurrentTask(keyword);
                
                $.post(ajaxurl, {
                    action: 'seopress_generate_by_keyword',
                    nonce: $('#seopress_nonce').val(),
                    keyword: keyword,
                    category: $('#default_category').val()
                }, function(response) {
                    isGenerating = false;
                    hideCurrentTask();
                    
                    if (response.success) {
                        updateProgress(100, '完成!');
                        showNotice('文章已发布: ' + response.data.title, 'success');
                        addLogEntry(response.data);
                        
                        setTimeout(function() {
                            showProgress(false);
                        }, 2000);
                    } else {
                        showProgress(false);
                        showNotice('生成失败: ' + (response.data.message || '未知错误'), 'error');
                    }
                }).fail(function() {
                    isGenerating = false;
                    hideCurrentTask();
                    showProgress(false);
                    showNotice('请求失败，请重试', 'error');
                });
            }
            
            // 批量生成
            function batchGenerate(count) {
                isGenerating = true;
                
                showProgress(true);
                updateProgress(0, '准备批量生成 ' + count + ' 篇文章...');
                
                $.post(ajaxurl, {
                    action: 'seopress_batch_generate',
                    nonce: $('#seopress_nonce').val(),
                    count: count,
                    category: $('#default_category').val()
                }, function(response) {
                    isGenerating = false;
                    hideCurrentTask();
                    
                    if (response.success) {
                        updateProgress(100, '批量生成完成!');
                        showNotice('成功生成 ' + response.data.success_count + ' 篇文章', 'success');
                        
                        // 刷新页面以显示最新日志
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showProgress(false);
                        showNotice('批量生成失败: ' + (response.data.message || '未知错误'), 'error');
                    }
                }).fail(function() {
                    isGenerating = false;
                    hideCurrentTask();
                    showProgress(false);
                    showNotice('请求失败，请重试', 'error');
                });
            }
            
            // 显示/隐藏进度条
            function showProgress(show) {
                if (show) {
                    $('#publish-progress').slideDown();
                } else {
                    $('#publish-progress').slideUp();
                }
            }
            
            // 更新进度
            function updateProgress(percent, text) {
                $('#publish-progress .progress-fill').css('width', percent + '%');
                $('#publish-progress .progress-text').text(text);
            }
            
            // 显示当前任务
            function showCurrentTask(keyword) {
                $('#current-task').show();
                $('#current-task .task-keyword').text(keyword);
            }
            
            // 隐藏当前任务
            function hideCurrentTask() {
                $('#current-task').hide();
            }
            
            // 添加日志条目
            function addLogEntry(data) {
                var html = '<tr>' +
                    '<td>' + data.time + '</td>' +
                    '<td>' + data.keyword + '</td>' +
                    '<td>' + data.title.substring(0, 30) + '</td>' +
                    '<td><span class="status-success">✓ 成功</span></td>' +
                    '</tr>';
                
                var $table = $('#publish-log table tbody');
                if ($table.length === 0) {
                    $('#publish-log').html('<table class="widefat"><thead><tr><th>时间</th><th>关键词</th><th>标题</th><th>状态</th></tr></thead><tbody></tbody></table>');
                    $table = $('#publish-log table tbody');
                }
                
                $table.prepend(html);
                
                // 只保留20条
                $table.find('tr:gt(19)').remove();
            }
            
            // 显示通知
            function showNotice(message, type) {
                var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
                $('.seopress-auto-publish-wrap h1').after($notice);
                
                setTimeout(function() {
                    $notice.fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });
        </script>
        <?php
    }
    
    /**
     * 获取已发布的关键词列表
     */
    private function get_published_keywords(): array {
        global $wpdb;
        
        $results = $wpdb->get_col(
            "SELECT meta_value FROM {$wpdb->postmeta} 
             WHERE meta_key = '_seopress_keyword' 
             AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish')"
        );
        
        return array_filter( $results );
    }
    
    /**
     * AJAX: 根据关键词生成文章
     */
    public function ajax_generate_by_keyword(): void {
        check_ajax_referer( 'seopress_admin', 'nonce' );
        
        if ( ! current_user_can( 'publish_posts' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
        $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        
        if ( empty( $keyword ) ) {
            wp_send_json_error( array( 'message' => '请输入关键词' ) );
        }
        
        // 获取设置
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        
        // 构建提示词
        $template = isset( $settings['prompt_template'] ) && ! empty( $settings['prompt_template'] ) 
            ? $settings['prompt_template'] 
            : '请围绕关键词"{keyword}"撰写一篇原创的SEO优化文章。

要求：
1. 文章字数不少于1500字
2. 包含合理的H2、H3标题结构
3. 自然融入关键词，密度控制在2%-3%
4. 内容具有实用价值，语言流畅自然
5. 结尾包含总结和行动建议

请直接输出文章内容，第一行为标题。';
        
        $prompt = str_replace( '{keyword}', $keyword, $template );
        
        // 调用 AI 生成
        $ai_manager = SeoPress_AI_Manager::get_instance();
        $result = $ai_manager->generate_content( $prompt );
        
        if ( ! $result['success'] ) {
            wp_send_json_error( array( 'message' => $result['error'] ?? 'AI 生成失败' ) );
        }
        
        $content = $result['content'];
        
        // 提取标题
        $title = $this->extract_title( $content, $keyword );
        
        // 从内容中移除标题行
        $content = $this->remove_title_from_content( $content );
        
        // 创建文章
        $post_data = array(
            'post_title'   => $title,
            'post_content' => wp_kses_post( $content ),
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'post_type'    => 'post',
        );
        
        if ( $category > 0 ) {
            $post_data['post_category'] = array( $category );
        }
        
        $post_id = wp_insert_post( $post_data );
        
        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }
        
        // 保存元数据
        update_post_meta( $post_id, '_seopress_keyword', $keyword );
        update_post_meta( $post_id, '_seopress_ai_generated', 1 );
        update_post_meta( $post_id, '_seopress_generated_at', current_time( 'mysql' ) );
        
        // 生成 SEO Meta
        $description = wp_trim_words( strip_tags( $content ), 30, '...' );
        update_post_meta( $post_id, '_seopress_meta_description', $description );
        update_post_meta( $post_id, '_seopress_focus_keyword', $keyword );
        
        // 自动设置缩略图
        if ( class_exists( 'SeoPress_AI_Unsplash' ) ) {
            $unsplash = SeoPress_AI_Unsplash::get_instance();
            $unsplash->set_featured_image( $post_id, $keyword );
        }
        
        // 记录日志
        $this->log_publish( $keyword, $title, 'success' );
        
        // 推送到百度（如果启用）
        if ( ! empty( $settings['auto_push_baidu'] ) ) {
            if ( class_exists( 'SeoPress_AI_Baidu_Push' ) ) {
                $baidu_push = SeoPress_AI_Baidu_Push::get_instance();
                $baidu_push->push_url( get_permalink( $post_id ) );
            }
        }
        
        wp_send_json_success( array(
            'post_id'  => $post_id,
            'title'    => $title,
            'keyword'  => $keyword,
            'time'     => current_time( 'Y-m-d H:i:s' ),
            'edit_url' => get_edit_post_link( $post_id, '' ),
            'view_url' => get_permalink( $post_id ),
        ) );
    }
    
    /**
     * AJAX: 批量生成
     */
    public function ajax_batch_generate(): void {
        check_ajax_referer( 'seopress_admin', 'nonce' );
        
        if ( ! current_user_can( 'publish_posts' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $count = isset( $_POST['count'] ) ? min( 10, max( 1, intval( $_POST['count'] ) ) ) : 1;
        $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        
        // 获取关键词列表
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        $keywords = isset( $settings['keywords'] ) ? $settings['keywords'] : '';
        $keywords_array = array_filter( array_map( 'trim', explode( "\n", $keywords ) ) );
        
        if ( empty( $keywords_array ) ) {
            wp_send_json_error( array( 'message' => '关键词列表为空' ) );
        }
        
        // 获取已发布的关键词
        $published = $this->get_published_keywords();
        
        // 筛选未发布的关键词
        $unpublished = array_diff( $keywords_array, $published );
        
        if ( empty( $unpublished ) ) {
            wp_send_json_error( array( 'message' => '所有关键词都已发布' ) );
        }
        
        // 随机选择关键词
        shuffle( $unpublished );
        $selected = array_slice( $unpublished, 0, $count );
        
        $success_count = 0;
        $results = array();
        
        foreach ( $selected as $keyword ) {
            // 模拟 POST 数据
            $_POST['keyword'] = $keyword;
            $_POST['category'] = $category;
            
            // 调用单个生成方法
            ob_start();
            $this->ajax_generate_by_keyword();
            $response = ob_get_clean();
            
            // 解析响应
            $data = json_decode( $response, true );
            if ( isset( $data['success'] ) && $data['success'] ) {
                $success_count++;
                $results[] = $data['data'];
            }
            
            // 添加延迟避免 API 限流
            if ( count( $selected ) > 1 ) {
                sleep( 2 );
            }
        }
        
        wp_send_json_success( array(
            'success_count' => $success_count,
            'total'         => count( $selected ),
            'results'       => $results,
        ) );
    }
    
    /**
     * AJAX: 保存关键词
     */
    public function ajax_save_keywords(): void {
        check_ajax_referer( 'seopress_admin', 'nonce' );
        
        if ( ! current_user_can( 'publish_posts' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $keywords = isset( $_POST['keywords'] ) ? sanitize_textarea_field( $_POST['keywords'] ) : '';
        $category = isset( $_POST['default_category'] ) ? intval( $_POST['default_category'] ) : 0;
        
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        $settings['keywords'] = $keywords;
        $settings['default_category'] = $category;
        
        update_option( 'seopress_auto_publish_settings', $settings );
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: 保存设置
     */
    public function ajax_save_settings(): void {
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'seopress_admin' ) ) {
            wp_send_json_error( array( 'message' => '安全验证失败' ) );
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        
        // 更新各个字段
        if ( isset( $_POST['enabled'] ) ) {
            $settings['enabled'] = (bool) $_POST['enabled'];
        }
        if ( isset( $_POST['interval'] ) ) {
            $settings['interval'] = sanitize_key( $_POST['interval'] );
        }
        if ( isset( $_POST['auto_count'] ) ) {
            $settings['auto_count'] = min( 5, max( 1, intval( $_POST['auto_count'] ) ) );
        }
        if ( isset( $_POST['auto_push_baidu'] ) ) {
            $settings['auto_push_baidu'] = (bool) $_POST['auto_push_baidu'];
        }
        if ( isset( $_POST['prompt_template'] ) ) {
            $settings['prompt_template'] = sanitize_textarea_field( $_POST['prompt_template'] );
        }
        
        update_option( 'seopress_auto_publish_settings', $settings );
        
        // 重新调度 Cron
        $auto_publish = SeoPress_Auto_Publish::get_instance();
        $auto_publish->reschedule_cron();
        
        wp_send_json_success();
    }
    
    /**
     * 从内容提取标题
     */
    private function extract_title( string $content, string $keyword ): string {
        $lines = explode( "\n", trim( $content ) );
        $first_line = trim( $lines[0] );
        
        // 移除 Markdown 标题标记
        $first_line = preg_replace( '/^#+\s*/', '', $first_line );
        
        // 移除可能的引号
        $first_line = trim( $first_line, '"\'""' );
        
        if ( mb_strlen( $first_line ) > 10 && mb_strlen( $first_line ) < 100 ) {
            return $first_line;
        }
        
        return $keyword;
    }
    
    /**
     * 从内容移除标题行
     */
    private function remove_title_from_content( string $content ): string {
        $lines = explode( "\n", $content );
        
        // 移除第一行（标题）
        if ( count( $lines ) > 1 ) {
            array_shift( $lines );
            // 移除可能的空行
            while ( ! empty( $lines ) && trim( $lines[0] ) === '' ) {
                array_shift( $lines );
            }
        }
        
        return implode( "\n", $lines );
    }
    
    /**
     * 记录发布日志
     */
    private function log_publish( string $keyword, string $title, string $status ): void {
        $log = get_option( 'seopress_publish_log', array() );
        
        $log[] = array(
            'time'    => current_time( 'Y-m-d H:i:s' ),
            'keyword' => $keyword,
            'title'   => $title,
            'status'  => $status,
        );
        
        // 只保留最近100条
        if ( count( $log ) > 100 ) {
            $log = array_slice( $log, -100 );
        }
        
        update_option( 'seopress_publish_log', $log );
    }
}

// 初始化
add_action( 'init', function() {
    if ( is_admin() ) {
        SeoPress_AI_Auto_Publish_Page::get_instance();
    }
} );
