<?php
/**
 * =========================================================
 *  Stories Addon - AJAX Action Router
 *  نقطة استقبال جميع طلبات الـ AJAX الخاصة بالإضافة
 * =========================================================
 *  استدعاء نمطي من الواجهة (script.js):
 *  addons/stories/system/action.php?do=bar
 *  addons/stories/system/action.php?do=create   (POST)
 *  addons/stories/system/action.php?do=feed&user_id=123
 *  addons/stories/system/action.php?do=view&story_id=55
 *  addons/stories/system/action.php?do=viewers&story_id=55
 *  addons/stories/system/action.php?do=react    (POST)
 *  addons/stories/system/action.php?do=delete   (POST)
 */

define('IN_SCRIPT', true);

// نحمّل جلسة نواة CodyChat عشان يتوفر $data['user_id'] (المستخدم الحالي).
// ملف config_session.php يستخدم مسارات نسبية (require("database.php"))
// فلازم نغيّر مجلد العمل مؤقتاً لمجلد system/ الأصلي قبل تحميله.
if (!isset($GLOBALS['data']['user_id'])) {
    $__stories_cwd = getcwd();
    chdir(__DIR__ . '/../../../system');
    require_once 'config_session.php';
    chdir($__stories_cwd);
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/upload_handler.php';

header('Content-Type: application/json; charset=utf-8');

function stories_json($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

if (!stories_is_logged_in()) {
    stories_json(array('success' => false, 'error' => 'يجب تسجيل الدخول أولاً'));
}

$user_id = stories_current_user_id();
$db = stories_db();

switch ($do) {

    /* ==================== نشر ستوري جديد ==================== */
    case 'create': {
        $type = isset($_POST['type']) ? $_POST['type'] : 'text';
        if (!in_array($type, array('text', 'image', 'video'), true)) {
            stories_json(array('success' => false, 'error' => 'نوع الستوري غير صالح'));
        }

        $settings = stories_settings();
        $gold_cost = 0;
        if ($settings['gold_enabled']) {
            $gold_cost = max(0, (int) ($_POST['gold_cost'] ?? $settings['gold_cost']));
        }

        $bg_color = stories_clean($_POST['bg_color'] ?? '#6c5ce7');
        $text_color = stories_clean($_POST['text_color'] ?? '#ffffff');
        $content = '';

        if ($type === 'text') {
            $text = trim($_POST['text'] ?? '');
            if ($text === '') {
                stories_json(array('success' => false, 'error' => 'لا يمكن نشر ستوري نصية فارغة'));
            }
            if (mb_strlen($text) > $settings['max_text_length']) {
                stories_json(array('success' => false, 'error' => 'النص طويل جداً'));
            }
            $content = stories_clean($text);
        } else {
            if (!isset($_FILES['media'])) {
                stories_json(array('success' => false, 'error' => 'لم يتم إرفاق أي ملف'));
            }
            $upload = stories_handle_upload($_FILES['media']);
            if (!$upload['success']) {
                stories_json(array('success' => false, 'error' => $upload['error']));
            }
            $type = $upload['type']; // تصحيح تلقائي حسب فحص الملف الحقيقي
            $content = $upload['path'];
        }

        if ($gold_cost > 0 && !stories_charge_gold($user_id, $gold_cost)) {
            stories_json(array('success' => false, 'error' => 'رصيد الذهب غير كافٍ لنشر هذه الستوري'));
        }

        $hours = $settings['duration_hours'];
        $stmt = $db->prepare('INSERT INTO cody_stories (user_id, type, content, bg_color, text_color, gold_cost, created_at, expires_at)
                               VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))');
        $stmt->bind_param('isssiii', $user_id, $type, $content, $bg_color, $text_color, $gold_cost, $hours);
        $stmt->execute();
        $story_id = $stmt->insert_id;
        $stmt->close();

        stories_json(array('success' => true, 'story_id' => $story_id));
    }

    /* ==================== شريط الستوريات العلوي ==================== */
    case 'bar': {
        $sql = 'SELECT s.user_id,
                       u.`' . STORIES_USER_NAME_COL . '` AS username,
                       u.`' . STORIES_USER_AVATAR_COL . '` AS avatar,
                       MAX(s.created_at) AS last_story,
                       SUM(CASE WHEN v.viewer_id IS NULL THEN 1 ELSE 0 END) AS unseen_count
                FROM cody_stories s
                JOIN `' . STORIES_USERS_TABLE . '` u ON u.`' . STORIES_USER_ID_COL . '` = s.user_id
                LEFT JOIN cody_stories_views v ON v.story_id = s.id AND v.viewer_id = ?
                WHERE s.status = 1 AND s.expires_at > NOW()
                GROUP BY s.user_id, u.`' . STORIES_USER_NAME_COL . '`, u.`' . STORIES_USER_AVATAR_COL . '`
                ORDER BY unseen_count DESC, last_story DESC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $list = array();
        while ($row = $res->fetch_assoc()) {
            $row['has_new'] = ((int) $row['unseen_count']) > 0;
            $row['is_me'] = ((int) $row['user_id'] === $user_id);
            $list[] = $row;
        }
        $stmt->close();

        stories_json(array('success' => true, 'users' => $list));
    }

    /* ==================== ستوريات مستخدم معيّن (للعرض بملء الشاشة) ==================== */
    case 'feed': {
        $target_user = (int) ($_REQUEST['user_id'] ?? 0);
        if (!$target_user) {
            stories_json(array('success' => false, 'error' => 'مستخدم غير صالح'));
        }

        $stmt = $db->prepare('SELECT id, user_id, type, content, bg_color, text_color, views, created_at, expires_at
                               FROM cody_stories
                               WHERE user_id = ? AND status = 1 AND expires_at > NOW()
                               ORDER BY created_at ASC');
        $stmt->bind_param('i', $target_user);
        $stmt->execute();
        $res = $stmt->get_result();

        $stories = array();
        while ($row = $res->fetch_assoc()) {
            $stories[] = $row;
        }
        $stmt->close();

        $owner = stories_get_user($target_user);
        stories_json(array('success' => true, 'owner' => $owner, 'stories' => $stories));
    }

    /* ==================== تسجيل مشاهدة ==================== */
    case 'view': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);
        if (!$story_id) {
            stories_json(array('success' => false));
        }

        $stmt = $db->prepare('INSERT IGNORE INTO cody_stories_views (story_id, viewer_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $story_id, $user_id);
        $stmt->execute();
        $is_new_view = $stmt->affected_rows > 0;
        $stmt->close();

        if ($is_new_view) {
            $upd = $db->prepare('UPDATE cody_stories SET views = views + 1 WHERE id = ?');
            $upd->bind_param('i', $story_id);
            $upd->execute();
            $upd->close();
        }

        stories_json(array('success' => true));
    }

    /* ==================== قائمة المشاهدين (لصاحب الستوري فقط) ==================== */
    case 'viewers': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);

        $check = $db->prepare('SELECT user_id FROM cody_stories WHERE id = ?');
        $check->bind_param('i', $story_id);
        $check->execute();
        $owner_row = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$owner_row || (int) $owner_row['user_id'] !== $user_id) {
            stories_json(array('success' => false, 'error' => 'هذه البيانات متاحة لصاحب الستوري فقط'));
        }

        $stmt = $db->prepare('SELECT v.viewer_id, v.viewed_at,
                                     u.`' . STORIES_USER_NAME_COL . '` AS username,
                                     u.`' . STORIES_USER_AVATAR_COL . '` AS avatar
                               FROM cody_stories_views v
                               JOIN `' . STORIES_USERS_TABLE . '` u ON u.`' . STORIES_USER_ID_COL . '` = v.viewer_id
                               WHERE v.story_id = ?
                               ORDER BY v.viewed_at DESC');
        $stmt->bind_param('i', $story_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $viewers = array();
        while ($row = $res->fetch_assoc()) {
            $viewers[] = $row;
        }
        $stmt->close();

        stories_json(array('success' => true, 'count' => count($viewers), 'viewers' => $viewers));
    }

    /* ==================== التفاعل: إيموجي أو رد خاص ==================== */
    case 'react': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);
        $type = ($_REQUEST['type'] ?? 'emoji') === 'message' ? 'message' : 'emoji';
        $content = trim($_REQUEST['content'] ?? '');

        if ($story_id <= 0 || $content === '') {
            stories_json(array('success' => false, 'error' => 'بيانات ناقصة'));
        }
        if (mb_strlen($content) > 255) {
            $content = mb_substr($content, 0, 255);
        }
        $content_clean = stories_clean($content);

        $check = $db->prepare('SELECT user_id FROM cody_stories WHERE id = ?');
        $check->bind_param('i', $story_id);
        $check->execute();
        $owner_row = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$owner_row) {
            stories_json(array('success' => false, 'error' => 'الستوري غير موجودة أو انتهت'));
        }

        $stmt = $db->prepare('INSERT INTO cody_stories_reactions (story_id, user_id, type, content, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiss', $story_id, $user_id, $type, $content_clean);
        $stmt->execute();
        $stmt->close();

        // إرسال الرد كرسالة خاصة إلى صاحب الستوري (اربطها بنظامك الفعلي داخل config.php)
        stories_send_private_reply($user_id, (int) $owner_row['user_id'], $content_clean);

        stories_json(array('success' => true));
    }

    /* ==================== حذف ستوري (لصاحبها فقط) ==================== */
    case 'delete': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);

        $stmt = $db->prepare('UPDATE cody_stories SET status = 0 WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $story_id, $user_id);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        stories_json(array('success' => $ok));
    }

    default:
        stories_json(array('success' => false, 'error' => 'إجراء غير معروف'));
}
