<?php
/**
 * تنظيف دوري (اختياري) لحذف ملفات الوسائط الخاصة بالستوريات المنتهية فعلياً من الخادم.
 * الستوريات المنتهية تختفي تلقائياً من العرض عبر شرط expires_at في الاستعلامات،
 * لكن هذا الملف يقوم بحذف الملفات الفعلية من مجلد uploads/stories/ لتوفير المساحة.
 *
 * طريقة الاستخدام: أضف مهمة Cron Job على الخادم لتشغيل هذا الملف كل ساعة مثلاً:
 *   0 * * * * php /path/to/addons/stories/system/cron_cleanup.php
 */

define('IN_SCRIPT', true);
require_once __DIR__ . '/config.php';

$db = stories_db();

$res = $db->query("SELECT id, content, type FROM cody_stories
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

// تحديث حالة الستوريات المنتهية إلى غير نشطة (تنظيف منطقي إضافي)
$db->query("UPDATE cody_stories SET status = 0 WHERE expires_at <= NOW() AND status = 1");

echo "تم تنظيف {$deleted_files} ملف/ملفات منتهية.\n";
