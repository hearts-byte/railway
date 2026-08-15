<?php
// نسخة تشخيصية مؤقتة - ترجع رسالة نصية واضحة بدل ما "تموت" بصمت
// بعد ما نلقى المشكلة، رجّع الملف الأصلي (save_settings_fix.php) واحذف هذا

header('Content-Type: text/plain; charset=UTF-8');

echo "DEBUG STEP 1: بدأ تنفيذ الملف\n";

$config_path = '../../../system/config_addons.php';
echo "DEBUG STEP 2: مسار config_addons.php هو: " . realpath(dirname(__FILE__) . '/' . $config_path) . "\n";

if (!file_exists(dirname(__FILE__) . '/' . $config_path)) {
    echo "DEBUG FAIL: الملف مو موجود بهذا المسار! تحقق من مكان ملف system/config_addons.php الحقيقي.\n";
    exit;
}

require_once($config_path);
echo "DEBUG STEP 3: تم تحميل config_addons.php بنجاح\n";

echo "DEBUG STEP 4: هل الدالة canManageAddons موجودة؟ " . (function_exists('canManageAddons') ? 'نعم' : 'لا') . "\n";

if (function_exists('canManageAddons')) {
    $can = canManageAddons();
    echo "DEBUG STEP 5: نتيجة canManageAddons() = " . var_export($can, true) . "\n";
    if (!$can) {
        echo "DEBUG FAIL: canManageAddons() رجعت false - يعني المستخدم مو معترف فيه كأدمن مسموح له بإدارة الإضافات.\n";
        exit;
    }
} else {
    echo "DEBUG FAIL: الدالة canManageAddons غير معرفة أصلاً - تأكد من تحميل config_addons.php صح.\n";
    exit;
}

echo "DEBUG STEP 6: هل \$mysqli موجود ومتصل؟ " . (isset($mysqli) && $mysqli instanceof mysqli ? 'نعم' : 'لا') . "\n";

echo "DEBUG STEP 7: محتوى \$_POST هو:\n";
print_r($_POST);

$addon_name = basename(dirname(__DIR__));
echo "DEBUG STEP 8: اسم الإضافة المكتشف: $addon_name\n";

if (isset($_POST['save_settings'])) {
    echo "DEBUG STEP 9: save_settings موجود بالطلب، رح نكمل للحفظ\n";

    $access = isset($_POST['set_access']) ? (int) $_POST['set_access'] : 0;
    $price  = isset($_POST['set_price'])  ? (int) $_POST['set_price']  : 0;

    global $mysqli;

    $stmt = $mysqli->prepare("UPDATE boom_addons SET addons_access = ?, custom1 = ? WHERE addons = ?");
    if (!$stmt) {
        echo "DEBUG FAIL: فشل تحضير الاستعلام (prepare). خطأ MySQL: " . $mysqli->error . "\n";
        exit;
    }
    echo "DEBUG STEP 10: تم تحضير الاستعلام بنجاح\n";

    $price_str = (string) $price;
    $stmt->bind_param('iss', $access, $price_str, $addon_name);

    if ($stmt->execute()) {
        echo "DEBUG STEP 11: تم تنفيذ التحديث بنجاح. عدد الصفوف المتأثرة: " . $stmt->affected_rows . "\n";
        if (function_exists('redisDeleteObject')) {
            @redisDeleteObject('avatar_frame:list');
        }
        echo "DEBUG RESULT: 1 (نجح)\n";
    } else {
        echo "DEBUG FAIL: فشل تنفيذ الاستعلام. خطأ MySQL: " . $stmt->error . "\n";
    }
    exit;
}

echo "DEBUG FAIL: save_settings غير موجود بالـ POST، وصلنا لنهاية الملف بدون تنفيذ شي.\n";
