<?php
/**
 * action_createuser_fix.php
 * بديل غير مشفّر لعملية إنشاء حساب جديد من لوحة التحكم (create_user)
 * بدلاً من الملف المشفّر action_users.php
 * يُرفع داخل مجلد: system/action/
 *
 * أكواد الرد (متوافقة مع الواجهة الحالية بدون تعديل JS):
 * 1  = نجاح
 * 2  = حقول فارغة
 * 3  = اسم مستخدم غير صالح
 * 4  = اسم المستخدم مستخدم مسبقًا
 * 5  = إيميل غير صالح
 * 6  = الإيميل مستخدم مسبقًا
 * 13 = لا يحقق شرط العمر الأدنى
 * 0  = خطأ عام / غير مصرح
 */

require __DIR__ . "/../config_session.php";

// وضع تشخيص مؤقت: اجعلها true أثناء الاختبار فقط، ثم أعدها false على الإنتاج
define('CREATEUSER_FIX_DEBUG', true);

function createUserFixDebug($label, $value = null) {
	if (!CREATEUSER_FIX_DEBUG) return;
	error_log("[action_createuser_fix] $label => " . json_encode($value));
}

if (!isset($_POST['create_user'])) {
	echo 0;
	exit;
}

if (empty($data) || empty($data['user_id'])) {
	createUserFixDebug('reject_reason', 'no_session_data');
	echo 0;
	exit;
}

if (!function_exists('canCreateUser') || !canCreateUser()) {
	createUserFixDebug('reject_reason', 'not_allowed_rank');
	echo 0;
	exit;
}

$name     = trim($_POST['create_name'] ?? '');
$password = trim($_POST['create_password'] ?? '');
$email    = trim($_POST['create_email'] ?? '');
$gender   = trim($_POST['create_gender'] ?? '');
$age      = trim($_POST['create_age'] ?? '');

createUserFixDebug('input', [
	'name'   => $name,
	'email'  => $email,
	'gender' => $gender,
	'age'    => $age,
	// كلمة المرور غير مسجّلة بالـ log لأسباب أمنية
]);

// 1) فحص الحقول الفارغة (نفس رسالة "بعض الحقول المطلوبة فارغة")
if ($name === '' || $password === '' || $email === '' || $gender === '' || $age === '') {
	createUserFixDebug('reject_reason', 'empty_field');
	echo 2;
	exit;
}

$name  = escape($name);
$email = escape($email);
$age   = (int) $age;
$gender = (int) $gender;

// 2) التحقق من شرط العمر الأدنى
$min_age = (int) ($setting['min_age'] ?? 0);
if ($age < $min_age) {
	createUserFixDebug('reject_reason', 'age_requirement');
	echo 13;
	exit;
}

// 3) التحقق من صيغة اسم المستخدم
if (!function_exists('validName') || !validName($name)) {
	createUserFixDebug('reject_reason', 'invalid_username');
	echo 3;
	exit;
}

// 4) التحقق من عدم تكرار اسم المستخدم
if (!function_exists('boomUsername') || !boomUsername($name)) {
	createUserFixDebug('reject_reason', 'username_taken');
	echo 4;
	exit;
}

// 5) التحقق من صيغة الإيميل
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	createUserFixDebug('reject_reason', 'invalid_email');
	echo 5;
	exit;
}

// 6) التحقق من عدم تكرار الإيميل
$check_email = $mysqli->query("SELECT user_id FROM boom_users WHERE user_email = '$email' LIMIT 1");
if ($check_email && $check_email->num_rows > 0) {
	createUserFixDebug('reject_reason', 'email_taken');
	echo 6;
	exit;
}

// تشفير كلمة المرور بنفس طريقة السيستم الأصلية
$hashed_password = encrypt($password);
$now = time();
$default_room = (int) ($setting['main_room'] ?? 1);
$new_session = md5(rand(10000, 99999) . rand(10000, 99999));

$insert = $mysqli->query("
	INSERT INTO boom_users
	(user_name, user_password, user_email, user_sex, user_age, user_join, last_action, user_rank, user_level, user_roomid, session_id, user_status)
	VALUES
	('$name', '$hashed_password', '$email', '$gender', '$age', '$now', '$now', '0', '1', '$default_room', '$new_session', '0')
");

if (!$insert) {
	createUserFixDebug('reject_reason', 'sql_insert_failed: ' . $mysqli->error);
	echo 0;
	exit;
}

createUserFixDebug('success', ['new_user_id' => $mysqli->insert_id]);

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
	boomConsole('create_user_admin', [
		'target' => $mysqli->insert_id,
		'name'   => $name,
	]);
}

echo 1;
exit;
