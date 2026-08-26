<?php
if (!defined('BOOM')) { define('BOOM', true); }

/* ==================== ثوابت عامة ==================== */
define('STORIES_ADDON_URL', '/addons/stories/');
define('STORIES_UPLOAD_DIR', dirname(__DIR__, 3) . '/uploads/stories/');
define('STORIES_UPLOAD_URL', '/uploads/stories/');
define('STORIES_MAX_FILE_SIZE', 20 * 1024 * 1024); // 20MB
define('STORIES_ALLOWED_IMAGE', array('jpg', 'jpeg', 'png', 'gif', 'webp'));
define('STORIES_ALLOWED_VIDEO', array('mp4', 'mov', 'webm'));
define('STORIES_DURATION_HOURS', 24);
define('STORIES_MAX_TEXT_LENGTH', 300);
define('STORIES_MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB (أكبر من الصور لأن الفيديو أثقل)
define('STORIES_MAX_VIDEO_SECONDS', 60); // أقصى مدة فيديو مقبولة (ثانية)

/* ==================== ربط حقيقي بجدول الأعضاء boom_users ==================== */
define('STORIES_USERS_TABLE', 'boom_users');
define('STORIES_USER_ID_COL', 'user_id');
define('STORIES_USER_NAME_COL', 'user_name');
define('STORIES_USER_AVATAR_COL', 'user_tumb');

/* ==================== الجلسة الحقيقية (من config_addons.php وليس $_SESSION) ==================== */
function stories_current_user_id()
{
    global $data;
    return isset($data['user_id']) ? (int) $data['user_id'] : 0;
}

function stories_is_logged_in()
{
    return stories_current_user_id() > 0;
}

/* ==================== مسار صورة العضو الحقيقي ==================== */
function stories_avatar_url($filename)
{
    $filename = $filename ?: 'default_avatar.png';
    if (function_exists('myAvatar')) {
        return myAvatar($filename);
    }
    return '/default_images/avatar/' . $filename;
}

/* ==================== بيانات عضو من boom_users ==================== */
function stories_get_user($user_id)
{
    global $mysqli;
    $user_id = (int) $user_id;
    $stmt = $mysqli->prepare('SELECT `' . STORIES_USER_ID_COL . '` AS id, `' . STORIES_USER_NAME_COL . '` AS username, `' . STORIES_USER_AVATAR_COL . '` AS avatar_raw FROM `' . STORIES_USERS_TABLE . '` WHERE `' . STORIES_USER_ID_COL . '` = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return null;
    }
    $row['avatar'] = stories_avatar_url($row['avatar_raw']);
    unset($row['avatar_raw']);
    return $row;
}

/* ==================== تنظيف نص بسيط ==================== */
function stories_clean($str)
{
    return htmlspecialchars(trim((string) $str), ENT_QUOTES, 'UTF-8');
}
