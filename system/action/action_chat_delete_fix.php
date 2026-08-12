<?php
/**
 * action_chat_delete_fix.php
 * بديل غير مشفّر لعملية حذف رسالة من الدردشة العامة (del_post)
 * بدلاً من الملف المشفّر action_chat.php
 * يُرفع داخل مجلد: system/action/
 *
 * أكواد الرد:
 * 1  = نجاح، تم الحذف فعليًا من قاعدة البيانات
 * 2  = الرسالة غير موجودة أصلاً (محذوفة مسبقًا)
 * 0  = خطأ عام / غير مصرح بالحذف
 */

require __DIR__ . "/../config_session.php";

define('CHATDEL_FIX_DEBUG', true);

function chatDelFixDebug($label, $value = null) {
	if (!CHATDEL_FIX_DEBUG) return;
	error_log("[action_chat_delete_fix] $label => " . json_encode($value));
}

if (!isset($_POST['del_post'])) {
	echo 0;
	exit;
}

if (empty($data) || empty($data['user_id'])) {
	chatDelFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

$post_id = (int) $_POST['del_post'];

if ($post_id <= 0) {
	chatDelFixDebug('reject_reason', 'invalid_post_id');
	echo 0;
	exit;
}

// اجلب الرسالة أولاً للتأكد من وجودها ومعرفة صاحبها
$get_post = $mysqli->query("SELECT post_id, user_id, post_roomid FROM boom_chat WHERE post_id = '$post_id' LIMIT 1");
if (!$get_post || $get_post->num_rows === 0) {
	chatDelFixDebug('reject_reason', 'post_not_found');
	echo 2;
	exit;
}
$post = $get_post->fetch_assoc();

// تحقق من الصلاحية: صاحب الرسالة نفسه أو إدارة عندها صلاحية حذف المحتوى/سجلات الغرفة
$allowed = false;

if (function_exists('canDeleteSelfLog') && canDeleteSelfLog($post)) {
	$allowed = true;
}
if (!$allowed && function_exists('canDeleteContent') && canDeleteContent()) {
	$allowed = true;
}
if (!$allowed && function_exists('canDeleteRoomLog') && canDeleteRoomLog()) {
	$allowed = true;
}

if (!$allowed) {
	chatDelFixDebug('reject_reason', 'not_allowed');
	echo 0;
	exit;
}

// الحذف الفعلي من قاعدة البيانات
$delete = $mysqli->query("DELETE FROM boom_chat WHERE post_id = '$post_id' LIMIT 1");

if (!$delete) {
	chatDelFixDebug('reject_reason', 'sql_delete_failed: ' . $mysqli->error);
	echo 0;
	exit;
}

// تنظيف أي اقتباسات مرتبطة بهذه الرسالة (رسائل اقتبست منها) عشان ما تضل يتيمة
$mysqli->query("UPDATE boom_chat SET qpost = 0, quser = 0 WHERE qpost = '$post_id'");

chatDelFixDebug('success', ['deleted_post_id' => $post_id]);

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
