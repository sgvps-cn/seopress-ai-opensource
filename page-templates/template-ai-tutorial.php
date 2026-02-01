<?php
/**
 * Template Name: AI 配置教程
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

get_header();
?>

<div class="wrap container seopress-tutorial-page">
    <header class="page-header">
        <h1 class="page-title"><?php the_title(); ?></h1>
    </header>

    <div class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">
                <div class="seopress-tutorial-section" style="background:#e6f7e6;border:1px solid #52c41a;padding:20px;border-radius:8px;margin-bottom:30px;">
                    <h2 style="color:#52c41a;margin-top:0;">🌟 硅基流动 SiliconFlow（强烈推荐 - 永久免费）</h2>
                    <p>国内最良心的AI模型聚合平台，<strong style="color:#f5222d;">9B以下模型永久免费、无限使用</strong>，包含 Qwen2.5、DeepSeek-V2.5、Llama 3.1 等主流开源模型。</p>
                    <ol>
                        <li>访问 <a href="https://cloud.siliconflow.cn/" target="_blank" rel="nofollow">cloud.siliconflow.cn</a></li>
                        <li>使用手机号注册账号并登录</li>
                        <li>点击左侧菜单 "API密钥" → "新建API密钥"</li>
                        <li>复制生成的 API Key 并粘贴到本主题的 <a href="<?php echo admin_url('themes.php?page=seopress-ai-settings&tab=ai'); ?>">AI 设置</a> 中</li>
                        <li>在 AI 服务提供商选择 "硅基流动（免费推荐）"</li>
                    </ol>
                    <p><strong>推荐模型：</strong>Qwen2.5-7B-Instruct（中文能力强）、DeepSeek-V2.5（推理能力强）</p>
                </div>

                <div class="seopress-tutorial-grid">
                    <div class="seopress-tutorial-card">
                        <h3>DeepSeek</h3>
                        <p>DeepSeek 提供高质量的中文内容生成能力，价格极低。</p>
                        <a href="https://platform.deepseek.com" target="_blank" rel="nofollow" class="button">获取 API Key</a>
                    </div>

                    <div class="seopress-tutorial-card">
                        <h3>通义千问</h3>
                        <p>阿里云百炼平台提供通义千问模型，新用户在新加坡地域有免费额度。</p>
                        <a href="https://bailian.console.aliyun.com" target="_blank" rel="nofollow" class="button">获取 API Key</a>
                    </div>

                    <div class="seopress-tutorial-card">
                        <h3>百度文心一言</h3>
                        <p>百度智能云千帆平台提供文心一言大模型，部分模型长期免费开放。</p>
                        <a href="https://qianfan.baidubce.com" target="_blank" rel="nofollow" class="button">获取 API Key</a>
                    </div>

                    <div class="seopress-tutorial-card">
                        <h3>月之暗面 Kimi</h3>
                        <p>Kimi 擅长长文本处理，支持 128K 上下文。</p>
                        <a href="https://platform.moonshot.cn" target="_blank" rel="nofollow" class="button">获取 API Key</a>
                    </div>
                </div>

                <div class="seopress-tutorial-section" style="margin-top:30px;">
                    <h3>如何配置百度自动推送？</h3>
                    <p>配置百度推送后，新发布的文章会自动提交到百度收录，加快收录速度。</p>
                    <ol>
                        <li>访问 <a href="https://ziyuan.baidu.com" target="_blank" rel="nofollow">百度搜索资源平台</a></li>
                        <li>添加并验证您的网站</li>
                        <li>进入 "普通收录" -> "API 提交"</li>
                        <li>复制接口调用地址中的 <code>token</code> 参数值</li>
                        <li>粘贴到 <a href="<?php echo admin_url('themes.php?page=seopress-ai-settings&tab=seo'); ?>">SEO 设置</a> 中的 "百度推送 Token"</li>
                    </ol>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.seopress-tutorial-page {
    padding-top: 40px;
    padding-bottom: 40px;
}
.seopress-tutorial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.seopress-tutorial-card {
    border: 1px solid #eee;
    padding: 20px;
    border-radius: 8px;
    background: #fff;
    transition: transform 0.2s;
}
.seopress-tutorial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.seopress-tutorial-card h3 {
    margin-top: 0;
    color: #333;
}
.seopress-tutorial-card .button {
    display: inline-block;
    padding: 8px 16px;
    background: #2271b1;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 10px;
}
.seopress-tutorial-card .button:hover {
    background: #135e96;
}
</style>

<?php
get_footer();
