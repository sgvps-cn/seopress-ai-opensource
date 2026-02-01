<?php
/**
 * AI 提供商接口
 * 
 * 所有 AI 服务提供商适配器必须实现此接口
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface SeoPress_AI_Provider_Interface {
    
    /**
     * 获取提供商名称
     * 
     * @return string 提供商显示名称
     */
    public function get_name(): string;
    
    /**
     * 获取提供商ID
     * 
     * @return string 提供商唯一标识符
     */
    public function get_id(): string;
    
    /**
     * 生成内容
     * 
     * @param string $prompt 用户提示词
     * @param array  $options 可选参数 (temperature, max_tokens等)
     * @return array 返回格式: ['success' => bool, 'content' => string, 'usage' => array, 'error' => string]
     */
    public function generate_content( string $prompt, array $options = array() ): array;
    
    /**
     * 测试API连接
     * 
     * @return bool 连接是否成功
     */
    public function test_connection(): bool;
    
    /**
     * 获取设置字段
     * 
     * @return array 设置字段配置数组
     */
    public function get_settings_fields(): array;
}
