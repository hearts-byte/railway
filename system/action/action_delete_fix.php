<?php
/**
 * action_delete_fix.php
 * بديل غير مشفّر لعملية delete_user_account بدلاً من الملف المشفّر action_users.php
 * يُرفع داخل مجلد: system/action/
 *
 * تحذير: هذا حذف فعلي (DELETE) من جدول boom_users فقط.
 * بيانات المستخدم بجداول أخرى (chat, friends, gifts...) لن تُحذف تلقائيًا.
 */

require __DIR__ . "/../config.php";

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
$result = $mysqli->query("SELECT user_id, user_rank, user_name FROM boom_users WHERE user_id = '$target' LIMIT 1");

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

// تنفيذ الحذف الفعلي
$delete = $mysqli->query("DELETE FROM boom_users WHERE user_id = '{$user['user_id']}' LIMIT 1");

if (!$delete) {
	deleteFixDebug('reject_reason', 'sql_delete_failed: ' . $mysqli->error);
	echo 0;
	exit;
}

if ($mysqli->affected_rows === 0) {
	deleteFixDebug('reject_reason', 'no_rows_deleted');
	echo 0;
	exit;
}

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
