<?php
/**
 * Groq AI 适配器
 * 
 * 全球推理速度最快的AI平台，提供免费API访问
 * 支持 Llama 3.1, Mixtral, Qwen 等开源模型
 * 
 * 官网: https://console.groq.com/
 * API文档: https://console.groq.com/docs/api-reference
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Groq implements SeoPress_AI_Provider_Interface {
    
    /**
     * API 端点 - 兼容OpenAI格式
     */
    private const API_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';
    
    /**
     * 默认模型 - 使用Llama 3.1 8B，速度快且免费
     */
    private const DEFAULT_MODEL = 'llama-3.1-8b-instant';
    
    /**
     * 可用模型列表
     */
    private const AVAILABLE_MODELS = array(
        'llama-3.1-8b-instant'   => 'Llama 3.1 8B（推荐，超快）',
        'llama-3.1-70b-versatile' => 'Llama 3.1 70B（更强大）',
        'llama-3.3-70b-versatile' => 'Llama 3.3 70B（最新）',
        'mixtral-8x7b-32768'     => 'Mixtral 8x7B',
        'gemma2-9b-it'           => 'Gemma 2 9B',
        'qwen-qwq-32b'           => 'Qwen QwQ 32B',
    );
    
    /**
     * 获取提供商名称
     */
    public function get_name(): string {
        return 'Groq（极速免费）';
    }
    
    /**
     * 获取提供商ID
     */
    public function get_id(): string {
        return 'groq';
    }
    
    /**
     * 获取API密钥
     */
    private function get_api_key(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['groq_api_key'] ) ? $options['groq_api_key'] : '';
    }
    
    /**
     * 获取选择的模型
     */
    private function get_model(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['groq_model'] ) && !empty( $options['groq_model'] ) 
            ? $options['groq_model'] 
            : self::DEFAULT_MODEL;
    }
    
    /**
     * 生成内容
     * 
     * @param string $prompt 用户提示词
     * @param array  $options 可选参数
     * @return array
     */
    public function generate_content( string $prompt, array $options = array() ): array {
        $api_key = $this->get_api_key();
        
        if ( empty( $api_key ) ) {
            return array(
                'success' => false,
                'content' => '',
                'usage'   => array(),
                'error'   => '未配置 Groq API 密钥，请前往 console.groq.com 注册获取',
            );
        }
        
        // 默认参数
        $defaults = array(
            'temperature' => 0.7,
            'max_tokens'  => 2000,
            'model'       => $this->get_model(),
        );
        $options = wp_parse_args( $options, $defaults );
        
        // 构建请求体 - OpenAI兼容格式
        $body = array(
            'model'       => $options['model'],
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => '你是一个专业的SEO内容写作专家，擅长撰写高质量、对搜索引擎友好的中文文章。请用Markdown格式输出，包含合理的标题结构。',
                ),
                array(
                    'role'    => 'user',
                    'content' => $prompt,
                ),
            ),
            'temperature' => floatval( $options['temperature'] ),
            'max_tokens'  => intval( $options['max_tokens'] ),
        );
        
        // 发送请求
        $response = wp_remote_post( self::API_ENDPOINT, array(
            'timeout' => 120,
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'body'    => wp_json_encode( $body ),
        ) );
        
        // 检查请求错误
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
        
        // 检查HTTP状态码
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
        
        // 解析响应
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
        $result = $this->generate_content( 'Hello, please reply with "Connection successful" in Chinese.', array(
            'max_tokens' => 50,
        ) );
        return $result['success'];
    }
    
    /**
     * 获取设置字段
     */
    public function get_settings_fields(): array {
        $model_options = array();
        foreach ( self::AVAILABLE_MODELS as $id => $name ) {
            $model_options[] = array(
                'value' => $id,
                'label' => $name,
            );
        }
        
        return array(
            array(
                'id'          => 'groq_api_key',
                'label'       => 'Groq API 密钥',
                'type'        => 'password',
                'description' => '从 <a href="https://console.groq.com/keys" target="_blank">console.groq.com</a> 获取API密钥。<strong>免费使用，推理速度全球最快！</strong>',
            ),
            array(
                'id'          => 'groq_model',
                'label'       => '选择模型',
                'type'        => 'select',
                'options'     => $model_options,
                'default'     => self::DEFAULT_MODEL,
                'description' => 'Llama 3.1 8B 速度最快，70B 效果更好',
            ),
        );
    }
    
    /**
     * 获取可用模型列表
     */
    public static function get_available_models(): array {
        return self::AVAILABLE_MODELS;
    }
}
