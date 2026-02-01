<?php
/**
 * 百度推送模块
 * 
 * 实现百度站长平台 API 主动推送功能
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeoPress_AI_Baidu_Push {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 百度推送 API 基础 URL
     */
    private const API_BASE = 'http://data.zz.baidu.com/urls';
    
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
        $this->init_hooks();
    }
    
    /**
     * 初始化钩子
     */
    private function init_hooks(): void {
        // 文章发布时推送
        add_action( 'publish_post', array( $this, 'push_on_publish' ), 10, 2 );
        
        // 页面发布时推送
        add_action( 'publish_page', array( $this, 'push_on_publish' ), 10, 2 );
        
        // 在页脚添加百度自动推送 JS
        add_action( 'wp_footer', array( $this, 'output_auto_push_js' ), 99 );
        
        // AJAX 处理
        add_action( 'wp_ajax_seopress_test_baidu_push', array( $this, 'ajax_test_push' ) );
        add_action( 'wp_ajax_seopress_manual_push', array( $this, 'ajax_manual_push' ) );
    }
    
    /**
     * 检查推送是否启用
     */
    private function is_push_enabled(): bool {
        $options = get_option( 'seopress_ai_options', array() );
        return ! empty( $options['baidu_push_enabled'] ) && ! empty( $options['baidu_push_token'] );
    }
    
    /**
     * 获取推送 Token
     */
    private function get_push_token(): string {
        $options = get_option( 'seopress_ai_options', array() );
        return isset( $options['baidu_push_token'] ) ? $options['baidu_push_token'] : '';
    }
    
    /**
     * 获取站点域名
     */
    private function get_site_domain(): string {
        $url = home_url();
        $parsed = wp_parse_url( $url );
        return isset( $parsed['host'] ) ? $parsed['host'] : '';
    }
    
    /**
     * 文章发布时推送
     * 
     * @param int     $post_id 文章ID
     * @param WP_Post $post    文章对象
     */
    public function push_on_publish( int $post_id, WP_Post $post ): void {
        // 检查是否启用推送
        if ( ! $this->is_push_enabled() ) {
            return;
        }
        
        // 检查是否是修订版本
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        // 检查是否已经推送过
        $pushed = get_post_meta( $post_id, '_seopress_baidu_pushed', true );
        if ( $pushed ) {
            return;
        }
        
        // 获取文章链接
        $url = get_permalink( $post_id );
        
        // 推送
        $result = $this->push_urls( array( $url ) );
        
        // 记录推送结果
        if ( $result['success'] ) {
            update_post_meta( $post_id, '_seopress_baidu_pushed', time() );
            $this->log_push( 'success', $url, $result );
        } else {
            $this->log_push( 'error', $url, $result );
        }
    }
    
    /**
     * 推送单个 URL (兼容性包装器)
     * 
     * @param string $url 推送的URL
     * @return bool 成功返回true
     */
    public function push_url( string $url ): bool {
        $result = $this->push_urls( array( $url ) );
        return $result['success'];
    }

    /**
     * 推送 URL 列表
     * 
     * @param array $urls URL 列表（最多20条）
     * @return array
     */
    public function push_urls( array $urls ): array {
        $token = $this->get_push_token();
        $site = $this->get_site_domain();
        
        if ( empty( $token ) || empty( $site ) ) {
            return array(
                'success' => false,
                'message' => '未配置百度推送 Token 或站点域名',
            );
        }
        
        // 限制最多20条
        $urls = array_slice( $urls, 0, 20 );
        
        // 构建 API URL
        $api_url = add_query_arg( array(
            'site'  => $site,
            'token' => $token,
        ), self::API_BASE );
        
        // 发送请求
        $response = wp_remote_post( $api_url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'text/plain',
            ),
            'body'    => implode( "\n", $urls ),
        ) );
        
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( $status_code !== 200 ) {
            $error_msg = isset( $data['message'] ) ? $data['message'] : '请求失败，状态码: ' . $status_code;
            return array(
                'success' => false,
                'message' => $error_msg,
                'data'    => $data,
            );
        }
        
        // 检查返回结果
        if ( isset( $data['success'] ) && $data['success'] > 0 ) {
            return array(
                'success' => true,
                'message' => sprintf( '成功推送 %d 条链接，剩余配额 %d', $data['success'], $data['remain'] ?? 0 ),
                'data'    => $data,
            );
        }
        
        // 处理错误
        $error_msg = isset( $data['message'] ) ? $data['message'] : '推送失败';
        if ( isset( $data['not_same_site'] ) && ! empty( $data['not_same_site'] ) ) {
            $error_msg .= '，不在站点白名单: ' . implode( ', ', $data['not_same_site'] );
        }
        
        return array(
            'success' => false,
            'message' => $error_msg,
            'data'    => $data,
        );
    }
    
    /**
     * 记录推送日志
     * 
     * @param string $status 状态 (success/error)
     * @param string $url    推送的URL
     * @param array  $result 推送结果
     */
    private function log_push( string $status, string $url, array $result ): void {
        $logs = get_option( 'seopress_baidu_push_logs', array() );
        
        // 添加新日志
        array_unshift( $logs, array(
            'time'    => current_time( 'mysql' ),
            'status'  => $status,
            'url'     => $url,
            'message' => $result['message'] ?? '',
        ) );
        
        // 只保留最近100条
        $logs = array_slice( $logs, 0, 100 );
        
        update_option( 'seopress_baidu_push_logs', $logs );
    }
    
    /**
     * 获取推送日志
     * 
     * @param int $limit 数量限制
     * @return array
     */
    public function get_push_logs( int $limit = 20 ): array {
        $logs = get_option( 'seopress_baidu_push_logs', array() );
        return array_slice( $logs, 0, $limit );
    }
    
    /**
     * 获取推送统计
     * 
     * @return array
     */
    public function get_push_stats(): array {
        $logs = get_option( 'seopress_baidu_push_logs', array() );
        
        $stats = array(
            'total'   => count( $logs ),
            'success' => 0,
            'error'   => 0,
        );
        
        foreach ( $logs as $log ) {
            if ( $log['status'] === 'success' ) {
                $stats['success']++;
            } else {
                $stats['error']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * 输出百度自动推送 JS
     */
    public function output_auto_push_js(): void {
        if ( ! $this->is_push_enabled() ) {
            return;
        }
        
        ?>
        <script>
        (function(){
            var bp = document.createElement('script');
            var curProtocol = window.location.protocol.split(':')[0];
            if (curProtocol === 'https') {
                bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
            }
            else {
                bp.src = 'http://push.zhanzhang.baidu.com/push.js';
            }
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(bp, s);
        })();
        </script>
        <?php
    }
    
    /**
     * AJAX 测试推送
     */
    public function ajax_test_push(): void {
        check_ajax_referer( 'seopress_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $url = home_url( '/' );
        $result = $this->push_urls( array( $url ) );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }
    
    /**
     * AJAX 手动推送
     */
    public function ajax_manual_push(): void {
        check_ajax_referer( 'seopress_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => '权限不足' ) );
        }
        
        $urls = isset( $_POST['urls'] ) ? array_map( 'esc_url_raw', (array) $_POST['urls'] ) : array();
        
        if ( empty( $urls ) ) {
            wp_send_json_error( array( 'message' => '请提供要推送的URL' ) );
        }
        
        $result = $this->push_urls( $urls );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }
}
