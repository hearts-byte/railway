<?php
/**
 * action_editname_fix.php
 * بديل غير مشفّر لعملية تعديل اسم عضو من طرف الإدارة (target_id + user_new_name)
 * بدلاً من الملف المشفّر action_users.php
 * يُرفع داخل مجلد: system/action/
 *
 * أكواد الرد (متوافقة مع الواجهة الحالية بدون تعديل JS):
 * 1 = نجاح
 * 2 = اسم غير صالح
 * 3 = الاسم مستخدم مسبقًا
 * 0 = خطأ عام / غير مصرح
 */

require __DIR__ . "/../config.php";

// وضع تشخيص مؤقت: اجعلها true أثناء الاختبار فقط، ثم أعدها false على الإنتاج
define('EDITNAME_FIX_DEBUG', true);

function editNameFixDebug($label, $value = null) {
	if (!EDITNAME_FIX_DEBUG) return;
	error_log("[action_editname_fix] $label => " . json_encode($value));
}

if (!isset($_POST['target_id'], $_POST['user_new_name'])) {
	echo 0;
	exit;
}

if (empty($data) || empty($data['user_id'])) {
	editNameFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

$target      = (int) $_POST['target_id'];
$new_name    = escape($_POST['user_new_name']);
$my_rank     = (int) $data['user_rank'];
$my_id       = (int) $data['user_id'];
$can_modname = (int) ($setting['can_modname'] ?? 0);

editNameFixDebug('input', [
	'my_user_id'  => $my_id,
	'my_rank'     => $my_rank,
	'target'      => $target,
	'new_name'    => $new_name,
	'can_modname' => $can_modname,
]);

// 1) هل رتبتي مسموح لها أصلاً بتعديل أسماء الأعضاء؟
if ($my_rank < $can_modname) {
	editNameFixDebug('reject_reason', 'boomAllow_can_modname_failed');
	echo 0;
	exit;
}

// 2) جلب بيانات الهدف مباشرة من قاعدة البيانات (بدون كاش)
$result = $mysqli->query("SELECT user_id, user_rank, user_name, user_roomid FROM boom_users WHERE user_id = '$target' LIMIT 1");

if (!$result || $result->num_rows === 0) {
	editNameFixDebug('reject_reason', 'target_not_found');
	echo 0;
	exit;
}

$user = $result->fetch_assoc();
$target_rank = (int) $user['user_rank'];

editNameFixDebug('target_user', $user);

// 3) لا يمكن تعديل اسم من رتبته تساوي أو تفوق رتبتي (نفس منطق باقي العمليات) — إلا لو كنت نفسي المستهدف
if ($target !== $my_id && $my_rank <= $target_rank) {
	editNameFixDebug('reject_reason', 'target_rank_gte_my_rank');
	echo 0;
	exit;
}

// إذا الاسم نفسه بدون تغيير حقيقي
if (function_exists('boomSame') && boomSame($new_name, $user['user_name'])) {
	// اعتبره نفس الاسم = نجاح بدون تغيير فعلي، أو ارفضه كـ "مستخدم مسبقًا" حسب رغبتك
	if ($new_name === $user['user_name']) {
		editNameFixDebug('success', 'same_name_no_change');
		echo 1;
		exit;
	}
}

// 4) التحقق من صلاحية تنسيق الاسم
if (!function_exists('validName') || !validName($new_name)) {
	editNameFixDebug('reject_reason', 'invalid_name_format');
	echo 2;
	exit;
}

// 5) التحقق من عدم استخدام الاسم من طرف عضو آخر
if (!function_exists('boomUsername') || !boomUsername($new_name)) {
	editNameFixDebug('reject_reason', 'username_taken');
	echo 3;
	exit;
}

// تنفيذ التحديث
$update = $mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '{$user['user_id']}' LIMIT 1");

if (!$update) {
	editNameFixDebug('reject_reason', 'sql_update_failed: ' . $mysqli->error);
	echo 0;
	exit;
}

editNameFixDebug('success', true);

// سجل التغيير + تفريغ الكاش
if (function_exists('changeNameLog')) {
	changeNameLog($user, $new_name);
}
if (function_exists('redisUpdateUser')) {
	redisUpdateUser($target);
}
if (function_exists('redisFlushAll')) {
	redisFlushAll();
}
if (function_exists('boomCacheUpdate')) {
	boomCacheUpdate();
}
if (function_exists('opcache_reset')) {
	opcache_reset();
}
if (function_exists('boomConsole')) {
	boomConsole('change_name_admin', [
		'target'   => $target,
		'old_name' => $user['user_name'],
		'new_name' => $new_name,
	]);
}

echo 1;
exit;
