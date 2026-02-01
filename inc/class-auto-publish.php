<?php
/**
 * Auto Publish Manager
 * 
 * 使用 WordPress Cron 实现定时自动发布 AI 生成的文章
 * 
 * @package SeoPress_AI
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SeoPress_Auto_Publish {
    const CRON_HOOK = 'seopress_auto_publish';
    private static $instance = null;

    /**
     * 获取实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct() {
        add_action('init', array($this, 'schedule_cron'));
        add_action(self::CRON_HOOK, array($this, 'execute_auto_publish'));
        
        // AJAX hooks stubs (legacy support or potential future use)
        add_action('wp_ajax_seopress_manual_publish', array($this, 'ajax_manual_publish'));
        add_action('wp_ajax_seopress_get_queue', array($this, 'ajax_get_queue'));
        add_action('wp_ajax_seopress_add_to_queue', array($this, 'ajax_add_to_queue'));
        add_action('wp_ajax_seopress_remove_from_queue', array($this, 'ajax_remove_from_queue'));
    }

    /**
     * 注册 Cron 任务
     */
    public function schedule_cron() {
        // 注册自定义间隔
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $settings = get_option('seopress_auto_publish_settings', array());
            $interval = isset($settings['interval']) ? $settings['interval'] : 'daily';
            wp_schedule_event(time(), $interval, self::CRON_HOOK);
        }
    }

    /**
     * 取消 Cron 任务
     */
    public function unschedule_cron() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    /**
     * 重新调度 Cron
     */
    public function reschedule_cron() {
        $this->unschedule_cron();
        $this->schedule_cron();
    }

    /**
     * 添加自定义 Cron 间隔
     */
    public function add_cron_intervals($schedules) {
        $schedules['every_6_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => __('每6小时', 'seopress-ai')
        );
        $schedules['every_12_hours'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => __('每12小时', 'seopress-ai')
        );
        $schedules['twice_daily'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => __('每天两次', 'seopress-ai')
        );
        $schedules['hourly'] = array(
            'interval' => HOUR_IN_SECONDS,
            'display'  => __('每小时', 'seopress-ai')
        );
        return $schedules;
    }

    /**
     * 执行自动发布
     */
    public function execute_auto_publish() {
        $settings = get_option('seopress_auto_publish_settings', array());
        
        // 检查是否启用
        if (empty($settings['enabled'])) {
            return;
        }
        
        $count = isset($settings['auto_count']) ? intval($settings['auto_count']) : 1;
        
        // Loop for count
        for ($i = 0; $i < $count; $i++) {
            // New Group Logic
            if (!empty($settings['publish_groups'])) {
                $this->generate_and_publish_from_groups($settings);
            } else {
                // Legacy: Try to migrate on the fly or use simple list if groups missing
                if (!empty($settings['keywords'])) {
                     $temp_settings = $settings;
                     $temp_settings['publish_groups'] = array(
                        array(
                            'category' => isset($settings['default_category']) ? $settings['default_category'] : 0,
                            'keywords' => $settings['keywords']
                        )
                     );
                     $this->generate_and_publish_from_groups($temp_settings);
                }
            }
            
            // Avoid timeout / rate limit
            if ($i < $count - 1) {
                sleep(5);
            }
        }
    }
    
    /**
     * 从分组中生成并发布 (New Core Logic)
     */
    private function generate_and_publish_from_groups($settings) {
        $groups = $settings['publish_groups'];
        if (empty($groups)) return;
        
        // Get last used group index to rotate
        $last_group_index = get_option('seopress_auto_publish_last_group_index', -1);
        $current_group_index = ($last_group_index + 1) % count($groups);
        
        // Try to find a valid keyword in the current group, or rotate until found
        $attempts = 0;
        $max_attempts = count($groups);
        $selected_keyword = '';
        $selected_category = 0;
        
        while ($attempts < $max_attempts) {
            $group = $groups[$current_group_index];
            $keywords_list = explode("\n", $group['keywords']);
            $keywords_list = array_map('trim', $keywords_list);
            $keywords_list = array_values(array_filter($keywords_list)); // Re-index array
            
            if (!empty($keywords_list)) {
                // SEQUENTIAL LOGIC:
                // Track the last used index for this specific group to ensure strict order.
                $option_name = 'seopress_last_kw_index_group_' . $current_group_index;
                $last_kw_index = (int)get_option($option_name, -1);
                
                // Calculate next index
                $next_kw_index = ($last_kw_index + 1);
                
                // Cycle if reached end
                if ($next_kw_index >= count($keywords_list)) {
                    $next_kw_index = 0;
                }
                
                $selected_keyword = $keywords_list[$next_kw_index];
                
                // Save state immediately
                update_option($option_name, $next_kw_index);
                
                $selected_category = $group['category'];
                
                // Update group index for next time (rotate group)
                update_option('seopress_auto_publish_last_group_index', $current_group_index);
                break;
            }
            
            // Move to next group if this one is empty
            $current_group_index = ($current_group_index + 1) % count($groups);
            $attempts++;
        }
        
        if (empty($selected_keyword)) {
            $this->log_error('没有找到有效的关键词配置，请检查设置。');
            return;
        }
        
        // Start Generation with Retry Logic for Duplicates
        $ai_manager = SeoPress_AI_Manager::get_instance();
        $retry_count = 0;
        $max_retries = 3;
        $published_success = false;

        while ($retry_count < $max_retries && !$published_success) {
            
            // Build Prompt
            $prompt = $this->build_prompt($selected_keyword, $settings);
            
            // If checking duplicate content, maybe vary the prompt slightly?
            // For now, standard generation usually varies enough.
            
            $result = $ai_manager->generate_content($prompt);
            
            if (empty($result['success'])) {
                $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
                $this->log_error('生成失败: ' . $error_msg);
                return; // API failure, don't retry immediately to save quota/avoid loops
            }
            
            $content = isset($result['content']) ? $result['content'] : '';
            $title = $this->extract_title($content, $selected_keyword);
            $clean_content = $this->remove_title_from_content($content);

            // DUPLICATE CHECK: Check if a post with this title already exists
            if ($this->is_title_duplicate($title)) {
                $retry_count++;
                $this->log_error("检测到重复内容/标题 '{$title}'，正在尝试重新生成 (第 {$retry_count} 次)...");
                continue; // Retry loop
            }

            // Publish
            $post_data = array(
                'keyword' => $selected_keyword,
                'title'   => $title,
                'content' => $clean_content,
                'category' => $selected_category,
            );
            
            $post_id = $this->publish_article($post_data);
            if ($post_id) {
                $published_success = true;
            } else {
                 return; // Publish failed (db error?), stop.
            }
        }

        if (!$published_success) {
            $this->log_error("连续 {$max_retries} 次生成均检测到重复或失败，放弃本次发布。");
        }
    }

    /**
     * 构建 AI 提示词
     */
    private function build_prompt($keyword, $settings) {
        $template = isset($settings['prompt_template']) ? $settings['prompt_template'] : '';
        
        if (empty($template)) {
            $template = "请围绕关键词\"{keyword}\"撰写一篇原创的SEO优化文章。\n\n要求：\n1. 文章字数不少于1500字\n2. 包含合理的H2、H3标题结构\n3. 自然融入关键词，密度控制在2%-3%\n4. 内容具有实用价值，语言流畅自然\n5. 结尾包含总结和行动建议\n\n请直接输出文章内容，第一行为标题。";
        }
        
        return str_replace('{keyword}', $keyword, $template);
    }

    /**
     * 从内容提取标题 (Optimized)
     */
    private function extract_title($content, $keyword) {
        $lines = explode("\n", trim($content));
        $first_line = trim($lines[0]);
        
        // Remove Markdown headers (#, ##, etc.) and bold markers
        $clean_title = preg_replace('/^#+\s*|\*\*|《|》/', '', $first_line);
        $clean_title = strip_tags($clean_title);
        $clean_title = trim($clean_title);
        
        // Validation: Length check (5-100 chars) and ensure it's not just "Title:"
        if (mb_strlen($clean_title) > 4 && mb_strlen($clean_title) < 100 && stripos($clean_title, '标题') === false) {
            return $clean_title;
        }
        
        // Fallback: Combine keyword with generic suffix if extraction fails
        return $keyword . ' - 深度解析';
    }
    
    /**
     * 移除标题行/清理内容 (Optimized)
     */
    private function remove_title_from_content($content) {
        $lines = explode("\n", trim($content));
        if (count($lines) > 0) {
            $first_line = trim($lines[0]);
            // Logic: If first line contains "Title", "标题", or starts with #, remove it
            if (preg_match('/^#+\s*/', $first_line) || stripos($first_line, '标题') !== false || mb_strlen($first_line) < 100) {
                 array_shift($lines);
            }
        }
        // Remove empty lines at start
        while (count($lines) > 0 && empty(trim($lines[0]))) {
            array_shift($lines);
        }
        return implode("\n", $lines);
    }

    /**
     * 发布文章
     */
    public function publish_article($data) {
        $post_data = array(
            'post_title'   => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['content']),
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id() ?: 1,
            'post_type'    => 'post',
        );
        
        if (!empty($data['category'])) {
            $post_data['post_category'] = array(intval($data['category']));
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            $this->log_error('发布失败: ' . $post_id->get_error_message());
            return false;
        }
        
        if (!empty($data['keyword'])) {
            update_post_meta($post_id, '_seopress_keyword', $data['keyword']);
            update_post_meta($post_id, '_seopress_ai_generated', 1);
            update_post_meta($post_id, '_seopress_generated_at', current_time('mysql'));
        }
        
        $this->generate_seo_meta($post_id, $data);
        
        $settings = get_option('seopress_auto_publish_settings', array());
        if (!empty($settings['auto_push_baidu'])) {
            if (class_exists('SeoPress_AI_Baidu_Push')) {
                $baidu_push = SeoPress_AI_Baidu_Push::get_instance();
                $baidu_push->push_url(get_permalink($post_id));
            }
        }
        
        $this->log_publish($data);
        
        return $post_id;
    }

    /**
     * 生成 SEO Meta
     */
    private function generate_seo_meta($post_id, $data) {
        $post = get_post($post_id);
        $keyword = isset($data['keyword']) ? $data['keyword'] : '';
        
        $description = wp_trim_words(strip_tags($post->post_content), 150, '...');
        update_post_meta($post_id, '_seopress_meta_description', $description);
        
        if ($keyword) {
            update_post_meta($post_id, '_seopress_meta_keywords', $keyword);
        }
        
        update_post_meta($post_id, '_seopress_focus_keyword', $keyword);
    }

    /**
     * 检查关键词是否已发布
     */
    private function is_keyword_published($keyword) {
        $args = array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'meta_query'  => array(
                array(
                    'key'   => '_seopress_keyword',
                    'value' => $keyword,
                ),
            ),
            'posts_per_page' => 1,
            'fields' => 'ids'
        );
        
        $query = new WP_Query($args);
        return $query->have_posts();
    }

    /**
     * 检查标题是否存在重复
     */
    private function is_title_duplicate($title) {
        if (empty($title)) return false;
        
        $args = array(
            'post_type'   => 'post',
            'post_status' => 'any', // Check published, draft, etc.
            'title'       => $title,
            'posts_per_page' => 1,
            'fields'      => 'ids'
        );
        
        $query = new WP_Query($args);
        return $query->have_posts();
    }

    /**
     * 记录发布日志
     */
    private function log_publish($item) {
        $log = get_option('seopress_publish_log', array());
        
        $log[] = array(
            'time'    => current_time('mysql'),
            'keyword' => isset($item['keyword']) ? $item['keyword'] : '',
            'title'   => isset($item['title']) ? $item['title'] : '',
            'status'  => 'success',
        );
        
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        
        update_option('seopress_publish_log', $log);
    }

    /**
     * 记录错误日志
     */
    private function log_error($message) {
        $log = get_option('seopress_error_log', array());
        
        $log[] = array(
            'time'    => current_time('mysql'),
            'message' => $message,
        );
        
        if (count($log) > 50) {
            $log = array_slice($log, -50);
        }
        
        update_option('seopress_error_log', $log);
    }

    /**
     * 获取下次执行时间
     */
    public function get_next_run() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            // Use wp_date to automatically respect the site's timezone setting (e.g. Asia/Shanghai)
            return wp_date('Y-m-d H:i:s', $timestamp);
        }
        return null;
    }

    // AJAX Stubs
    public function ajax_manual_publish() {}
    public function ajax_get_queue() {}
    public function ajax_add_to_queue() {}
    public function ajax_remove_from_queue() {}
}

// 初始化
add_action('init', function() {
    SeoPress_Auto_Publish::get_instance();
});
