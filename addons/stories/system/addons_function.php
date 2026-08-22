<?php
if (!defined('IN_SCRIPT')) { define('IN_SCRIPT', true); }
require_once __DIR__ . '/config.php';

/**
 * استدعِ هذه الدالة من داخل قالب الغرفة/الشات الرئيسي في المكان الذي
 * تريد ظهور شريط الستوريات فيه (عادة أعلى قائمة الغرف أو أعلى الشات).
 *
 * مثال الاستخدام داخل ملفات السكربت الأساسية:
 *   <?php echo stories_render_bar(); ?>
 */
function stories_render_bar()
{
    if (!stories_is_logged_in()) {
        return '';
    }
    ob_start();
    include __DIR__ . '/../files/stories.php';
    return ob_get_clean();
}

/**
 * ضع ناتج هذه الدالة داخل <head> (أو قبل إغلاق </body>) في القالب الرئيسي
 * لتحميل ملفات التنسيق والسكربت الخاصة بالإضافة.
 *
 * مثال الاستخدام:
 *   <?php echo stories_assets(); ?>
 */
function stories_assets()
{
    $css = STORIES_ADDON_URL . 'files/style.css';
    $js = STORIES_ADDON_URL . 'files/script.js';
    return "<link rel=\"stylesheet\" href=\"{$css}\">\n<script src=\"{$js}\" defer></script>\n";
}
