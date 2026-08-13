<?php
/**
 * action_delete_fix.php
 * بديل غير مشفّر لعملية delete_user_account بدلاً من الملف المشفّر action_users.php
 * يُرفع داخل مجلد: system/action/
 *
 * يستخدم دالة clearUserData() الأصلية لتنظيف كل الجداول المرتبطة بالعضو قبل حذفه.
 */

require __DIR__ . "/../config_session.php";

// وضع تشخيص مؤقت: اجعلها true أثناء الاختبار فقط، ثم أعدها false على الإنتاج
define('DELETE_FIX_DEBUG', true);

function deleteFixDebug($label, $value = null) {
	if (!DELETE_FIX_DEBUG) return;
	error_log("[action_delete_fix] $label => " . json_encode($value));
}

// الحقل المتوقع إرساله من الواجهة: delete_user_account = target_id
if (!isset($_POST['delete_user_account'])) {
	echo 0;
	exit;
}

if (empty($data) || empty($data['user_id'])) {
	deleteFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

$target   = (int) $_POST['delete_user_account'];
$my_rank  = (int) $data['user_rank'];
$my_id    = (int) $data['user_id'];
$can_del  = (int) ($setting['can_delete'] ?? 0);

deleteFixDebug('input', [
	'my_user_id' => $my_id,
	'my_rank'    => $my_rank,
	'target'     => $target,
	'can_delete' => $can_del,
]);

// منع حذف حسابك نفسك عن طريق الخطأ
if ($target === $my_id) {
	deleteFixDebug('reject_reason', 'cannot_delete_self');
	echo 0;
	exit;
}

// 1) هل مسموح لرتبتي أصلاً بالحذف؟
if ($my_rank < $can_del) {
	deleteFixDebug('reject_reason', 'boomAllow_can_delete_failed');
	echo 0;
	exit;
}

// 2) جلب بيانات الهدف مباشرة من قاعدة البيانات (بدون كاش)
$result = $mysqli->query("SELECT user_id, user_rank, user_name, user_tumb, user_cover FROM boom_users WHERE user_id = '$target' LIMIT 1");

if (!$result || $result->num_rows === 0) {
	deleteFixDebug('reject_reason', 'target_not_found');
	echo 0;
	exit;
}

$user = $result->fetch_assoc();
$target_rank = (int) $user['user_rank'];

deleteFixDebug('target_user', $user);

// 3) لا يمكن حذف من رتبته تساوي أو تفوق رتبتي
if ($my_rank <= $target_rank) {
	deleteFixDebug('reject_reason', 'target_rank_gte_my_rank');
	echo 0;
	exit;
}

// تنفيذ الحذف الفعلي عبر دالة النظام الأصلية clearUserData
// هذي الدالة تنظف كل الجداول المرتبطة بالعضو (الدردشة، الخاص، الأصدقاء، الإشعارات،
// المستوى/XP، الشارات، الصورة والغلاف، إلخ) قبل حذف الصف الأساسي من boom_users
if (!function_exists('clearUserData')) {
	deleteFixDebug('reject_reason', 'clearUserData_function_missing');
	echo 0;
	exit;
}

clearUserData($user);

deleteFixDebug('success', true);

// تفريغ الكاش إن وُجد
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
	boomConsole('delete_account', [
		'target' => $target,
		'target_name' => $user['user_name'],
	]);
}

echo 1;
exit;
