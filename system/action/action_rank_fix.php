<?php
/**
 * action_rank_fix.php
 * بديل غير مشفّر لعملية change_rank بدلاً من الملف المشفّر action_users.php
 * يُرفع داخل مجلد: system/action/
 */

require __DIR__ . "/../config_session.php";

// وضع تشخيص مؤقت: اجعلها true أثناء الاختبار فقط، ثم أعدها false على الإنتاج
define('RANK_FIX_DEBUG', true);

function rankFixDebug($label, $value = null) {
	if (!RANK_FIX_DEBUG) return;
	error_log("[action_rank_fix] $label => " . json_encode($value));
}

if (!isset($_POST['change_rank'], $_POST['target'])) {
	echo 0;
	exit;
}

// المستخدم غير مسجل دخول أصلاً
if (empty($data) || empty($data['user_id'])) {
	rankFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

$change_rank = (int) $_POST['change_rank'];
$target      = (int) $_POST['target'];
$my_rank     = (int) $data['user_rank'];
$can_rank    = (int) ($setting['can_rank'] ?? 0);

rankFixDebug('input', [
	'my_user_id'   => $data['user_id'],
	'my_rank'      => $my_rank,
	'target'       => $target,
	'change_rank'  => $change_rank,
	'can_rank'     => $can_rank,
]);

// 1) هل مسموح لرتبتي أصلاً باستخدام هذه الميزة؟
if ($my_rank < $can_rank) {
	rankFixDebug('reject_reason', 'boomAllow_failed');
	echo 0;
	exit;
}

// 2) لا يمكن منح رتبة تساوي أو تفوق رتبتي
if ($my_rank <= $change_rank) {
	rankFixDebug('reject_reason', 'change_rank_gte_my_rank');
	echo 0;
	exit;
}

// 3) جلب بيانات الهدف مباشرة من قاعدة البيانات (بدون كاش) لتفادي أي بيانات قديمة
$target_safe = (int) $target;
$result = $mysqli->query("SELECT user_id, user_rank FROM boom_users WHERE user_id = '$target_safe' LIMIT 1");

if (!$result || $result->num_rows === 0) {
	rankFixDebug('reject_reason', 'target_not_found');
	echo 0;
	exit;
}

$user = $result->fetch_assoc();
$target_rank = (int) $user['user_rank'];

rankFixDebug('target_user', $user);

// 4) لا يمكن التعديل على من رتبته تساوي أو تفوق رتبتي
if ($my_rank <= $target_rank) {
	rankFixDebug('reject_reason', 'target_rank_gte_my_rank');
	echo 0;
	exit;
}

// جلب بيانات الهدف الكاملة (مطلوبة لدالة userReset لأنها تعتمد على أعمدة إضافية غير user_rank فقط)
$full_user = userDetails($target_safe);
if (empty($full_user)) {
	rankFixDebug('reject_reason', 'userDetails_failed');
	echo 0;
	exit;
}

// تنفيذ التحديث عبر دالة النظام الأصلية userReset
// هذي الدالة تتكفل بكل شي: تحديث الرتبة، تصفير المزايا الممنوعة على الرتبة الجديدة،
// زيادة user_action (المسؤولة عن تحديث الصفحة تلقائيًا عند الهدف)،
// وإرسال إشعار رفع الرتبة (rank_change) تلقائيًا لو الرتبة الجديدة أعلى
userReset($full_user, $change_rank);

rankFixDebug('success', ['old_rank' => $target_rank, 'new_rank' => $change_rank]);

// تفريغ الكاش إن وُجد (userReset أصلاً يستدعي redisUpdateUser داخليًا، هذا احتياط إضافي فقط)
if (function_exists('redisUpdateUser')) {
	redisUpdateUser($target_safe);
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
	boomConsole('change_rank', [
		'target' => $target_safe,
		'new_rank' => $change_rank,
	]);
}

echo 1;
exit;
