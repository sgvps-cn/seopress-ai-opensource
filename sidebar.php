<?php
/**
 * 侧边栏模板
 *
 * @package SeoPress_AI
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area sidebar">
    <div class="sidebar-inner">
        <?php dynamic_sidebar('sidebar-1'); ?>
    </div>
</aside>
