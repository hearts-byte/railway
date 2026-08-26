<?php
/**
 * =========================================================
 *  Stories Addon - AJAX Action Router
 *  نقطة استقبال جميع طلبات الـ AJAX الخاصة بالإضافة
 * =========================================================
 *  addons/stories/system/action.php?do=bar
 *  addons/stories/system/action.php?do=create   (POST)
 *  addons/stories/system/action.php?do=feed&user_id=123
 *  addons/stories/system/action.php?do=view&story_id=55
 *  addons/stories/system/action.php?do=viewers&story_id=55
 *  addons/stories/system/action.php?do=react    (POST)
 *  addons/stories/system/action.php?do=delete   (POST)
 *  addons/stories/system/action.php?do=set_access (POST, admin only)
 *
 *  كل الطلبات لازم تُرسل معها token = utk (نفس متغير الجافاسكربت
 *  المستخدم بباقي الإضافات) عشان تعدي فحص checkToken() بالكور.
 */

$load_addons = 'stories';
require_once('../../../system/config_addons.php');
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/upload_handler.php';

header('Content-Type: application/json; charset=utf-8');

function stories_json($payload)
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

/* ==================== حفظ إعدادات الأدمن (الرتبة المسموحة) ==================== */
if ($do === 'set_access') {
    if (!canManageAddons()) {
        stories_json(array('success' => false));
    }
    $rank = (int) ($_POST['set_addon_access'] ?? 0);
    $mysqli->query("UPDATE boom_addons SET addons_access = '$rank' WHERE addons = 'stories'");
    stories_json(1);
}

if (!stories_is_logged_in()) {
    stories_json(array('success' => false, 'error' => $lang['stories_err_login'] ?? 'يجب تسجيل الدخول أولاً'));
}

$user_id = stories_current_user_id();
$db = $mysqli;

try {
switch ($do) {

    /* ==================== نشر ستوري جديد ==================== */
    case 'create': {
        if (!boomAllow($addons['addons_access'] ?? 0)) {
            stories_json(array('success' => false, 'error' => 'ما تملك صلاحية نشر ستوري'));
        }
        $type = isset($_POST['type']) ? $_POST['type'] : 'text';
        if (!in_array($type, array('text', 'image', 'video'), true)) {
            stories_json(array('success' => false, 'error' => 'نوع الستوري غير صالح'));
        }

        $bg_color = stories_clean($_POST['bg_color'] ?? '#6c5ce7');
        $text_color = stories_clean($_POST['text_color'] ?? '#ffffff');
        $content = '';

        if ($type === 'text') {
            $text = trim($_POST['text'] ?? '');
            if ($text === '') {
                stories_json(array('success' => false, 'error' => $lang['stories_err_empty_text'] ?? 'لا يمكن نشر ستوري نصية فارغة'));
            }
            if (mb_strlen($text) > STORIES_MAX_TEXT_LENGTH) {
                stories_json(array('success' => false, 'error' => 'النص طويل جداً'));
            }
            $content = stories_clean($text);
        } else {
            if (!isset($_FILES['media'])) {
                stories_json(array('success' => false, 'error' => $lang['stories_err_no_file'] ?? 'لم يتم إرفاق أي ملف'));
            }
            $upload = stories_handle_upload($_FILES['media']);
            if (!$upload['success']) {
                stories_json(array('success' => false, 'error' => $upload['error']));
            }
            $type = $upload['type'];
            $content = $upload['path'];
        }

        $hours = STORIES_DURATION_HOURS;
        $gold_cost = 0; // نظام الذهب أُلغي؛ العمود متبقٍّ بقاعدة البيانات لأسباب توافقية فقط
        $stmt = $db->prepare('INSERT INTO cody_stories (user_id, type, content, bg_color, text_color, gold_cost, created_at, expires_at)
                               VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))');
        $stmt->bind_param('isssiii', $user_id, $type, $content, $bg_color, $text_color, $gold_cost, $hours);
        $stmt->execute();
        $story_id = $stmt->insert_id;
        $stmt->close();

        stories_json(array('success' => true, 'story_id' => $story_id));
    }

    /* ==================== بياناتي أنا (لبناء زر "إضافتك" بالجافاسكربت) ==================== */
    case 'me': {
        $me = stories_get_user($user_id);
        stories_json(array(
            'success' => true,
            'user_id' => $user_id,
            'username' => $me['username'] ?? '',
            'avatar' => $me['avatar'] ?? stories_avatar_url(''),
        ));
    }

    /* ==================== شريط الستوريات العلوي ==================== */
    case 'bar': {
        $sql = 'SELECT s.user_id,
                       u.`' . STORIES_USER_NAME_COL . '` AS username,
                       u.`' . STORIES_USER_AVATAR_COL . '` AS avatar_raw,
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
            $row['avatar'] = stories_avatar_url($row['avatar_raw']);
            unset($row['avatar_raw']);
            $row['has_new'] = ((int) $row['unseen_count']) > 0;
            $row['is_me'] = ((int) $row['user_id'] === $user_id);
            $list[] = $row;
        }
        $stmt->close();

        stories_json(array('success' => true, 'users' => $list));
    }

    /* ==================== ستوريات مستخدم معيّن ==================== */
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

    /* ==================== قائمة المشاهدين ==================== */
    case 'viewers': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);

        $check = $db->prepare('SELECT user_id FROM cody_stories WHERE id = ?');
        $check->bind_param('i', $story_id);
        $check->execute();
        $owner_row = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$owner_row || (int) $owner_row['user_id'] !== $user_id) {
            stories_json(array('success' => false, 'error' => $lang['stories_err_not_owner'] ?? 'هذه البيانات متاحة لصاحب الستوري فقط'));
        }

        $stmt = $db->prepare('SELECT v.viewer_id, v.viewed_at,
                                     u.`' . STORIES_USER_NAME_COL . '` AS username,
                                     u.`' . STORIES_USER_AVATAR_COL . '` AS avatar_raw,
                                     r.reaction
                               FROM cody_stories_views v
                               JOIN `' . STORIES_USERS_TABLE . '` u ON u.`' . STORIES_USER_ID_COL . '` = v.viewer_id
                               LEFT JOIN (
                                    SELECT story_id, user_id, content AS reaction
                                    FROM cody_stories_reactions r1
                                    WHERE type = "emoji" AND created_at = (
                                        SELECT MAX(r2.created_at) FROM cody_stories_reactions r2
                                        WHERE r2.story_id = r1.story_id AND r2.user_id = r1.user_id AND r2.type = "emoji"
                                    )
                               ) r ON r.story_id = v.story_id AND r.user_id = v.viewer_id
                               WHERE v.story_id = ?
                               ORDER BY v.viewed_at DESC');
        $stmt->bind_param('i', $story_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $viewers = array();
        while ($row = $res->fetch_assoc()) {
            $row['avatar'] = stories_avatar_url($row['avatar_raw']);
            unset($row['avatar_raw']);
            $viewers[] = $row;
        }
        $stmt->close();

        stories_json(array('success' => true, 'count' => count($viewers), 'viewers' => $viewers));
    }

    /* ==================== التفاعل: إيموجي فقط (بدون رد نصي، وبدون إرسال أي رسالة خاصة) ==================== */
    case 'react': {
        $story_id = (int) ($_REQUEST['story_id'] ?? 0);
        $type = 'emoji';
        $content = trim($_REQUEST['content'] ?? '');

        if ($story_id <= 0 || $content === '') {
            stories_json(array('success' => false, 'error' => 'بيانات ناقصة'));
        }
        if (mb_strlen($content) > 20) {
            $content = mb_substr($content, 0, 20);
        }
        $content_clean = stories_clean($content);

        $check = $db->prepare('SELECT user_id FROM cody_stories WHERE id = ?');
        $check->bind_param('i', $story_id);
        $check->execute();
        $owner_row = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$owner_row) {
            stories_json(array('success' => false, 'error' => $lang['stories_err_expired'] ?? 'الستوري غير موجودة أو انتهت'));
        }

        // التفاعل يُسجَّل فقط ليظهر لصاحب الستوري بجانب المشاهد (بقائمة المشاهدين)،
        // بدون إرسال أي رسالة خاصة بالدردشة
        $stmt = $db->prepare('INSERT INTO cody_stories_reactions (story_id, user_id, type, content, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiss', $story_id, $user_id, $type, $content_clean);
        $stmt->execute();
        $stmt->close();

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
} catch (\Throwable $e) {
    // بدل ما يرجع رد فاضي (يسبب كسر res.json() بالواجهة ويخلي الستوري ما تظهر بدون أي سبب واضح)
    // نرجع خطأ JSON واضح فيه رسالة قاعدة البيانات/PHP الحقيقية عشان يسهل تشخيصه
    stories_json(array('success' => false, 'error' => 'خطأ داخلي: ' . $e->getMessage()));
}
