<?php
/**
 * تنظيف دوري (اختياري) لحذف ملفات الوسائط الخاصة بالستوريات المنتهية فعلياً من الخادم.
 * الستوريات المنتهية تختفي تلقائياً من العرض عبر شرط expires_at بالاستعلامات،
 * لكن هذا الملف يحذف الملفات الفعلية من مجلد uploads/stories/ لتوفير المساحة.
 *
 * الاستخدام: أضف Cron Job على السيرفر لتشغيله كل ساعة مثلاً:
 *   0 * * * * php /path/to/addons/stories/system/cron_cleanup.php
 *
 * ملاحظة: هذا الملف يُشغّل من الطرفية (CLI) مباشرة وليس عبر متصفح، لذا يتصل
 * بقاعدة البيانات مباشرة عبر ملف الكور system/database.php بدل نظام الجلسة
 * (الذي يعتمد على كوكيز المتصفح وغير متاح بالـ CLI).
 */

define('BOOM', true);
require_once __DIR__ . '/../../../system/database.php';
require_once __DIR__ . '/helpers.php';

$res = $mysqli->query("SELECT id, content, type FROM cody_stories
                        WHERE expires_at <= NOW() AND status = 1 AND type IN ('image','video')");

$deleted_files = 0;
while ($row = $res->fetch_assoc()) {
    $relative = ltrim(str_replace(STORIES_UPLOAD_URL, '', $row['content']), '/');
    $full_path = STORIES_UPLOAD_DIR . basename($relative);
    if (is_file($full_path)) {
        @unlink($full_path);
        $deleted_files++;
    }
}

$mysqli->query("UPDATE cody_stories SET status = 0 WHERE expires_at <= NOW() AND status = 1");

echo "تم تنظيف {$deleted_files} ملف/ملفات منتهية.\n";
