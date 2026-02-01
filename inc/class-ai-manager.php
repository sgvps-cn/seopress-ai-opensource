<?php
/**
 * AI 管理器
 * 
 * 管理所有 AI 提供商，提供统一的调用接口
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Manager {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 已注册的提供商
     */
    private $providers = array();
    
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
        $this->load_providers();
        $this->register_providers();
    }
    
    /**
     * 加载提供商类文件
     */
    private function load_providers(): void {
        $providers_dir = SEOPRESS_AI_PATH . 'inc/ai-providers/';
        
        // 加载接口
        require_once $providers_dir . 'interface-ai-provider.php';
        
        // 加载各个提供商
        $provider_files = array(
            'class-deepseek.php',
            'class-qwen.php',
            'class-ernie.php',
            'class-kimi.php',
            'class-zhipu.php',
            'class-siliconflow.php',
            'class-groq.php',
        );
        
        foreach ( $provider_files as $file ) {
            $file_path = $providers_dir . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * 注册所有提供商
     */
    private function register_providers(): void {
        $provider_classes = array(
            'SeoPress_AI_SiliconFlow',
            'SeoPress_AI_Groq',
            'SeoPress_AI_DeepSeek',
            'SeoPress_AI_Qwen',
            'SeoPress_AI_Ernie',
            'SeoPress_AI_Kimi',
            'SeoPress_AI_Zhipu',
        );
        
        foreach ( $provider_classes as $class_name ) {
            if ( class_exists( $class_name ) ) {
                $provider = new $class_name();
                if ( $provider instanceof SeoPress_AI_Provider_Interface ) {
                    $this->providers[ $provider->get_id() ] = $provider;
                }
            }
        }
    }
    
    /**
     * 获取所有提供商
     * 
     * @return array
     */
    public function get_providers(): array {
        return $this->providers;
    }
    
    /**
     * 获取提供商选项（用于下拉菜单）
     * 
     * @return array
     */
    public function get_provider_options(): array {
        $options = array();
        foreach ( $this->providers as $id => $provider ) {
            $options[ $id ] = $provider->get_name();
        }
        return $options;
    }
    
    /**
     * 获取当前选择的提供商ID
     * 
     * @return string
     */
    public function get_current_provider_id(): string {
        $options = get_option( 'seopress_ai_options', array() );
        $provider_id = isset( $options['ai_provider'] ) ? $options['ai_provider'] : 'siliconflow';
        
        // 验证提供商是否存在
        if ( ! isset( $this->providers[ $provider_id ] ) ) {
            $provider_id = 'siliconflow';
        }
        
        return $provider_id;
    }
    
    /**
     * 获取当前提供商实例
     * 
     * @return SeoPress_AI_Provider_Interface|null
     */
    public function get_current_provider(): ?SeoPress_AI_Provider_Interface {
        $provider_id = $this->get_current_provider_id();
        return isset( $this->providers[ $provider_id ] ) ? $this->providers[ $provider_id ] : null;
    }
    
    /**
     * 获取指定提供商
     * 
     * @param string $provider_id 提供商ID
     * @return SeoPress_AI_Provider_Interface|null
     */
    public function get_provider( string $provider_id ): ?SeoPress_AI_Provider_Interface {
        return isset( $this->providers[ $provider_id ] ) ? $this->providers[ $provider_id ] : null;
    }
    
    /**
     * 生成内容（使用当前选择的提供商）
     * 
     * @param string $prompt 提示词
     * @param array  $options 选项
     * @return array{success: bool, content: string, usage: array, error?: string}
     */
    public function generate_content( string $prompt, array $options = array() ): array {
        $provider = $this->get_current_provider();
        
        if ( null === $provider ) {
            return array(
                'success' => false,
                'content' => '',
                'usage'   => array(),
                'error'   => '未找到可用的 AI 提供商',
            );
        }
        
        // 从设置获取默认参数
        $settings = get_option( 'seopress_ai_options', array() );
        $defaults = array(
            'temperature' => isset( $settings['ai_temperature'] ) ? floatval( $settings['ai_temperature'] ) : 0.7,
            'max_tokens'  => isset( $settings['ai_max_tokens'] ) ? intval( $settings['ai_max_tokens'] ) : 2000,
        );
        $options = wp_parse_args( $options, $defaults );
        
        return $provider->generate_content( $prompt, $options );
    }
    
    /**
     * 测试指定提供商的连接
     * 
     * @param string $provider_id 提供商ID
     * @return array
     */
    public function test_provider_connection( string $provider_id ): array {
        $provider = $this->get_provider( $provider_id );
        
        if ( null === $provider ) {
            return array(
                'success' => false,
                'message' => '未找到指定的 AI 提供商',
            );
        }
        
        $result = $provider->test_connection();
        
        return array(
            'success' => $result,
            'message' => $result ? '连接成功' : '连接失败，请检查 API 密钥是否正确',
        );
    }
    
    /**
     * 获取所有提供商的设置字段
     * 
     * @return array
     */
    public function get_all_settings_fields(): array {
        $fields = array();
        foreach ( $this->providers as $provider ) {
            $fields = array_merge( $fields, $provider->get_settings_fields() );
        }
        return $fields;
    }
}
