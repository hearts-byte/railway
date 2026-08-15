<?php
// ملف بديل غير مشفّر لوظيفة حفظ إعدادات الإضافة
// يسوي نفس اللي كان المفروض يسويه action.php (save_settings) لكن بشكل مباشر وواضح

require_once('../../../system/config_addons.php');

// نفس فحص الصلاحية المستخدم بملف config.php الأصلي
if (!canManageAddons()) {
    http_response_code(403);
    echo '0';
    exit;
}

$addon_name = basename(dirname(__DIR__)); // يقرأ اسم مجلد الإضافة تلقائيًا (Avatar_Frame-BLK)

if (isset($_POST['save_settings'])) {

    $access = isset($_POST['set_access']) ? (int) $_POST['set_access'] : 0;
    $price  = isset($_POST['set_price'])  ? (int) $_POST['set_price']  : 0;

    global $mysqli;

    $stmt = $mysqli->prepare("UPDATE boom_addons SET addons_access = ?, custom1 = ? WHERE addons = ?");
    if (!$stmt) {
        echo '0';
        exit;
    }

    $price_str = (string) $price; // custom1 عمود varchar بالجدول
    $stmt->bind_param('iss', $access, $price_str, $addon_name);

    if ($stmt->execute()) {
        // امسح الكاش المحتمل بـ Redis حتى تنعكس الإعدادات فورًا (إذا الدوال موجودة)
        if (function_exists('redisDeleteObject')) {
            @redisDeleteObject('avatar_frame:list');
        }
        echo '1';
    } else {
        echo '0';
    }
    exit;
}

echo '0';
