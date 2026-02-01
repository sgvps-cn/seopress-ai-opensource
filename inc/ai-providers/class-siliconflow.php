<?php
/**
 * 硅基流动 SiliconFlow AI 适配器
 * 
 * 国内最良心的AI模型聚合平台
 * 9B以下模型永久免费（包括DeepSeek-V3、Qwen2.5-7B等）
 * 
 * 官网: https://siliconflow.cn/
 * API文档: https://docs.siliconflow.cn/
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_SiliconFlow implements SeoPress_AI_Provider_Interface {
    
    /**
     * API 端点 - 兼容OpenAI格式
     */
    private const API_ENDPOINT = 'https://api.siliconflow.cn/v1/chat/completions';
    
    /**
     * 默认模型 - 使用免费的Qwen2.5-7B
     * 可选免费模型：
     * - Qwen/Qwen2.5-7B-Instruct (免费)
     * - deepseek-ai/DeepSeek-V2.5 (免费)
     * - internlm/internlm2_5-7b-chat (免费)
     * - THUDM/glm-4-9b-chat (免费)
     */
    private const DEFAULT_MODEL = 'Qwen/Qwen2.5-7B-Instruct';
    
    /**
     * 可用的免费模型列表
     */
    private const FREE_MODELS = array(
        'Qwen/Qwen2.5-7B-Instruct'      => 'Qwen2.5-7B（推荐，免费）',
        'deepseek-ai/DeepSeek-V2.5'      => 'DeepSeek-V2.5（免费）',
        'internlm/internlm2_5-7b-chat'   => 'InternLM2.5-7B（免费）',
        'THUDM/glm-4-9b-chat'            => 'GLM-4-9B（免费）',
        'meta-llama/Meta-Llama-3.1-8B-Instruct' => 'Llama-3.1-8B（免费）',
    );
    
    /**
     * 获取提供商名称
     */
    public function get_name(): string {
        return '硅基流动（免费推荐）';
    }
    
    /**
     * 获取提供商ID
     */
    public function get_id(): string {
        return 'siliconflow';
    }
    
    /**
     * 获取API密钥
     */
    private function get_api_key(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['siliconflow_api_key'] ) ? $options['siliconflow_api_key'] : '';
    }
    
    /**
     * 获取选择的模型
     */
    private function get_model(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['siliconflow_model'] ) && !empty( $options['siliconflow_model'] ) 
            ? $options['siliconflow_model'] 
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
                'error'   => '未配置硅基流动 API 密钥，请前往 siliconflow.cn 注册获取',
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
            'stream'      => false,
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
                : ( isset( $body_data['message'] ) ? $body_data['message'] : '请求失败，状态码: ' . $status_code );
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
        $result = $this->generate_content( '你好，请回复"连接成功"四个字', array(
            'max_tokens' => 50,
        ) );
        return $result['success'];
    }
    
    /**
     * 获取设置字段
     */
    public function get_settings_fields(): array {
        $model_options = array();
        foreach ( self::FREE_MODELS as $id => $name ) {
            $model_options[] = array(
                'value' => $id,
                'label' => $name,
            );
        }
        
        return array(
            array(
                'id'          => 'siliconflow_api_key',
                'label'       => '硅基流动 API 密钥',
                'type'        => 'password',
                'description' => '从 <a href="https://cloud.siliconflow.cn/" target="_blank">cloud.siliconflow.cn</a> 获取API密钥。<strong>9B以下模型永久免费！</strong>',
            ),
            array(
                'id'          => 'siliconflow_model',
                'label'       => '选择模型',
                'type'        => 'select',
                'options'     => $model_options,
                'default'     => self::DEFAULT_MODEL,
                'description' => '以上模型均为免费模型，无需担心费用',
            ),
        );
    }
    
    /**
     * 获取免费模型列表
     */
    public static function get_free_models(): array {
        return self::FREE_MODELS;
    }
}
