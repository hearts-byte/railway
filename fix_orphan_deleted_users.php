<?php
/**
 * fix_orphan_deleted_users.php
 * سكربت صيانة يُشغَّل مرة وحدة بس: يحذف كل الصفوف اليتيمة (بجداول ثانية)
 * العائدة لأعضاء انحذفوا من boom_users سابقًا بدون تنظيف كامل
 * يُرفع مؤقتًا بجذر الموقع، يُشغَّل مرة، وبعدها يُحذف نهائيًا
 */

require __DIR__ . "/config_session.php";

if (empty($data) || empty($data['user_id'])) {
	die("يجب تسجيل الدخول أولاً");
}

// كل الجداول اللي فيها عمود يشير لعضو، مع اسم العمود الصحيح بكل جدول
$tables = [
	'boom_exp'            => 'uid',
	'boom_users_data'     => 'uid',
	'boom_users_gift'     => 'target',
	'boom_name'           => 'uid',
	'boom_data'           => 'data_user',
	'boom_call'           => 'call_target', // يُعالج بشكل خاص أدناه لأن فيه عمودين
];

echo "<h2>تنظيف الصفوف اليتيمة</h2>";

foreach ($tables as $table => $column) {
	$deleted = $mysqli->query("
		DELETE t FROM `$table` t
		LEFT JOIN boom_users u ON t.`$column` = u.user_id
		WHERE u.user_id IS NULL
	");
	if ($deleted) {
		echo "<p>✅ $table: تم حذف " . $mysqli->affected_rows . " صف يتيم</p>";
	} else {
		echo "<p style='color:red;'>❌ $table: فشل - " . $mysqli->error . "</p>";
	}
}

// جداول فيها أكثر من عمود مستخدم (hunter/target وما شابه) نتعامل معها لحالها
$dual_tables = [
	'boom_private'       => ['hunter', 'target'],
	'boom_conversation'  => ['hunter', 'target'],
	'boom_friends'       => ['hunter', 'target'],
	'boom_notification'  => ['notifier', 'notified'],
	'boom_ignore'        => ['ignorer', 'ignored'],
	'boom_report'        => ['report_user', 'report_target'],
	'boom_history'       => ['hunter', 'target'],
	'boom_pro_like'      => ['hunter', 'target'],
	'boom_console'       => ['hunter', 'target'],
];

foreach ($dual_tables as $table => $cols) {
	$total = 0;
	foreach ($cols as $col) {
		$deleted = $mysqli->query("
			DELETE t FROM `$table` t
			LEFT JOIN boom_users u ON t.`$col` = u.user_id
			WHERE t.`$col` > 0 AND u.user_id IS NULL
		");
		if ($deleted) {
			$total += $mysqli->affected_rows;
		}
	}
	echo "<p>✅ $table: تم حذف $total صف يتيم</p>";
}

// جدول الدردشة: نحذف فقط صفوف المستخدم المحذوف نفسه (مو الرسائل الموجهة له quser، عشان ما نخرب سياق المحادثة)
$deleted_chat = $mysqli->query("
	DELETE t FROM boom_chat t
	LEFT JOIN boom_users u ON t.user_id = u.user_id
	WHERE t.user_id > 0 AND t.syslog = 0 AND u.user_id IS NULL
");
if ($deleted_chat) {
	echo "<p>✅ boom_chat: تم حذف " . $mysqli->affected_rows . " رسالة يتيمة</p>";
}

echo "<p style='color:red; font-weight:bold; margin-top:20px;'>⚠️ احذف هذا الملف من السيرفر الحين بعد ما شفت النتيجة.</p>";
?>
