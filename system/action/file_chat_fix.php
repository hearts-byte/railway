<?php
/**
 * file_chat_fix.php
 * بديل غير مشفّر لرفع صورة/ملف بالدردشة العامة
 * بدلاً من الملف المشفّر file_chat.php
 * يُرفع داخل مجلد: system/action/
 *
 * أكواد الرد (مطابقة لما يتوقعه uploadChat في function_logged.js):
 * {"code": 5, "log": {...}}  = نجاح
 * {"code": 1}  = نوع ملف غير مدعوم
 * {"code": 9}  = محظور من الإرسال (بان/ميوت/كيك)
 * {"code": 0}  = خطأ عام
 */

require __DIR__ . "/../config_session.php";
header('Content-Type: application/json');

define('FILECHAT_FIX_DEBUG', true);

function fileChatFixDebug($label, $value = null) {
	if (!FILECHAT_FIX_DEBUG) return;
	error_log("[file_chat_fix] $label => " . json_encode($value));
}

if (empty($data) || empty($data['user_id'])) {
	fileChatFixDebug('reject_reason', 'no_session_data');
	echo json_encode(['code' => 0]);
	exit;
}

if (!function_exists('checkToken') || !checkToken()) {
	fileChatFixDebug('reject_reason', 'bad_token');
	echo json_encode(['code' => 0]);
	exit;
}

// تحقق من الحظر/الميوت/الكيك
if (
	(function_exists('isBanned') && isBanned($data)) ||
	(function_exists('isMuted') && isMuted($data)) ||
	(function_exists('isKicked') && isKicked($data))
) {
	fileChatFixDebug('reject_reason', 'user_blocked');
	echo json_encode(['code' => 9]);
	exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
	fileChatFixDebug('reject_reason', 'no_file_or_upload_error');
	echo json_encode(['code' => 0]);
	exit;
}

// تحقق من حجم الملف
if (function_exists('fileError') && fileError(1)) {
	fileChatFixDebug('reject_reason', 'file_too_big');
	echo json_encode(['code' => 1]);
	exit;
}

$original_name = $_FILES['file']['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

$file_type = '';
if (function_exists('isImage') && isImage($ext)) {
	$file_type = 'image';
} elseif (function_exists('isFile') && isFile($ext)) {
	$file_type = 'file';
} elseif (function_exists('isMusic') && isMusic($ext)) {
	$file_type = 'audio';
} elseif (function_exists('isVideo') && isVideo($ext)) {
	$file_type = 'video';
}

if ($file_type === '') {
	fileChatFixDebug('reject_reason', 'unsupported_file_type: ' . $ext . ' / ' . $_FILES['file']['type']);
	echo json_encode(['code' => 1]);
	exit;
}

// تسمية الملف ونقله لمجلد chat
$file_name = function_exists('encodeFile') ? encodeFile($ext) : ('chat_' . $data['user_id'] . '_' . time() . '.' . $ext);
$destination = 'chat/' . $file_name;

if (!is_dir(__DIR__ . '/../../chat')) {
	@mkdir(__DIR__ . '/../../chat', 0755, true);
}

if ($file_type === 'image' && function_exists('boomMoveImageFile')) {
	boomMoveImageFile($destination, $_FILES['file']['type']);
} elseif (function_exists('boomMoveFile')) {
	boomMoveFile($destination);
} else {
	move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../../' . $destination);
}

if (!file_exists(__DIR__ . '/../../' . $destination)) {
	fileChatFixDebug('reject_reason', 'move_uploaded_file_failed');
	echo json_encode(['code' => 0]);
	exit;
}

// بناء محتوى الرسالة نفسه (كود HTML للصورة/الملف) بنفس دالة النظام الأصلية
$html_content = function_exists('boomPostFile') ? boomPostFile($destination, $file_type) : '';

// إرسال الرسالة بنفس دالة النظام الأصلية المستخدمة للرسائل النصية العادية
$result = userPostChat($html_content, [
	'file' => $destination,
	'filetype' => $file_type,
]);

if (empty($result)) {
	fileChatFixDebug('reject_reason', 'userPostChat_returned_empty');
	echo json_encode(['code' => 0]);
	exit;
}

fileChatFixDebug('success', ['file' => $destination, 'log_id' => $result['log_id'] ?? null]);

if (function_exists('redisFlushAll')) {
	redisFlushAll();
}
if (function_exists('boomCacheUpdate')) {
	boomCacheUpdate();
}

echo json_encode(['code' => 5, 'log' => $result]);
exit;
