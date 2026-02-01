/**
 * SeoPress AI Theme - 后台管理脚本
 * 
 * @package SeoPress_AI
 * @version 1.0.0
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        SeoPressAdmin.init();
    });

    var SeoPressAdmin = {
        /**
         * 初始化
         */
        init: function() {
            this.initTabs();
            this.initAIGenerator();
            this.initTestConnection();
            this.initBaiduPushTest();
        },

        /**
         * 选项卡切换
         */
        initTabs: function() {
            var tabs = document.querySelectorAll('.seopress-tab');
            var contents = document.querySelectorAll('.seopress-tab-content');

            if (!tabs.length) return;

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    var target = this.dataset.tab;

                    // 更新选项卡状态
                    tabs.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');

                    // 更新内容区域
                    contents.forEach(function(c) { c.classList.remove('active'); });
                    var targetContent = document.getElementById('tab-' + target);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }

                    // 更新 URL hash
                    history.replaceState(null, null, '#' + target);
                });
            });

            // 从 URL hash 恢复选项卡
            var hash = window.location.hash.slice(1);
            if (hash) {
                var targetTab = document.querySelector('.seopress-tab[data-tab="' + hash + '"]');
                if (targetTab) {
                    targetTab.click();
                }
            }
        },

        /**
         * AI 文章生成器
         */
        initAIGenerator: function() {
            var form = document.getElementById('seopress-generator-form');
            var generateBtn = document.getElementById('seopress-generate-btn');
            var resultArea = document.getElementById('seopress-result');
            var resultContent = document.getElementById('seopress-result-content');
            var publishBtn = document.getElementById('seopress-publish-btn');
            var regenerateBtn = document.getElementById('seopress-regenerate-btn');

            if (!form || !generateBtn) return;

            var self = this;
            var generatedContent = null;

            generateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.generateArticle(form, resultArea, resultContent, generateBtn);
            });

            if (publishBtn) {
                publishBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.publishArticle(form, publishBtn);
                });
            }

            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.generateArticle(form, resultArea, resultContent, generateBtn);
                });
            }
        },

        /**
         * 生成文章
         */
        generateArticle: function(form, resultArea, resultContent, btn) {
            var formData = new FormData(form);
            formData.append('action', 'seopress_generate_article');
            formData.append('nonce', seopressAdmin.nonce);

            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span>AI 正在生成中...';

            fetch(seopressAdmin.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    resultContent.innerHTML = data.data.content;
                    resultArea.classList.add('show');
                    
                    // 存储生成的内容
                    form.dataset.generatedTitle = data.data.title || '';
                    form.dataset.generatedContent = data.data.content || '';
                    
                    // 滚动到结果区域
                    resultArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    alert('生成失败: ' + (data.data || '未知错误'));
                }
            })
            .catch(function(error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('请求失败: ' + error.message);
            });
        },

        /**
         * 发布文章
         */
        publishArticle: function(form, btn) {
            if (!confirm('确定要发布这篇文章吗？')) return;

            var formData = new FormData();
            formData.append('action', 'seopress_publish_article');
            formData.append('nonce', seopressAdmin.nonce);
            formData.append('title', form.dataset.generatedTitle || document.getElementById('article-keyword')?.value || '');
            formData.append('content', form.dataset.generatedContent || '');
            formData.append('category', form.querySelector('[name="category"]')?.value || '');
            formData.append('push_baidu', form.querySelector('[name="push_baidu"]')?.checked ? '1' : '0');

            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span>发布中...';

            fetch(seopressAdmin.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    alert('文章发布成功！');
                    if (data.data.edit_url) {
                        window.open(data.data.edit_url, '_blank');
                    }
                } else {
                    alert('发布失败: ' + (data.data || '未知错误'));
                }
            })
            .catch(function(error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('请求失败: ' + error.message);
            });
        },

        /**
         * AI 连接测试
         */
        initTestConnection: function() {
            var testBtns = document.querySelectorAll('.seopress-test-connection');
            var self = this;

            testBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var provider = this.dataset.provider;
                    self.testAIConnection(provider, this);
                });
            });
        },

        /**
         * 测试 AI 连接
         */
        testAIConnection: function(provider, btn) {
            var formData = new FormData();
            formData.append('action', 'seopress_test_ai_connection');
            formData.append('nonce', seopressAdmin.nonce);
            formData.append('provider', provider);

            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0;"></span>';

            fetch(seopressAdmin.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalText;

                var statusEl = btn.nextElementSibling;
                if (!statusEl || !statusEl.classList.contains('seopress-status')) {
                    statusEl = document.createElement('span');
                    statusEl.className = 'seopress-status';
                    btn.parentNode.insertBefore(statusEl, btn.nextSibling);
                }

                if (data.success) {
                    statusEl.className = 'seopress-status seopress-status-success';
                    statusEl.innerHTML = '<span class="dashicons dashicons-yes"></span> 连接成功';
                } else {
                    statusEl.className = 'seopress-status seopress-status-error';
                    statusEl.innerHTML = '<span class="dashicons dashicons-no"></span> ' + (data.data || '连接失败');
                }
            })
            .catch(function(error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('测试失败: ' + error.message);
            });
        },

        /**
         * 百度推送测试
         */
        initBaiduPushTest: function() {
            var testBtn = document.getElementById('seopress-test-baidu-push');
            if (!testBtn) return;

            var self = this;
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.testBaiduPush(this);
            });
        },

        /**
         * 测试百度推送
         */
        testBaiduPush: function(btn) {
            var formData = new FormData();
            formData.append('action', 'seopress_test_baidu_push');
            formData.append('nonce', seopressAdmin.nonce);

            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span>测试中...';

            fetch(seopressAdmin.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    alert('百度推送测试成功！\n' + JSON.stringify(data.data, null, 2));
                } else {
                    alert('推送测试失败: ' + (data.data || '未知错误'));
                }
            })
            .catch(function(error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('请求失败: ' + error.message);
            });
        }
    };

    window.SeoPressAdmin = SeoPressAdmin;

})();
