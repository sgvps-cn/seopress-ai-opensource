<?php
/**
 * 设置管理类
 * 
 * 使用 WordPress Settings API 管理主题设置
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Settings {
    
    /**
     * 选项名称
     */
    const OPTION_NAME = 'seopress_ai_options';
    
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
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    
    /**
     * 注册设置
     */
    public function register_settings(): void {
        register_setting(
            'seopress_ai_settings',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_options' ),
                'default'           => $this->get_defaults(),
            )
        );
    }
    
    /**
     * 获取默认值
     */
    public function get_defaults(): array {
        return array(
            // 常规设置
            'footer_text'         => '',
            'analytics_code'      => '',
            'baidu_verify_code'   => '',
            
            // AI 设置
            'ai_provider'           => 'siliconflow',
            'deepseek_api_key'      => '',
            'qwen_api_key'          => '',
            'ernie_api_key'         => '',
            'kimi_api_key'          => '',
            'zhipu_api_key'         => '',
            'siliconflow_api_key'   => '',
            'siliconflow_model'     => 'Qwen/Qwen2.5-7B-Instruct',
            'groq_api_key'          => '',
            'groq_model'            => 'llama-3.1-8b-instant',
            'ai_temperature'        => 0.7,
            'ai_max_tokens'         => 2000,
            
            // SEO 设置
            'baidu_push_token'    => '',
            'baidu_push_enabled'  => true,
            'auto_meta_enabled'   => true,
            'jsonld_enabled'      => true,
            
            // 自动发布设置
            'auto_publish_enabled'  => false,
            'publish_interval'      => 'daily',
            'publish_category'      => 0,
            'publish_status'        => 'draft',
            'publish_keywords'      => '',
            
            // 社交网络设置
            'qq_link'               => '',
            'github_link'           => 'https://github.com/sgvps-cn/seopress-ai-opensource',
            'email_address'         => 'admin@example.com',
        );
    }
    
    /**
     * 获取选项值
     * 
     * @param string $key 选项键
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get( string $key, $default = null ) {
        $options = get_option( self::OPTION_NAME, $this->get_defaults() );
        
        if ( $default === null ) {
            $defaults = $this->get_defaults();
            $default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
        }
        
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
    
    /**
     * 更新选项值
     * 
     * @param string $key 选项键
     * @param mixed  $value 选项值
     * @return bool
     */
    public function set( string $key, $value ): bool {
        $options = get_option( self::OPTION_NAME, $this->get_defaults() );
        $options[ $key ] = $value;
        return update_option( self::OPTION_NAME, $options );
    }
    
    /**
     * 清理选项值
     * 
     * @param array $input 输入值
     * @return array
     */
    public function sanitize_options( array $input ): array {
        $sanitized = array();
        $defaults = $this->get_defaults();
        
        foreach ( $defaults as $key => $default_value ) {
            if ( ! isset( $input[ $key ] ) ) {
                $sanitized[ $key ] = $default_value;
                continue;
            }
            
            $value = $input[ $key ];
            
            // 根据键名进行清理
            if ( strpos( $key, '_api_key' ) !== false ) {
                // API 密钥
                $sanitized[ $key ] = sanitize_text_field( $value );
            } elseif ( strpos( $key, '_enabled' ) !== false ) {
                // 布尔值
                $sanitized[ $key ] = (bool) $value;
            } elseif ( $key === 'ai_temperature' ) {
                // 温度值 (0.1 - 1.0)
                $sanitized[ $key ] = max( 0.1, min( 1.0, floatval( $value ) ) );
            } elseif ( $key === 'ai_max_tokens' ) {
                // 最大 tokens
                $sanitized[ $key ] = max( 100, min( 4000, intval( $value ) ) );
            } elseif ( $key === 'analytics_code' ) {
                // 统计代码（允许 script 标签）
                $sanitized[ $key ] = $value; // 保留原始值
            } elseif ( $key === 'footer_text' ) {
                // 页脚文本
                $sanitized[ $key ] = wp_kses_post( $value );
            } elseif ( $key === 'publish_interval' ) {
                // 发布间隔
                $allowed = array( 'hourly', 'twicedaily', 'daily' );
                $sanitized[ $key ] = in_array( $value, $allowed ) ? $value : 'daily';
            } elseif ( $key === 'publish_status' ) {
                // 发布状态
                $allowed = array( 'draft', 'publish' );
                $sanitized[ $key ] = in_array( $value, $allowed ) ? $value : 'draft';
            } elseif ( $key === 'publish_keywords' ) {
                // 发布关键词
                $sanitized[ $key ] = sanitize_textarea_field( $value );
            } elseif ( $key === 'publish_categories' ) {
                // 发布分类 (多选)
                $sanitized[ $key ] = is_array( $value ) ? array_map( 'intval', $value ) : array();
            } else {
                // 默认清理
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }
        
        // 特殊处理 QQ 链接 (允许 URL 或数字)
        if ( isset( $input['qq_link'] ) ) {
            $sanitized['qq_link'] = sanitize_text_field( $input['qq_link'] );
        }
        
        return $sanitized;
    }
    
    /**
     * 获取下次执行时间显示
     */
    private function get_next_schedule_display(): string {
        if ( class_exists( 'SeoPress_Auto_Publish' ) ) {
            $next = SeoPress_Auto_Publish::get_instance()->get_next_run();
            if ( $next ) {
                return '<br><span style="color:#2271b1;">' . sprintf( __( '下次执行时间：%s', 'seopress-ai' ), $next ) . '</span>';
            }
        }
        return '';
    }

    /**
     * 获取设置字段配置
     */
    public function get_settings_fields(): array {
        return array(
            'general' => array(
                'title'  => __( '常规设置', 'seopress-ai' ),
                'fields' => array(
                    array(
                        'id'          => 'footer_text',
                        'label'       => __( '页脚版权文本', 'seopress-ai' ),
                        'type'        => 'textarea',
                        'description' => __( '显示在页脚的版权信息，支持 HTML', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'analytics_code',
                        'label'       => __( '统计代码', 'seopress-ai' ),
                        'type'        => 'textarea',
                        'description' => __( '百度统计或其他统计代码，将添加到页脚', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'baidu_verify_code',
                        'label'       => __( '百度站点验证码', 'seopress-ai' ),
                        'type'        => 'text',
                        'description' => __( '百度站长平台的站点验证码', 'seopress-ai' ),
                    ),
                ),
            ),
            'social' => array(
                'title'  => __( '社交网络', 'seopress-ai' ),
                'fields' => array(
                    array(
                        'id'          => 'qq_link',
                        'label'       => __( 'QQ 链接/号码', 'seopress-ai' ),
                        'type'        => 'text',
                        'description' => __( '输入QQ号或完整的QQ推广链接', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'github_link',
                        'label'       => __( 'GitHub 主页', 'seopress-ai' ),
                        'type'        => 'text',
                        'description' => __( 'GitHub 个人或组织主页链接', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'email_address',
                        'label'       => __( '联系邮箱', 'seopress-ai' ),
                        'type'        => 'text',
                        'description' => __( '页脚显示的联系邮箱地址', 'seopress-ai' ),
                    ),
                ),
            ),
            'ai' => array(
                'title'  => __( 'AI 设置', 'seopress-ai' ),
                'fields' => array(
                    array(
                        'id'          => 'ai_provider',
                        'label'       => __( 'AI 服务提供商', 'seopress-ai' ),
                        'type'        => 'select',
                        'options'     => array(
                            'siliconflow' => '硅基流动（免费推荐）',
                            'groq'        => 'Groq（极速免费）',
                            'deepseek'    => 'DeepSeek',
                            'qwen'        => '通义千问',
                            'ernie'       => '百度文心一言',
                            'kimi'        => '月之暗面 Kimi',
                            'zhipu'       => '智谱AI ChatGLM',
                        ),
                        'description' => __( '选择用于生成文章内容的 AI 服务，硅基流动和Groq提供免费额度', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'siliconflow_api_key',
                        'label'       => __( '硅基流动 API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从 cloud.siliconflow.cn 获取，9B以下模型永久免费', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'siliconflow_model',
                        'label'       => __( '硅基流动模型', 'seopress-ai' ),
                        'type'        => 'select',
                        'options'     => array(
                            'Qwen/Qwen2.5-7B-Instruct'      => 'Qwen2.5-7B（推荐，免费）',
                            'deepseek-ai/DeepSeek-V2.5'     => 'DeepSeek-V2.5（免费）',
                            'internlm/internlm2_5-7b-chat'  => 'InternLM2.5-7B（免费）',
                            'THUDM/glm-4-9b-chat'           => 'GLM-4-9B（免费）',
                            'meta-llama/Meta-Llama-3.1-8B-Instruct' => 'Llama-3.1-8B（免费）',
                        ),
                        'description' => __( '以上模型均为免费模型', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'groq_api_key',
                        'label'       => __( 'Groq API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从 console.groq.com 获取，免费使用，推理速度全球最快', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'groq_model',
                        'label'       => __( 'Groq 模型', 'seopress-ai' ),
                        'type'        => 'select',
                        'options'     => array(
                            'llama-3.1-8b-instant'    => 'Llama 3.1 8B（推荐，超快）',
                            'llama-3.1-70b-versatile' => 'Llama 3.1 70B（更强大）',
                            'llama-3.3-70b-versatile' => 'Llama 3.3 70B（最新）',
                            'mixtral-8x7b-32768'      => 'Mixtral 8x7B',
                            'gemma2-9b-it'            => 'Gemma 2 9B',
                        ),
                        'description' => __( 'Llama 3.1 8B 速度最快，70B 效果更好', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'deepseek_api_key',
                        'label'       => __( 'DeepSeek API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从 platform.deepseek.com 获取，新用户有500万tokens免费额度', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'qwen_api_key',
                        'label'       => __( '通义千问 API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从阿里云百炼平台获取 DashScope API 密钥', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'ernie_api_key',
                        'label'       => __( '百度文心 API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从百度智能云千帆平台获取', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'kimi_api_key',
                        'label'       => __( 'Kimi API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从 platform.moonshot.cn 获取', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'zhipu_api_key',
                        'label'       => __( '智谱AI API 密钥', 'seopress-ai' ),
                        'type'        => 'password',
                        'description' => __( '从 open.bigmodel.cn 获取', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'ai_temperature',
                        'label'       => __( '生成温度', 'seopress-ai' ),
                        'type'        => 'number',
                        'min'         => 0.1,
                        'max'         => 1.0,
                        'step'        => 0.1,
                        'description' => __( '控制生成内容的随机性，值越高越有创意（0.1-1.0）', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'ai_max_tokens',
                        'label'       => __( '最大生成长度', 'seopress-ai' ),
                        'type'        => 'number',
                        'min'         => 100,
                        'max'         => 4000,
                        'step'        => 100,
                        'description' => __( '生成内容的最大 token 数量（100-4000）', 'seopress-ai' ),
                    ),
                ),
            ),
            'seo' => array(
                'title'  => __( 'SEO 设置', 'seopress-ai' ),
                'fields' => array(
                    array(
                        'id'          => 'baidu_push_token',
                        'label'       => __( '百度推送 Token', 'seopress-ai' ),
                        'type'        => 'text',
                        'description' => __( '从百度站长平台获取的推送 Token', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'baidu_push_enabled',
                        'label'       => __( '启用百度推送', 'seopress-ai' ),
                        'type'        => 'checkbox',
                        'description' => __( '发布文章时自动推送到百度', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'auto_meta_enabled',
                        'label'       => __( '自动生成 Meta 标签', 'seopress-ai' ),
                        'type'        => 'checkbox',
                        'description' => __( '自动生成 title、description、keywords 等 Meta 标签', 'seopress-ai' ),
                    ),
                    array(
                        'id'          => 'jsonld_enabled',
                        'label'       => __( 'JSON-LD 结构化数据', 'seopress-ai' ),
                        'type'        => 'checkbox',
                        'description' => __( '输出 JSON-LD 格式的结构化数据', 'seopress-ai' ),
                    ),
                ),
            ),

        );
    }

    /**
     * 渲染设置字段
     */
    public function render_field( array $args ): void {
        $options = get_option( self::OPTION_NAME );
        $value = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $this->get_defaults()[ $args['id'] ];

        switch ( $args['type'] ) {
            case 'text':
            case 'password':
            case 'number':
                printf(
                    '<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" class="regular-text" %5$s>',
                    esc_attr( $args['type'] ),
                    esc_attr( $args['id'] ),
                    esc_attr( self::OPTION_NAME ),
                    esc_attr( $value ),
                    ( isset( $args['min'] ) ? 'min="' . esc_attr( $args['min'] ) . '"' : '' ) .
                    ( isset( $args['max'] ) ? 'max="' . esc_attr( $args['max'] ) . '"' : '' ) .
                    ( isset( $args['step'] ) ? 'step="' . esc_attr( $args['step'] ) . '"' : '' )
                );
                break;
            case 'textarea':
                printf(
                    '<textarea id="%1$s" name="%2$s[%1$s]" rows="5" cols="50" class="large-text">%3$s</textarea>',
                    esc_attr( $args['id'] ),
                    esc_attr( self::OPTION_NAME ),
                    esc_textarea( $value )
                );
                break;
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s>',
                    esc_attr( $args['id'] ),
                    esc_attr( self::OPTION_NAME ),
                    checked( 1, $value, false )
                );
                break;
            case 'select':
                printf( '<select id="%1$s" name="%2$s[%1$s]">', esc_attr( $args['id'] ), esc_attr( self::OPTION_NAME ) );
                foreach ( $args['options'] as $key => $label ) {
                    printf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        esc_attr( $key ),
                        selected( $value, $key, false ),
                        esc_html( $label )
                    );
                }
                echo '</select>';
                break;
            case 'category': // For single category selection
                wp_dropdown_categories( array(
                    'show_option_none' => __( '选择分类', 'seopress-ai' ),
                    'orderby'          => 'name',
                    'hide_empty'       => 0,
                    'name'             => esc_attr( self::OPTION_NAME ) . '[' . esc_attr( $args['id'] ) . ']',
                    'id'               => esc_attr( $args['id'] ),
                    'selected'         => $value,
                    'hierarchical'     => true,
                    'taxonomy'         => 'category',
                    'class'            => 'regular-text',
                ) );
                break;
            case 'category_checklist': // For multiple category selection
                $selected_categories = is_array( $value ) ? $value : array();
                $categories = get_categories( array( 'hide_empty' => 0 ) );
                echo '<div style="height: 150px; overflow-y: scroll; border: 1px solid #ccc; padding: 5px; background-color: #fff;">';
                foreach ( $categories as $category ) {
                    printf(
                        '<label><input type="checkbox" name="%1$s[%2$s][]" value="%3$s" %4$s> %5$s</label><br>',
                        esc_attr( self::OPTION_NAME ),
                        esc_attr( $args['id'] ),
                        esc_attr( $category->term_id ),
                        checked( in_array( $category->term_id, $selected_categories ), true, false ),
                        esc_html( $category->name )
                    );
                }
                echo '</div>';
                break;
        }

        if ( isset( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', wp_kses_post( $args['description'] ) );
        }
    }
}

