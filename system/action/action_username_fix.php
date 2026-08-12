<?php
/**
 * action_username_fix.php
 * بديل غير مشفّر لعملية تغيير اسم المستخدم من صفحة الحساب الشخصي (edit_username)
 * بدلاً من الملف المشفّر action_profile.php
 * يُرفع داخل مجلد: system/action/
 *
 * أكواد الرد (مطابقة تمامًا لما يتوقعه changeMyUsername في function_logged.js):
 * 1  = نجاح
 * 2  = اسم مستخدم غير صالح (فارغ أو صيغة خاطئة)
 * 3  = اسم المستخدم مستخدم مسبقًا
 * 4  = تجاوز حد عدد مرات تغيير الاسم المسموح بها (غير مُفعّل حاليًا في هذا الملف)
 * 0  = خطأ عام / غير مصرح
 */

require __DIR__ . "/../config_session.php";

define('USERNAME_FIX_DEBUG', true);

function usernameFixDebug($label, $value = null) {
	if (!USERNAME_FIX_DEBUG) return;
	error_log("[action_username_fix] $label => " . json_encode($value));
}

if (!isset($_POST['edit_username'])) {
	echo 0;
	exit;
}

if (empty($data) || empty($data['user_id'])) {
	usernameFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

if (!function_exists('checkToken') || !checkToken()) {
	usernameFixDebug('reject_reason', 'bad_token');
	echo 0;
	exit;
}

$new_name = trim($_POST['new_name'] ?? '');

usernameFixDebug('input', ['new_name' => $new_name, 'user_id' => $data['user_id']]);

if ($new_name === '') {
	usernameFixDebug('reject_reason', 'empty_name');
	echo 2;
	exit;
}

$new_name = escape($new_name);

// لو الاسم الجديد نفس الاسم الحالي، لا داعي لأي تعديل - اعتبرها نجاح
if ($new_name === $data['user_name']) {
	echo 1;
	exit;
}

// 1) التحقق من صيغة اسم المستخدم
if (!function_exists('validName') || !validName($new_name)) {
	usernameFixDebug('reject_reason', 'invalid_username');
	echo 2;
	exit;
}

// 2) التحقق من عدم تكرار اسم المستخدم (بأي عضو غير نفسه)
$check_name = $mysqli->query("SELECT user_id FROM boom_users WHERE user_name = '$new_name' AND user_id != '{$data['user_id']}' LIMIT 1");
if ($check_name && $check_name->num_rows > 0) {
	usernameFixDebug('reject_reason', 'username_taken');
	echo 3;
	exit;
}

// التحديث الفعلي بقاعدة البيانات
$update = $mysqli->query("UPDATE boom_users SET user_name = '$new_name' WHERE user_id = '{$data['user_id']}'");

if (!$update) {
	usernameFixDebug('reject_reason', 'sql_update_failed: ' . $mysqli->error);
	echo 0;
	exit;
}

// تسجيل تغيير الاسم بنفس آلية النظام الأصلية (سجل الدردشة + تاريخ الأسماء)
// تحقق من وجود nameRecord لأن changeNameLog تستدعيها داخليًا؛ تفاديًا لأي Fatal Error مشابه للمشكلة الأصلية
if (function_exists('changeNameLog') && function_exists('nameRecord')) {
	changeNameLog($data, $new_name);
} else {
	usernameFixDebug('note', 'skipped changeNameLog: nameRecord not available');
}

usernameFixDebug('success', ['old_name' => $data['user_name'], 'new_name' => $new_name]);

if (function_exists('redisSetObject')) {
	// تحديث الكاش المحلي لبيانات هذا العضو مباشرة بدل الانتظار
	$data['user_name'] = $new_name;
	redisSetObject('user:' . $data['user_id'], $data);
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

echo 1;
exit;
