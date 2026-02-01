<?php
/**
 * 后台管理页面
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Admin_Page {
    
    /**
     * 设置实例
     */
    private $settings;
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->settings = SeoPress_AI_Settings::get_instance();
        
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'wp_ajax_seopress_save_settings', array( $this, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_seopress_test_ai', array( $this, 'ajax_test_ai' ) );
        add_action( 'wp_ajax_seopress_generate_article', array( $this, 'ajax_generate_article' ) );
        add_action( 'wp_ajax_seopress_save_auto_publish_config', array( $this, 'ajax_save_auto_publish_config' ) );
    }
    
    /**
     * 添加菜单页面
     */
    public function add_menu_page(): void {
        add_theme_page(
            __( 'SeoPress AI 设置', 'seopress-ai' ),
            __( 'SeoPress AI', 'seopress-ai' ),
            'manage_options',
            'seopress-ai-settings',
            array( $this, 'render_settings_page' )
        );
    }
    
    /**
     * 渲染设置页面
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $settings_fields = $this->settings->get_settings_fields();
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
        
        ?>
        <div class="wrap seopress-admin-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <?php foreach ( $settings_fields as $tab_id => $tab ) : ?>
                    <a href="?page=seopress-ai-settings&tab=<?php echo esc_attr( $tab_id ); ?>" 
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab['title'] ); ?>
                    </a>
                <?php endforeach; ?>
                <a href="?page=seopress-ai-settings&tab=generator" 
                   class="nav-tab <?php echo $current_tab === 'generator' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'AI 文章生成', 'seopress-ai' ); ?>
                </a>
                <a href="?page=seopress-ai-settings&tab=tutorial" 
                   class="nav-tab <?php echo $current_tab === 'tutorial' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '配置教程', 'seopress-ai' ); ?>
                </a>
                <a href="?page=seopress-ai-settings&tab=auto_publish" 
                   class="nav-tab <?php echo $current_tab === 'auto_publish' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '自动发布设置', 'seopress-ai' ); ?>
                </a>
                <a href="?page=seopress-ai-settings&tab=management" 
                   class="nav-tab <?php echo $current_tab === 'management' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '更新检查', 'seopress-ai' ); ?>
                </a>
            </nav>

            <div class="seopress-admin-content">
                <?php
                if ( $current_tab === 'generator' ) {
                    $this->render_generator_page();
                } elseif ( $current_tab === 'tutorial' ) {
                    $this->render_tutorial_page();
                } elseif ( $current_tab === 'auto_publish' ) {
                    $this->render_auto_publish_page();
                } elseif ( $current_tab === 'management' ) {
                    $this->render_management_page();
                } elseif ( isset( $settings_fields[ $current_tab ] ) ) {
                    $this->render_settings_form( $current_tab, $settings_fields[ $current_tab ] );
                }
                ?>
            </div>
        </div>
        
        <style>
        .seopress-admin-wrap {
            max-width: 800px;
        }
        .seopress-admin-content {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .seopress-field {
            margin-bottom: 20px;
        }
        .seopress-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .seopress-field input[type="text"],
        .seopress-field input[type="password"],
        .seopress-field input[type="number"],
        .seopress-field select,
        .seopress-field textarea {
            width: 100%;
            max-width: 400px;
        }
        .seopress-field textarea {
            min-height: 100px;
        }
        .seopress-field .description {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        .seopress-field-checkbox label {
            font-weight: normal;
        }
        .seopress-actions {
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .seopress-notice {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .seopress-notice-success {
            background: #ecf7ed;
            border-color: #52c41a;
        }
        .seopress-notice-error {
            background: #fff2f0;
            border-color: #f5222d;
        }
        .seopress-generator-form textarea {
            width: 100%;
            min-height: 150px;
        }
        .seopress-generator-result {
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        .seopress-tutorial-section {
            margin-bottom: 30px;
        }
        .seopress-tutorial-section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .seopress-tutorial-section ol {
            margin-left: 20px;
        }
        .seopress-tutorial-section li {
            margin-bottom: 10px;
        }
        .seopress-tutorial-section code {
            background: #f1f1f1;
            padding: 2px 6px;
        }
        </style>
        <?php
    }
    
    /**
     * 渲染设置表单
     */
    private function render_settings_form( string $tab_id, array $tab_config ): void {
        ?>
        <form id="seopress-settings-form" method="post">
            <input type="hidden" name="tab" value="<?php echo esc_attr( $tab_id ); ?>">
            <?php wp_nonce_field( 'seopress_admin_nonce', 'seopress_nonce' ); ?>
            
            <div id="seopress-notice" class="seopress-notice" style="display:none;"></div>
            
            <?php foreach ( $tab_config['fields'] as $field ) : ?>
                <?php $this->render_field( $field ); ?>
            <?php endforeach; ?>
            
            <div class="seopress-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( '保存设置', 'seopress-ai' ); ?>
                </button>
                
                <?php if ( $tab_id === 'ai' ) : ?>
                    <button type="button" id="test-ai-btn" class="button">
                        <?php esc_html_e( '测试 AI 连接', 'seopress-ai' ); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ( $tab_id === 'seo' ) : ?>
                    <button type="button" id="test-push-btn" class="button">
                        <?php esc_html_e( '测试百度推送', 'seopress-ai' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            // 保存设置
            $('#seopress-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                var $btn = $(this).find('button[type="submit"]');
                var $notice = $('#seopress-notice');
                
                $btn.prop('disabled', true).text('保存中...');
                
                $.post(ajaxurl, {
                    action: 'seopress_save_settings',
                    nonce: $('#seopress_nonce').val(),
                    data: $(this).serialize()
                }, function(response) {
                    $btn.prop('disabled', false).text('保存设置');
                    
                    if (response.success) {
                        $notice.removeClass('seopress-notice-error')
                               .addClass('seopress-notice-success')
                               .text('设置已保存')
                               .show();
                    } else {
                        $notice.removeClass('seopress-notice-success')
                               .addClass('seopress-notice-error')
                               .text(response.data.message || '保存失败')
                               .show();
                    }
                    
                    setTimeout(function() {
                        $notice.fadeOut();
                    }, 3000);
                });
            });
            
            // 测试 AI 连接
            $('#test-ai-btn').on('click', function() {
                var $btn = $(this);
                var $notice = $('#seopress-notice');
                
                $btn.prop('disabled', true).text('测试中...');
                
                $.post(ajaxurl, {
                    action: 'seopress_test_ai',
                    nonce: seopressAdmin.nonce
                }, function(response) {
                    $btn.prop('disabled', false).text('测试 AI 连接');
                    
                    if (response.success) {
                        $notice.removeClass('seopress-notice-error')
                               .addClass('seopress-notice-success')
                               .text('AI 连接成功！')
                               .show();
                    } else {
                        $notice.removeClass('seopress-notice-success')
                               .addClass('seopress-notice-error')
                               .text('连接失败: ' + (response.data.message || '未知错误'))
                               .show();
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * 渲染单个字段
     */
    private function render_field( array $field ): void {
        $id = $field['id'];
        $value = $this->settings->get( $id );
        $type = $field['type'] ?? 'text';
        
        echo '<div class="seopress-field' . ( $type === 'checkbox' ? ' seopress-field-checkbox' : '' ) . '">';
        
        if ( $type !== 'checkbox' ) {
            echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
        }
        
        switch ( $type ) {
            case 'text':
            case 'password':
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s">',
                    esc_attr( $type ),
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_attr( $value )
                );
                break;
                
            case 'number':
                printf(
                    '<input type="number" id="%s" name="%s" value="%s" min="%s" max="%s" step="%s">',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_attr( $value ),
                    esc_attr( $field['min'] ?? 0 ),
                    esc_attr( $field['max'] ?? 100 ),
                    esc_attr( $field['step'] ?? 1 )
                );
                break;
                
            case 'textarea':
                printf(
                    '<textarea id="%s" name="%s">%s</textarea>',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_textarea( $value )
                );
                break;
                
            case 'select':
                echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
                foreach ( $field['options'] as $opt_value => $opt_label ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $opt_value ),
                        selected( $value, $opt_value, false ),
                        esc_html( $opt_label )
                    );
                }
                echo '</select>';
                break;
                
            case 'checkbox':
                printf(
                    '<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    checked( $value, true, false ),
                    esc_html( $field['label'] )
                );
                break;
                
            case 'category':
                wp_dropdown_categories( array(
                    'name'             => $id,
                    'id'               => $id,
                    'selected'         => $value,
                    'show_option_none' => __( '选择分类', 'seopress-ai' ),
                    'hide_empty'       => false,
                ) );
                break;
        }
        
        if ( ! empty( $field['description'] ) ) {
            echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * 渲染文章生成页面
     */
    private function render_generator_page(): void {
        ?>
        <h2><?php esc_html_e( 'AI 文章生成', 'seopress-ai' ); ?></h2>
        
        <form id="seopress-generator-form" class="seopress-generator-form">
            <?php wp_nonce_field( 'seopress_admin_nonce', 'seopress_nonce' ); ?>
            
            <div class="seopress-field">
                <label for="article_title"><?php esc_html_e( '文章标题', 'seopress-ai' ); ?></label>
                <input type="text" id="article_title" name="article_title" placeholder="输入文章标题">
            </div>
            
            <div class="seopress-field">
                <label for="article_prompt"><?php esc_html_e( '内容提示', 'seopress-ai' ); ?></label>
                <textarea id="article_prompt" name="article_prompt" placeholder="描述您想要生成的文章内容，例如：&#10;请写一篇关于WordPress SEO优化的文章，包含以下要点：&#10;1. 关键词研究&#10;2. 内容优化&#10;3. 技术SEO"></textarea>
            </div>
            
            <div class="seopress-field">
                <label for="article_category"><?php esc_html_e( '文章分类', 'seopress-ai' ); ?></label>
                <?php
                wp_dropdown_categories( array(
                    'name'             => 'article_category',
                    'id'               => 'article_category',
                    'show_option_none' => __( '选择分类', 'seopress-ai' ),
                    'hide_empty'       => false,
                ) );
                ?>
            </div>
            
            <div class="seopress-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( '生成文章', 'seopress-ai' ); ?>
                </button>
            </div>
        </form>
        
        <div id="generator-result" class="seopress-generator-result" style="display:none;">
            <h3><?php esc_html_e( '生成结果', 'seopress-ai' ); ?></h3>
            <div id="generated-content"></div>
            <div class="seopress-actions" style="margin-top:15px;">
                <button type="button" id="save-draft-btn" class="button button-primary">
                    <?php esc_html_e( '保存为草稿', 'seopress-ai' ); ?>
                </button>
                <button type="button" id="publish-btn" class="button">
                    <?php esc_html_e( '直接发布', 'seopress-ai' ); ?>
                </button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var generatedContent = '';
            
            $('#seopress-generator-form').on('submit', function(e) {
                e.preventDefault();
                
                var $btn = $(this).find('button[type="submit"]');
                var $result = $('#generator-result');
                var $content = $('#generated-content');
                
                $btn.prop('disabled', true).text('生成中，请稍候...');
                $result.hide();
                
                $.post(ajaxurl, {
                    action: 'seopress_generate_article',
                    nonce: $('#seopress_nonce').val(),
                    title: $('#article_title').val(),
                    prompt: $('#article_prompt').val(),
                    category: $('#article_category').val()
                }, function(response) {
                    $btn.prop('disabled', false).text('生成文章');
                    
                    if (response.success) {
                        generatedContent = response.data.content;
                        $content.html('<pre style="white-space: pre-wrap;">' + generatedContent + '</pre>');
                        $result.show();
                    } else {
                        alert('生成失败: ' + (response.data.message || '未知错误'));
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * 渲染配置教程页面
     */
    private function render_tutorial_page(): void {
        ?>
        <h2><?php esc_html_e( 'AI 服务配置教程', 'seopress-ai' ); ?></h2>
        
        <div class="seopress-tutorial-section" style="background:#e6f7e6;border:1px solid #52c41a;padding:15px;border-radius:4px;margin-bottom:20px;">
            <h3 style="color:#52c41a;margin-top:0;">🌟 硅基流动 SiliconFlow（强烈推荐 - 永久免费）</h3>
            <p>国内最良心的AI模型聚合平台，<strong style="color:#f5222d;">9B以下模型永久免费、无限使用</strong>，包含 Qwen2.5、DeepSeek-V2.5、Llama 3.1 等主流开源模型。</p>
            <ol>
                <li>访问 <a href="https://cloud.siliconflow.cn/" target="_blank">cloud.siliconflow.cn</a></li>
                <li>使用手机号注册账号并登录</li>
                <li>点击左侧菜单 "API密钥" → "新建API密钥"</li>
                <li>复制生成的 API Key 并粘贴到本主题的 AI 设置中</li>
                <li>在 AI 服务提供商选择 "硅基流动（免费推荐）"</li>
            </ol>
            <p><strong>推荐模型：</strong>Qwen2.5-7B-Instruct（中文能力强）、DeepSeek-V2.5（推理能力强）</p>
        </div>
        
        <div class="seopress-tutorial-section" style="background:#e6f4ff;border:1px solid #1890ff;padding:15px;border-radius:4px;margin-bottom:20px;">
            <h3 style="color:#1890ff;margin-top:0;">⚡ Groq（极速免费 - 国际）</h3>
            <p>全球推理速度最快的AI平台，<strong>免费层级</strong>可满足大多数使用场景。需要科学上网访问。</p>
            <ol>
                <li>访问 <a href="https://console.groq.com/" target="_blank">console.groq.com</a>（需科学上网）</li>
                <li>使用 Google 账号或邮箱注册登录</li>
                <li>点击左侧 "API Keys" → "Create API Key"</li>
                <li>复制 API Key 粘贴到设置中</li>
                <li>在 AI 服务提供商选择 "Groq（极速免费）"</li>
            </ol>
            <p><strong>推荐模型：</strong>Llama 3.1 8B（速度最快）、Llama 3.1 70B（效果更好）</p>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>3. DeepSeek</h3>
            <p>DeepSeek 提供高质量的中文内容生成能力，价格极低。</p>
            <ol>
                <li>访问 <a href="https://platform.deepseek.com" target="_blank">platform.deepseek.com</a></li>
                <li>注册账号并登录</li>
                <li>在左侧菜单找到 "API Keys"</li>
                <li>点击 "创建 API Key"</li>
                <li>复制生成的 API Key 并粘贴到本主题的设置中</li>
            </ol>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>4. 通义千问（阿里云）</h3>
            <p>阿里云百炼平台提供通义千问模型，新用户在新加坡地域有免费额度。</p>
            <ol>
                <li>访问 <a href="https://bailian.console.aliyun.com" target="_blank">阿里云百炼平台</a></li>
                <li>使用阿里云账号登录（需实名认证）</li>
                <li>开通 DashScope 服务</li>
                <li>在 "API-KEY 管理" 中创建密钥</li>
                <li>复制 API Key 粘贴到设置中</li>
            </ol>
            <p><strong>注意：</strong>北京地域无免费额度，请选择新加坡地域。</p>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>5. 百度文心一言</h3>
            <p>百度智能云千帆平台提供文心一言大模型，部分模型长期免费开放。</p>
            <ol>
                <li>访问 <a href="https://qianfan.baidubce.com" target="_blank">百度智能云千帆平台</a></li>
                <li>使用百度账号登录并完成实名认证</li>
                <li>在 IAM 管理中创建 API Key</li>
                <li>选择 "千帆ModelBuilder" 作为资源配置</li>
                <li>复制 API Key 粘贴到设置中</li>
            </ol>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>6. 月之暗面 Kimi</h3>
            <p>Kimi 擅长长文本处理，支持 128K 上下文。按量付费，无固定免费额度。</p>
            <ol>
                <li>访问 <a href="https://platform.moonshot.cn" target="_blank">platform.moonshot.cn</a></li>
                <li>注册并登录</li>
                <li>在 "API Key 管理" 创建密钥</li>
                <li>充值后即可使用（需要预充值）</li>
            </ol>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>7. 智谱AI ChatGLM</h3>
            <p>智谱AI 提供 GLM 系列模型，有一定的免费体验额度。</p>
            <ol>
                <li>访问 <a href="https://open.bigmodel.cn" target="_blank">open.bigmodel.cn</a></li>
                <li>注册账号并登录</li>
                <li>在用户中心创建 API Key</li>
                <li>复制密钥粘贴到设置中</li>
            </ol>
        </div>
        
        <div class="seopress-tutorial-section">
            <h3>百度站长推送配置</h3>
            <p>配置百度推送后，新发布的文章会自动提交到百度收录。</p>
            <ol>
                <li>访问 <a href="https://ziyuan.baidu.com" target="_blank">百度搜索资源平台</a></li>
                <li>添加并验证您的网站</li>
                <li>进入 "普通收录" -> "API 提交"</li>
                <li>复制接口调用地址中的 <code>token</code> 参数值</li>
                <li>粘贴到本主题 SEO 设置中的 "百度推送 Token"</li>
            </ol>
        </div>
        <?php
    }

    /**
     * 渲染管理系统对接页面 (Renamed to Update Check)
     */
    private function render_management_page(): void {
        // Fetch Data
        $update_info = $this->fetch_remote_update();
        $ads = $this->fetch_remote_ads();
        
        ?>
        <h2><?php esc_html_e( '更新检查', 'seopress-ai' ); ?></h2>
        
        <div class="seopress-section-container">
            <!-- Connection Status -->
            <div class="seopress-card status-card">
                <h3 style="color:#155724;margin-top:0;">✅ 系统连接状态</h3>
                <p>已成功连接到中央管理系统。</p>
                <ul>
                    <li><strong>当前站点：</strong> <?php echo esc_html(site_url()); ?></li>
                    <li><strong>插件版本：</strong> <?php echo esc_html(SEOPRESS_AI_VERSION); ?></li>
                </ul>
            </div>

            <!-- Update Section -->
            <div class="seopress-card update-card">
                <h3 style="margin-top:0;">📦 插件更新</h3>
                <?php if ($update_info && !empty($update_info['has_update'])): ?>
                    <div class="seopress-notice seopress-notice-error">
                        <p><strong>发现新版本：<?php echo esc_html($update_info['version']); ?></strong></p>
                        <?php if (!empty($update_info['upgrade_notice'])): ?>
                            <p><?php echo wp_kses_post($update_info['upgrade_notice']); ?></p>
                        <?php endif; ?>
                        <p><a href="<?php echo esc_url($update_info['package_url']); ?>" class="button button-primary" target="_blank">下载更新包</a></p>
                    </div>
                <?php else: ?>
                    <div class="seopress-notice seopress-notice-success">
                        <p>当前已是最新版本。</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ads Section -->
            <div class="seopress-card ads-card" style="grid-column: 1 / -1;">
                <h3 style="margin-top:0;">📢 推广信息</h3>
                <?php if ($ads && !empty($ads)): ?>
                    <div class="ads-grid">
                        <?php foreach ($ads as $ad): ?>
                            <div class="ad-item">
                                <?php if (!empty($ad['image_url'])): ?>
                                    <div class="ad-image">
                                        <img src="<?php echo esc_url($ad['image_url']); ?>" alt="<?php echo esc_attr($ad['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="ad-details">
                                    <h4><?php echo esc_html($ad['title']); ?></h4>
                                    <div class="ad-content"><?php echo wp_kses_post($ad['description']); ?></div>
                                    <?php if (!empty($ad['url'])): ?>
                                        <a href="<?php echo esc_url($ad['url']); ?>" target="_blank" class="button">查看详情</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>暂无推广信息。</p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            .seopress-section-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .seopress-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .status-card { border-left: 4px solid #46b450; }
            .update-card { border-left: 4px solid #2271b1; }
            .ads-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
            .ad-item { border: 1px solid #eee; border-radius: 4px; overflow: hidden; display: flex; flex-direction: column; }
            .ad-image img { width: 100%; height: auto; display: block; }
            .ad-details { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
            .ad-details h4 { margin-top: 0; margin-bottom: 10px; }
            .ad-details .ad-content { flex-grow: 1; margin-bottom: 15px; font-size: 13px; color: #666; }
            .seopress-notice { padding: 10px; border-left: 4px solid; margin: 0; }
            .seopress-notice-success { background: #ecf7ed; border-color: #52c41a; }
            .seopress-notice-error { background: #fff2f0; border-color: #d63638; }
        </style>
        <?php
    }

    // Fixed: Use JSON Body for Update Check
    private function fetch_remote_update() {
        $response = wp_remote_post('https://api.sgvps.cn/api/check-update.php', array(
            'body' => json_encode(array(
                'plugin_slug' => 'seopress-ai',
                'current_version' => SEOPRESS_AI_VERSION
            )),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 5,
            'sslverify' => false 
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            return isset($data['update']) ? $data['update'] : false;
        }
        return false;
    }

    // Fixed: Use GET for Ads and check 'ads' key
    private function fetch_remote_ads() {
        $url = 'https://api.sgvps.cn/api/ad.php';
        $url = add_query_arg(array(
            'site_url' => site_url(),
            'plugin_slug' => 'seopress-ai'
        ), $url);

        $response = wp_remote_get($url, array(
            'timeout' => 5,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            return isset($data['ads']) ? $data['ads'] : array();
        }
        return array();
    }

    /**
     * 渲染自动发布页面 (New)
     */
    private function render_auto_publish_page(): void {
        $settings = get_option('seopress_auto_publish_settings', array());
        $groups = isset($settings['publish_groups']) ? $settings['publish_groups'] : array();
        
        // Migration: If no groups but old keywords exist, create default group
        if (empty($groups) && !empty($settings['keywords'])) {
            $groups[] = array(
                'category' => isset($settings['default_category']) ? $settings['default_category'] : 0,
                'keywords' => $settings['keywords']
            );
        }
        
        ?>
        <h2><?php esc_html_e( '自动发布设置', 'seopress-ai' ); ?></h2>
        
        <form id="seopress-auto-publish-form">
            <?php wp_nonce_field( 'seopress_admin_nonce', 'seopress_auto_publish_nonce' ); ?>
            
            <!-- Schedule Settings -->
            <div class="seopress-section">
                <h3><?php esc_html_e( '定时策略', 'seopress-ai' ); ?></h3>
                
                <div class="seopress-field seopress-field-checkbox">
                    <label>
                        <input type="checkbox" name="enabled" value="1" <?php checked(!empty($settings['enabled'])); ?>>
                        <?php esc_html_e( '启用定时自动发布任务', 'seopress-ai' ); ?>
                    </label>
                </div>
                
                <div class="seopress-field">
                    <label><?php esc_html_e( '发布频率', 'seopress-ai' ); ?></label>
                    <select name="interval">
                        <option value="hourly" <?php selected($settings['interval'] ?? '', 'hourly'); ?>><?php esc_html_e('每小时', 'seopress-ai'); ?></option>
                        <option value="every_6_hours" <?php selected($settings['interval'] ?? '', 'every_6_hours'); ?>><?php esc_html_e('每6小时', 'seopress-ai'); ?></option>
                        <option value="twicedaily" <?php selected($settings['interval'] ?? '', 'twicedaily'); ?>><?php esc_html_e('每天两次', 'seopress-ai'); ?></option>
                        <option value="daily" <?php selected($settings['interval'] ?? 'daily', 'daily'); ?>><?php esc_html_e('每天一次', 'seopress-ai'); ?></option>
                    </select>
                </div>
                
                <div class="seopress-field">
                    <label><?php esc_html_e( '每次发布数量', 'seopress-ai' ); ?></label>
                    <input type="number" name="auto_count" value="<?php echo esc_attr($settings['auto_count'] ?? 1); ?>" min="1" max="10" style="width:80px;">
                    <p class="description"><?php esc_html_e('每次定时任务执行时发布的文章数量（从下方分组中轮询）', 'seopress-ai'); ?></p>
                </div>
                
                 <div class="seopress-field seopress-field-checkbox">
                    <label>
                        <input type="checkbox" name="auto_push_baidu" value="1" <?php checked(!empty($settings['auto_push_baidu'])); ?>>
                        <?php esc_html_e( '发布后自动推送到百度', 'seopress-ai' ); ?>
                    </label>
                </div>
            </div>
            
            <hr>

            <!-- Keyword Groups -->
            <div class="seopress-section">
                <h3><?php esc_html_e( '发布分组 (关键词 + 分类)', 'seopress-ai' ); ?></h3>
                <p class="description"><?php esc_html_e('自动发布任务将按顺序循环使用以下分组中的关键词。', 'seopress-ai'); ?></p>
                
                <div id="publish-groups-container">
                    <?php foreach ($groups as $index => $group) : ?>
                        <div class="seopress-group-item" data-index="<?php echo $index; ?>">
                            <div class="group-header">
                                <span class="group-title"><?php printf(esc_html__('分组 #%d', 'seopress-ai'), $index + 1); ?></span>
                                <button type="button" class="button-link remove-group-btn" style="color: #d63638;"><?php esc_html_e('移除', 'seopress-ai'); ?></button>
                            </div>
                            <div class="group-body">
                                <div class="seopress-field">
                                    <label><?php esc_html_e('发布分类:', 'seopress-ai'); ?></label>
                                    <?php
                                    wp_dropdown_categories(array(
                                        'name' => "groups[$index][category]",
                                        'selected' => $group['category'],
                                        'show_option_none' => __('选择分类', 'seopress-ai'),
                                        'hide_empty' => false,
                                        'class' => 'group-category-select'
                                    ));
                                    ?>
                                </div>
                                <div class="seopress-field">
                                    <label><?php esc_html_e('关键词列表 (每行一个):', 'seopress-ai'); ?></label>
                                    <textarea name="groups[<?php echo $index; ?>][keywords]" rows="5" placeholder="<?php esc_attr_e('输入关键词...', 'seopress-ai'); ?>"><?php echo esc_textarea($group['keywords']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" id="add-group-btn" class="button">
                    <span class="dashicons dashicons-plus-alt2" style="vertical-align: text-bottom;"></span>
                    <?php esc_html_e( '添加新分组', 'seopress-ai' ); ?>
                </button>
            </div>
            
            <div class="seopress-actions" style="margin-top:20px;">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( '保存所有设置', 'seopress-ai' ); ?>
                </button>
                
                 <?php 
                $next_run = wp_next_scheduled( 'seopress_auto_publish' );
                if ( $next_run ) :
                ?>
                    <span class="next-run-info" style="margin-left: 10px; color: #666;">
                        <?php 
                        // Fix for Timezone display: Use wp_date to respect site timezone settings
                        $local_time = wp_date( 'Y-m-d H:i:s', $next_run );
                        printf( 
                            esc_html__( '下次执行：%s', 'seopress-ai' ),
                            $local_time . ' (' . get_option('timezone_string') . ')'
                        ); ?>
                    </span>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Logic for JS Template -->
        <div id="category-dropdown-template" style="display:none;">
            <?php
            wp_dropdown_categories(array(
                'name' => 'category_template',
                'id' => 'category_template',
                'show_option_none' => __('选择分类', 'seopress-ai'),
                'hide_empty' => false,
                'class' => 'group-category-select'
            ));
            ?>
        </div>
        
        <style>
        .seopress-group-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .group-title {
            font-weight: bold;
            font-size: 14px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Save Settings
            $('#seopress-auto-publish-form').on('submit', function(e) {
                e.preventDefault();
                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('保存中...');
                
                // Collect group data
                // Form serialization handles array inputs correctly: groups[0][category], etc.
                
                $.post(ajaxurl, {
                    action: 'seopress_save_auto_publish_config',
                    nonce: $('#seopress_auto_publish_nonce').val(),
                    data: $(this).serialize()
                }, function(response) {
                    $btn.prop('disabled', false).text('保存所有设置');
                     if (response.success) {
                        alert('设置已保存');
                        location.reload(); 
                    } else {
                        alert('保存失败: ' + (response.data.message || '未知错误'));
                    }
                });
            });
            
            // Add Group
            $('#add-group-btn').on('click', function() {
                var index = $('.seopress-group-item').length;
                var template = `
                    <div class="seopress-group-item" data-index="${index}">
                        <div class="group-header">
                            <span class="group-title">分组 #${index + 1}</span>
                            <button type="button" class="button-link remove-group-btn" style="color: #d63638;">移除</button>
                        </div>
                        <div class="group-body">
                            <div class="seopress-field">
                                <label>发布分类:</label>
                                <div class="cat-select-wrapper"></div>
                            </div>
                            <div class="seopress-field">
                                <label>关键词列表 (每行一个):</label>
                                <textarea name="groups[${index}][keywords]" rows="5" placeholder="输入关键词..."></textarea>
                            </div>
                        </div>
                    </div>
                `;
                
                var $newItem = $(template);
                
                // Clone category dropdown
                var $catSelect = $('#category-dropdown-template select').clone();
                $catSelect.attr('name', `groups[${index}][category]`).removeAttr('id');
                $newItem.find('.cat-select-wrapper').append($catSelect);
                
                $('#publish-groups-container').append($newItem);
            });
            
            // Remove Group
            $(document).on('click', '.remove-group-btn', function() {
                if(confirm('确定要移除此分组吗？')) {
                    $(this).closest('.seopress-group-item').remove();
                    // Re-index logic if strictly needed, but PHP handles non-sequential arrays fine usually.
                    // Or we can rebuild indices on save. But simple 'groups[]' works too if we didn't use explicit keys.
                    // Given we adhere to groups[index], removing one leaves a gap locally but serialize handles it.
                }
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX: 保存自动发布设置 (New)
     */
    public function ajax_save_auto_publish_config(): void {
        check_ajax_referer( 'seopress_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        parse_str( $_POST['data'], $data );
        
        $settings = get_option( 'seopress_auto_publish_settings', array() );
        
        // Basic Settings
        $settings['enabled'] = !empty($data['enabled']);
        $settings['interval'] = sanitize_text_field($data['interval']);
        $settings['auto_count'] = intval($data['auto_count']);
        $settings['auto_push_baidu'] = !empty($data['auto_push_baidu']);
        
        // Groups
        $groups = array();
        if (isset($data['groups']) && is_array($data['groups'])) {
            foreach ($data['groups'] as $g) {
                if (!empty($g['keywords'])) {
                     $groups[] = array(
                        'category' => intval($g['category']),
                        'keywords' => sanitize_textarea_field($g['keywords'])
                    );
                }
            }
        }
        $settings['publish_groups'] = $groups;
        
        update_option( 'seopress_auto_publish_settings', $settings );
        
        // Reschedule Cron
        if (class_exists('SeoPress_Auto_Publish')) {
             $auto_publish = SeoPress_Auto_Publish::get_instance();
             $auto_publish->reschedule_cron();
        }

        wp_send_json_success();
    }
}
