<?php
/**
 * action_addons_fix.php
 * بديل غير مشفّر لعملية activate_addons بدلاً من الملف المشفّر system_addons.php
 * يُرفع داخل مجلد: system/action/
 */

require __DIR__ . "/../config_session.php";

// وضع تشخيص مؤقت: اجعلها true أثناء الاختبار فقط، ثم أعدها false على الإنتاج
define('ADDONS_FIX_DEBUG', true);

function addonsFixDebug($label, $value = null) {
	if (!ADDONS_FIX_DEBUG) return;
	error_log("[action_addons_fix] $label => " . json_encode($value));
}

function addonsFixReply($code, $error = null) {
	$out = ["code" => $code];
	if ($error !== null) $out['error'] = $error;
	echo json_encode($out);
	exit;
}

if (!isset($_POST['activate_addons'], $_POST['addons'])) {
	echo 0;
	exit;
}

// المستخدم غير مسجل دخول أصلاً
if (empty($data) || empty($data['user_id'])) {
	addonsFixDebug('reject_reason', 'no_session_data');
	addonsFixReply(0, 'not_logged_in');
}

// تأكد إن المستخدم أدمن (نفس منطق لوحة التحكم: cp=admin + رتبة أدمن)
$my_rank = (int) $data['user_rank'];
if (empty($_POST['cp']) || $_POST['cp'] !== 'admin' || $my_rank < 99) {
	addonsFixDebug('reject_reason', 'not_admin');
	addonsFixReply(0, 'access_denied');
}

$nameaddon = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['addons']);

addonsFixDebug('input', [
	'my_user_id' => $data['user_id'],
	'my_rank'    => $my_rank,
	'addons'     => $nameaddon,
]);

// تم التحديث: إزالة حظر aps_ لتمكين تفعيل إضافة aps_box_xo والإضافات المماثلة
if ($nameaddon === '' || strpos($nameaddon, 'BLK_') !== false) {
	addonsFixDebug('reject_reason', 'invalid_name');
	addonsFixReply(0, 'invalid_addon_name');
}

$nameaddon_safe = $mysqli->real_escape_string($nameaddon);

// تأكد الإضافة مو مثبتة أصلاً
$check = $mysqli->query("SELECT addons FROM boom_addons WHERE addons = '$nameaddon_safe' LIMIT 1");
if ($check && $check->num_rows > 0) {
	addonsFixDebug('reject_reason', 'already_installed');
	addonsFixReply(0, 'addon_already_installed');
}

$install_file = BOOM_PATH . "/addons/$nameaddon/system/install.php";
addonsFixDebug('install_file_path', $install_file);

if (!file_exists($install_file)) {
	addonsFixDebug('reject_reason', 'install_file_missing');
	addonsFixReply(0, 'install_file_not_found: ' . $install_file);
}

if (!is_readable($install_file)) {
	addonsFixDebug('reject_reason', 'install_file_not_readable');
	addonsFixReply(0, 'install_file_not_readable: ' . $install_file);
}

require($install_file);

if (!isset($ad['name'])) {
	addonsFixDebug('reject_reason', 'ad_name_not_set');
	addonsFixReply(0, 'install_file_did_not_define_ad_name');
}

$load    = isset($ad['load']) ? $mysqli->real_escape_string($ad['load']) : 0;
$custom1 = $mysqli->real_escape_string($ad['custom1'] ?? '');
$custom2 = $mysqli->real_escape_string($ad['custom2'] ?? '');
$custom3 = $mysqli->real_escape_string($ad['custom3'] ?? '');
$custom4 = $mysqli->real_escape_string($ad['custom4'] ?? '');
$custom5 = $mysqli->real_escape_string($ad['custom5'] ?? '');
$custom6 = $mysqli->real_escape_string($ad['custom6'] ?? '');
$custom7 = $mysqli->real_escape_string($ad['custom7'] ?? '');
$custom8 = $mysqli->real_escape_string($ad['custom8'] ?? '');
$custom9 = $mysqli->real_escape_string($ad['custom9'] ?? '');
$custom10 = $mysqli->real_escape_string($ad['custom10'] ?? '');

$res2 = $mysqli->query("SELECT MAX(addons_id) AS maxaid FROM boom_addons");
$row2 = $res2->fetch_assoc();
$next_addons = (int) $row2['maxaid'] + 1;

addonsFixDebug('ad_array', $ad);

if (isset($ad['bot_name'], $ad['bot_type'])) {
	$bot_name = $mysqli->real_escape_string($ad['bot_name']);
	$bot_type = (int) $ad['bot_type'];

	$res = $mysqli->query("SELECT MAX(user_id) AS maxid FROM boom_users");
	$row = $res->fetch_assoc();
	$next_id = (int) $row['maxid'] + 1;

	$pass = function_exists('randomPass') ? randomPass() : bin2hex(random_bytes(8));
	$time = time();

	$ins1 = $mysqli->query("INSERT INTO boom_users (user_id, user_name, user_ip, user_join, user_password, user_rank, user_tumb, user_bot)
					VALUES ($next_id, '$bot_name', '0.0.0.0', '$time', '$pass', '69', 'default_system.png', '$bot_type')");

	if (!$ins1) {
		addonsFixDebug('reject_reason', 'bot_user_insert_failed');
		addonsFixReply(0, 'bot_user_insert_error: ' . $mysqli->error);
	}

	$sql = "INSERT INTO boom_addons (addons_id, addons, addons_load, addons_key, addons_access, bot_name, bot_id,
			custom1, custom2, custom3, custom4, custom5, custom6, custom7, custom8, custom9, custom10)
			VALUES ($next_addons, '$nameaddon_safe', '$load', '', '0', '$bot_name', '$next_id',
			'$custom1', '$custom2', '$custom3', '$custom4', '$custom5', '$custom6', '$custom7', '$custom8', '$custom9', '$custom10')";
}
else {
	if (!isset($ad['access'])) {
		addonsFixDebug('reject_reason', 'ad_access_not_set');
		addonsFixReply(0, 'install_file_missing_access_field');
	}

	$access = $mysqli->real_escape_string($ad['access']);
	$sql = "INSERT INTO boom_addons (addons_id, addons, addons_load, addons_key, addons_access,
			custom1, custom2, custom3, custom4, custom5, custom6, custom7, custom8, custom9, custom10)
			VALUES ($next_addons, '$nameaddon_safe', '$load', '', '$access',
			'$custom1', '$custom2', '$custom3', '$custom4', '$custom5', '$custom6', '$custom7', '$custom8', '$custom9', '$custom10')";
}

if ($mysqli->query($sql)) {
	addonsFixDebug('success', ['addons_id' => $next_addons, 'addons' => $nameaddon]);

	if (function_exists('redisFlushAll')) redisFlushAll();
	if (function_exists('boomCacheUpdate')) boomCacheUpdate();
	if (function_exists('opcache_reset')) opcache_reset();
	if (function_exists('boomConsole')) {
		boomConsole('activate_addons', ['addons' => $nameaddon]);
	}

	addonsFixReply(1);
} else {
	addonsFixDebug('reject_reason', 'sql_insert_failed');
	addonsFixReply(0, 'sql_error: ' . $mysqli->error);
}
