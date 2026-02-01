<?php
/**
 * 智谱AI ChatGLM 适配器
 * 
 * 智谱 AI 大模型平台 GLM 模型适配器
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Zhipu implements SeoPress_AI_Provider_Interface {
    
    /**
     * API 端点
     */
    private const API_ENDPOINT = 'https://open.bigmodel.cn/api/paas/v4/chat/completions';
    
    /**
     * 默认模型
     */
    private const DEFAULT_MODEL = 'glm-4';
    
    /**
     * 获取提供商名称
     */
    public function get_name(): string {
        return '智谱AI ChatGLM';
    }
    
    /**
     * 获取提供商ID
     */
    public function get_id(): string {
        return 'zhipu';
    }
    
    /**
     * 获取API密钥
     */
    private function get_api_key(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['zhipu_api_key'] ) ? $options['zhipu_api_key'] : '';
    }
    
    /**
     * 生成内容
     */
    public function generate_content( string $prompt, array $options = array() ): array {
        $api_key = $this->get_api_key();
        
        if ( empty( $api_key ) ) {
            return array(
                'success' => false,
                'content' => '',
                'usage'   => array(),
                'error'   => '未配置智谱AI API 密钥',
            );
        }
        
        $defaults = array(
            'temperature' => 0.7,
            'max_tokens'  => 2000,
            'model'       => self::DEFAULT_MODEL,
        );
        $options = wp_parse_args( $options, $defaults );
        
        $body = array(
            'model'       => $options['model'],
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => '你是一个专业的SEO内容写作专家，擅长撰写高质量、对搜索引擎友好的中文文章。',
                ),
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
            'temperature' => floatval( $options['temperature'] ),
            'max_tokens'  => intval( $options['max_tokens'] ),
        );
        
        $response = wp_remote_post( self::API_ENDPOINT, array(
            'timeout' => 120,
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'body'    => wp_json_encode( $body ),
        ) );
        
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'content' => '',
                'usage'   => array(),
                'error'   => $response->get_error_message(),
            );
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $body_data   = json_decode( $body_raw, true );
        
        if ( $status_code !== 200 ) {
            $error_msg = isset( $body_data['error']['message'] ) 
                ? $body_data['error']['message'] 
                : '请求失败，状态码: ' . $status_code;
            return array(
                'success' => false,
                'content' => '',
                'usage'   => array(),
                'error'   => $error_msg,
            );
        }
        
        if ( isset( $body_data['choices'][0]['message']['content'] ) ) {
            return array(
                'success' => true,
                'content' => $body_data['choices'][0]['message']['content'],
                'usage'   => isset( $body_data['usage'] ) ? $body_data['usage'] : array(),
                'error'   => '',
            );
        }
        
        return array(
            'success' => false,
            'content' => '',
            'usage'   => array(),
            'error'   => '无法解析API响应',
        );
    }
    
    /**
     * 测试API连接
     */
    public function test_connection(): bool {
        $result = $this->generate_content( '你好，请回复"连接成功"', array(
            'max_tokens' => 50,
        ) );
        return $result['success'];
    }
    
    /**
     * 获取设置字段
     */
    public function get_settings_fields(): array {
        return array(
            array(
                'id'          => 'zhipu_api_key',
                'label'       => '智谱AI API 密钥',
                'type'        => 'password',
                'description' => '从 open.bigmodel.cn 获取 API 密钥',
            ),
        );
    }
}
