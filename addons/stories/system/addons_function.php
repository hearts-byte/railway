<?php
if (!defined('BOOM')) { define('BOOM', true); }
require_once __DIR__ . '/helpers.php';

/**
 * ملاحظة مهمة: هذه الدالة أصبحت اختيارية وغير مطلوبة للتشغيل العادي.
 * الإضافة الآن تحقن شريط الستوريات بنفسها بالكامل عبر الجافاسكربت
 * (script.js) داخل نافذة "المتصلين" الموجودة أصلاً بالسكربت، بدون أي
 * تعديل يدوي بملفات القالب الأساسية. لا تستدعِ هذه الدالة يدويًا من
 * أي ملف قالب أساسي (مثل chat.php) لأن هذا سيولّد شريطاً مكرراً
 * بمعرّفات (id) متعارضة مع النسخة اللي تحقنها الإضافة تلقائياً.
 * الدالة موجودة فقط للرجوع إليها أو الاستخدام اليدوي المتقدم.
 *
 *   <?php echo stories_render_bar(); ?>
 */
function stories_render_bar()
{
    if (!stories_is_logged_in()) {
        return '';
    }
    ob_start();
    include __DIR__ . '/bar_template.php';
    return ob_get_clean();
}

/**
 * ضع ناتج هذه الدالة داخل <head> (أو قبل إغلاق </body>) في القالب الرئيسي
 * لتحميل ملفات التنسيق والسكربت الخاصة بالإضافة.
 *
 *   <?php echo stories_assets(); ?>
 */
function stories_assets()
{
    $css = STORIES_ADDON_URL . 'files/style.css';
    $js = STORIES_ADDON_URL . 'files/script.js';
    return "<link rel=\"stylesheet\" href=\"{$css}\">\n<script src=\"{$js}\" defer></script>\n";
}
