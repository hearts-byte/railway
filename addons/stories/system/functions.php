<?php
/**
 * =========================================================
 *  Stories Addon - Configuration & Core Adapter
 *  إضافة الستوريات - ملف الإعدادات وربط الأنظمة الأساسية
 * =========================================================
 *
 *  هذا الملف هو نقطة الربط بين الإضافة ونواة سكربت CodyChat.
 *  بما أن أسماء دوال الاتصال بقاعدة البيانات ومتغيرات الجلسة قد تختلف
 *  حسب نسخة السكربت لديك، تم فصل كل نقاط الربط هنا بوضوح لتسهيل التعديل.
 *
 *  === تحقق من هذه النقاط قبل التشغيل ===
 *  1) مسار تحميل نواة السكربت في قسم "Bootstrap" أدناه.
 *  2) طريقة الوصول لاتصال قاعدة البيانات (mysqli) في stories_db().
 *  3) اسم مفتاح الجلسة الخاص بمعرف المستخدم في stories_current_user_id().
 *  4) أسماء جدول/أعمدة الأعضاء (المعرف، الاسم، الصورة، الذهب) بالأسفل.
 *  5) دالة إرسال الرسائل الخاصة الحقيقية في stories_send_private_reply().
 */

if (!defined('IN_SCRIPT')) { define('IN_SCRIPT', true); }

/* -------------------- Bootstrap: تحميل نواة السكربت -------------------- */
$stories_root = dirname(__DIR__, 3); // addons/stories/system -> جذر السكربت

/*
   عدّل السطر التالي ليطابق ملف الإعدادات/الاتصال الفعلي في سكربتك،
   ثم فعّل require_once (احذف التعليق) بعد التأكد من المسار الصحيح.
   لا يتم تفعيله تلقائياً لتفادي تعارض أو تكرار في تعريف الثوابت/الجلسة.
*/
// require_once $stories_root . '/system/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

/* -------------------- إعدادات ثابتة (غير قابلة للتعديل من لوحة التحكم) -------------------- */
define('STORIES_ADDON_URL', '/addons/stories/');
define('STORIES_UPLOAD_DIR', $stories_root . '/uploads/stories/');
define('STORIES_UPLOAD_URL', '/uploads/stories/');
define('STORIES_MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 ميجابايت
define('STORIES_ALLOWED_IMAGE', array('jpg', 'jpeg', 'png', 'gif', 'webp'));
define('STORIES_ALLOWED_VIDEO', array('mp4', 'mov', 'webm'));

/* -------------------- إعدادات قابلة للتعديل من لوحة التحكم (تُقرأ من الجدول) -------------------- */
function stories_settings()
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $defaults = array(
        'duration_hours'   => 24,
        'gold_enabled'     => 1,
        'gold_cost'        => 0,
        'max_text_length'  => 300,
        'access'           => 0,
    );

    $db = stories_db();
    $db->query("CREATE TABLE IF NOT EXISTS `cody_stories_settings` (
        `k` VARCHAR(50) NOT NULL,
        `v` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`k`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $res = $db->query("SELECT k, v FROM cody_stories_settings");
    $stored = array();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $stored[$row['k']] = $row['v'];
        }
    }

    $settings = array_merge($defaults, $stored);
    $settings['duration_hours']  = (int) $settings['duration_hours'];
    $settings['gold_enabled']    = (int) $settings['gold_enabled'];
    $settings['gold_cost']       = (int) $settings['gold_cost'];
    $settings['max_text_length'] = (int) $settings['max_text_length'];
    $settings['access']          = (int) $settings['access'];

    return $settings;
}

function stories_save_settings($input)
{
    $db = stories_db();
    $db->query("CREATE TABLE IF NOT EXISTS `cody_stories_settings` (
        `k` VARCHAR(50) NOT NULL,
        `v` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`k`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $allowed = array('duration_hours', 'gold_enabled', 'gold_cost', 'max_text_length', 'access');
    $stmt = $db->prepare("INSERT INTO cody_stories_settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)");
    foreach ($allowed as $key) {
        if (!isset($input[$key])) {
            continue;
        }
        $val = (string) (int) $input[$key];
        $stmt->bind_param('ss', $key, $val);
        $stmt->execute();
    }
    $stmt->close();
    return true;
}

/* -------------------- أسماء جدول وأعمدة الأعضاء لديك -------------------- */
/* عدّل هذه القيم فقط لتطابق بنية جدول الأعضاء الحالي في قاعدة بياناتك */
define('STORIES_USERS_TABLE', 'users');
define('STORIES_USER_ID_COL', 'id');
define('STORIES_USER_NAME_COL', 'username');
define('STORIES_USER_AVATAR_COL', 'avatar');
define('STORIES_USER_GOLD_COL', 'gold');

/* -------------------- الاتصال بقاعدة البيانات -------------------- */
function stories_db()
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }

    // الحالة 1: يوجد اتصال mysqli عام تم إنشاؤه من قبل نواة السكربت
    if (isset($GLOBALS['con']) && $GLOBALS['con'] instanceof mysqli) {
        return $conn = $GLOBALS['con'];
    }
    if (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) {
        return $conn = $GLOBALS['mysqli'];
    }
    if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof mysqli) {
        return $conn = $GLOBALS['db'];
    }

    // الحالة 2: اتصال احتياطي مستقل - عدّل البيانات التالية إذا لم يوجد اتصال عام
    $DB_HOST = 'localhost';
    $DB_USER = 'db_user';
    $DB_PASS = 'db_pass';
    $DB_NAME = 'db_name';

    $conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die('Stories Addon DB Error: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/* -------------------- المستخدم الحالي -------------------- */
function stories_current_user_id()
{
    // غيّر/أضف اسم مفتاح الجلسة الفعلي في سكربتك إذا كان مختلفاً
    foreach (array('user_id', 'id', 'uid') as $key) {
        if (!empty($_SESSION[$key])) {
            return (int) $_SESSION[$key];
        }
    }
    return 0;
}

function stories_is_logged_in()
{
    return stories_current_user_id() > 0;
}

/* -------------------- معلومات المستخدم -------------------- */
function stories_get_user($user_id)
{
    $db = stories_db();
    $table = STORIES_USERS_TABLE;
    $idCol = STORIES_USER_ID_COL;
    $nameCol = STORIES_USER_NAME_COL;
    $avatarCol = STORIES_USER_AVATAR_COL;

    $stmt = $db->prepare("SELECT `$idCol` AS id, `$nameCol` AS username, `$avatarCol` AS avatar FROM `$table` WHERE `$idCol` = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/* -------------------- نظام الذهب -------------------- */
function stories_get_user_gold($user_id)
{
    $db = stories_db();
    $table = STORIES_USERS_TABLE;
    $idCol = STORIES_USER_ID_COL;
    $goldCol = STORIES_USER_GOLD_COL;

    $stmt = $db->prepare("SELECT `$goldCol` AS gold FROM `$table` WHERE `$idCol` = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row['gold'] : 0;
}

// خصم الذهب بشكل آمن (Atomic) - يمنع الخصم لو الرصيد أصبح غير كافٍ لحظة التنفيذ
function stories_charge_gold($user_id, $amount)
{
    if ($amount <= 0) {
        return true; // نشر مجاني
    }
    $db = stories_db();
    $table = STORIES_USERS_TABLE;
    $idCol = STORIES_USER_ID_COL;
    $goldCol = STORIES_USER_GOLD_COL;

    $stmt = $db->prepare("UPDATE `$table` SET `$goldCol` = `$goldCol` - ? WHERE `$idCol` = ? AND `$goldCol` >= ?");
    $stmt->bind_param('iii', $amount, $user_id, $amount);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

/* -------------------- إرسال رد كرسالة خاصة -------------------- */
/* اربط هذه الدالة بنظام الرسائل الخاصة الفعلي لديك بدلاً من المثال أدناه */
function stories_send_private_reply($from_user_id, $to_user_id, $message_text)
{
    $db = stories_db();
    $check = $db->query("SHOW TABLES LIKE 'privates'");
    if ($check && $check->num_rows > 0) {
        $stmt = $db->prepare('INSERT INTO privates (from_id, to_id, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $from_user_id, $to_user_id, $message_text);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false; // لم يتم العثور على جدول الرسائل الافتراضي - اربطه يدوياً هنا
}

/* -------------------- تنظيف عام للمدخلات -------------------- */
function stories_clean($str)
{
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
