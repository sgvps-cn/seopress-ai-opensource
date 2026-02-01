/**
 * SeoPress AI 主题 - 前端交互脚本
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initThemeToggle();
        initMobileMenu();
        initBackToTop();
        initSearch();
        initReadingProgress();
        initSmoothScroll();
        initHeaderScroll();
        initCopyLink();
        initImageLightbox();
    });

    /**
     * 主题切换（暗色/亮色模式）
     */
    function initThemeToggle() {
        const toggle = document.getElementById('theme-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('sp-theme', newTheme);
            
            // 动画效果
            toggle.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                toggle.style.transform = '';
            }, 300);
        });

        // 监听系统主题变化
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('sp-theme')) {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * 移动端菜单
     */
    function initMobileMenu() {
        const menuToggle = document.getElementById('menu-toggle');
        const mainNav = document.getElementById('main-navigation');
        
        if (!menuToggle || !mainNav) return;

        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
            
            // 防止背景滚动
            document.body.style.overflow = isExpanded ? '' : 'hidden';
        });

        // 点击导航链接后关闭菜单
        mainNav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                menuToggle.classList.remove('active');
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        // ESC 关闭菜单
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainNav.classList.contains('active')) {
                menuToggle.classList.remove('active');
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    /**
     * 回到顶部按钮
     */
    function initBackToTop() {
        const button = document.getElementById('back-to-top');
        if (!button) return;

        let isVisible = false;
        const threshold = 400;

        function toggleButton() {
            const shouldShow = window.scrollY > threshold;
            
            if (shouldShow !== isVisible) {
                isVisible = shouldShow;
                button.classList.toggle('visible', shouldShow);
            }
        }

        // 使用 requestAnimationFrame 优化性能
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(function() {
                    toggleButton();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        button.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // 初始检查
        toggleButton();
    }

    /**
     * 搜索弹窗
     */
    function initSearch() {
        const searchToggle = document.getElementById('search-toggle');
        const searchOverlay = document.getElementById('search-overlay');
        const searchClose = document.getElementById('search-close');
        const searchField = searchOverlay?.querySelector('.search-field-overlay');
        
        if (!searchToggle || !searchOverlay) return;

        function openSearch() {
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => searchField?.focus(), 100);
        }

        function closeSearch() {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        searchToggle.addEventListener('click', openSearch);
        searchClose?.addEventListener('click', closeSearch);

        // 点击背景关闭
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) {
                closeSearch();
            }
        });

        // ESC 关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                closeSearch();
            }
        });

        // Ctrl/Cmd + K 打开搜索
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchOverlay.classList.contains('active')) {
                    closeSearch();
                } else {
                    openSearch();
                }
            }
        });
    }

    /**
     * 阅读进度条
     */
    function initReadingProgress() {
        const progressBar = document.getElementById('reading-progress');
        const content = document.getElementById('entry-content');
        
        if (!progressBar || !content) return;

        function updateProgress() {
            const contentRect = content.getBoundingClientRect();
            const contentTop = window.scrollY + contentRect.top;
            const contentHeight = content.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrolled = window.scrollY - contentTop + windowHeight;
            const progress = Math.min(Math.max(scrolled / contentHeight * 100, 0), 100);
            
            progressBar.style.width = progress + '%';
        }

        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(function() {
                    updateProgress();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        updateProgress();
    }

    /**
     * 平滑滚动
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (!target) return;

                e.preventDefault();
                
                const headerHeight = document.querySelector('.site-header')?.offsetHeight || 0;
                const targetPosition = target.getBoundingClientRect().top + window.scrollY - headerHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });

                // 更新 URL
                history.pushState(null, null, href);
            });
        });
    }

    /**
     * Header 滚动效果
     */
    function initHeaderScroll() {
        const header = document.getElementById('site-header');
        if (!header) return;

        let lastScrollY = window.scrollY;
        let ticking = false;

        function updateHeader() {
            const scrollY = window.scrollY;
            
            // 添加/移除滚动类
            header.classList.toggle('scrolled', scrollY > 50);
            
            // 向下滚动隐藏，向上滚动显示（可选）
            // if (scrollY > lastScrollY && scrollY > 200) {
            //     header.style.transform = 'translateY(-100%)';
            // } else {
            //     header.style.transform = 'translateY(0)';
            // }
            
            lastScrollY = scrollY;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(function() {
                    updateHeader();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * 复制链接功能
     */
    function initCopyLink() {
        const copyButton = document.getElementById('copy-link');
        if (!copyButton) return;

        copyButton.addEventListener('click', function() {
            const url = window.location.href;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    showToast('链接已复制到剪贴板');
                }).catch(function() {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        });

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showToast('链接已复制到剪贴板');
            } catch (err) {
                showToast('复制失败，请手动复制');
            }
            
            document.body.removeChild(textarea);
        }
    }

    /**
     * Toast 提示
     */
    function showToast(message, duration = 2000) {
        // 移除已有的 toast
        const existingToast = document.querySelector('.sp-toast');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = 'sp-toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--sp-text-primary);
            color: var(--sp-surface);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 10000;
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        `;

        document.body.appendChild(toast);

        // 动画显示
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
        });

        // 自动消失
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    /**
     * 图片灯箱（简易版）
     */
    function initImageLightbox() {
        const contentImages = document.querySelectorAll('.entry-content img');
        if (!contentImages.length) return;

        contentImages.forEach(function(img) {
            img.style.cursor = 'zoom-in';
            
            img.addEventListener('click', function() {
                const overlay = document.createElement('div');
                overlay.className = 'sp-lightbox';
                overlay.style.cssText = `
                    position: fixed;
                    inset: 0;
                    background: rgba(0,0,0,0.9);
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: zoom-out;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;

                const imgClone = document.createElement('img');
                imgClone.src = this.src;
                imgClone.alt = this.alt;
                imgClone.style.cssText = `
                    max-width: 90%;
                    max-height: 90%;
                    object-fit: contain;
                    transform: scale(0.95);
                    transition: transform 0.3s ease;
                `;

                overlay.appendChild(imgClone);
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';

                requestAnimationFrame(() => {
                    overlay.style.opacity = '1';
                    imgClone.style.transform = 'scale(1)';
                });

                function closeLightbox() {
                    overlay.style.opacity = '0';
                    imgClone.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        overlay.remove();
                        document.body.style.overflow = '';
                    }, 300);
                }

                overlay.addEventListener('click', closeLightbox);
                document.addEventListener('keydown', function handler(e) {
                    if (e.key === 'Escape') {
                        closeLightbox();
                        document.removeEventListener('keydown', handler);
                    }
                });
            });
        });
    }

})();

// 添加搜索弹窗样式
(function() {
    const style = document.createElement('style');
    style.textContent = `
        .search-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 15vh;
        }
        
        .search-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .search-overlay-inner {
            width: 90%;
            max-width: 600px;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        
        .search-overlay.active .search-overlay-inner {
            transform: translateY(0);
        }
        
        .search-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            cursor: pointer;
            padding: 12px;
            opacity: 0.9;
            transition: all 0.2s;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.25);
        }
        
        .search-form-overlay {
            display: flex;
            background: var(--sp-surface, #fff);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .search-field-overlay {
            flex: 1;
            padding: 20px 24px;
            border: none;
            font-size: 18px;
            background: transparent;
            color: var(--sp-text-primary, #1e293b);
            outline: none;
        }
        
        .search-field-overlay::placeholder {
            color: var(--sp-text-tertiary, #94a3b8);
        }
        
        .search-submit-overlay {
            padding: 20px 24px;
            background: var(--sp-gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
            border: none;
            color: white;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .search-submit-overlay:hover {
            opacity: 0.9;
        }
        
        .search-hint {
            text-align: center;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin-top: 16px;
        }
        
        /* 文章导航样式 */
        .post-navigation {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .post-navigation a {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1.5rem;
            background: var(--sp-surface, #fff);
            border-radius: 1rem;
            box-shadow: var(--sp-shadow-card, 0 4px 20px rgba(0, 0, 0, 0.08));
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .post-navigation a:hover {
            transform: translateY(-4px);
            box-shadow: var(--sp-shadow-card-hover, 0 12px 40px rgba(102, 126, 234, 0.2));
        }
        
        .post-navigation .nav-next {
            text-align: right;
        }
        
        .post-navigation .nav-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--sp-text-tertiary, #94a3b8);
        }
        
        .post-navigation .nav-next .nav-label {
            justify-content: flex-end;
        }
        
        .post-navigation .nav-title {
            font-weight: 600;
            color: var(--sp-text-primary, #1e293b);
            line-height: 1.4;
        }
        
        /* 相关文章样式 */
        .related-posts {
            margin-top: 2rem;
            padding: 2rem;
            background: var(--sp-surface, #fff);
            border-radius: 1.5rem;
            box-shadow: var(--sp-shadow-card, 0 4px 20px rgba(0, 0, 0, 0.08));
        }
        
        .related-posts-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .related-posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        
        .related-post-card {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .related-post-card:hover {
            transform: translateY(-4px);
        }
        
        .related-post-thumbnail {
            aspect-ratio: 16/10;
            border-radius: 0.75rem;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }
        
        .related-post-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .related-post-card:hover .related-post-thumbnail img {
            transform: scale(1.05);
        }
        
        .related-post-placeholder {
            width: 100%;
            height: 100%;
            background: var(--sp-gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .related-post-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--sp-text-primary, #1e293b);
            margin-bottom: 0.25rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .related-post-date {
            font-size: 0.8125rem;
            color: var(--sp-text-tertiary, #94a3b8);
        }
        
        @media (max-width: 768px) {
            .post-navigation {
                grid-template-columns: 1fr;
            }
            
            .post-navigation .nav-next {
                text-align: left;
            }
            
            .post-navigation .nav-next .nav-label {
                justify-content: flex-start;
            }
            
            .related-posts-grid {
                grid-template-columns: 1fr;
            }
            
            .related-post-card {
                flex-direction: row;
                gap: 1rem;
            }
            
            .related-post-thumbnail {
                width: 100px;
                aspect-ratio: 1;
                flex-shrink: 0;
                margin-bottom: 0;
            }
        }
    `;
    document.head.appendChild(style);
})();
