<?php
/**
 * cleanup_orphans.php
 * ينظف البيانات اليتيمة (مرتبطة بحسابات محذوفة من boom_users) من عدة جداول.
 * يُرفع بأي مكان تحت مجلد system/ ويُشغّل يدويًا من المتصفح مباشرة من طرف المالك فقط.
 *
 * الوضع الافتراضي: DRY RUN (تجربة فقط) — يعرض كم صف بده يتحذف بدون ما يحذف فعليًا.
 * لتنفيذ الحذف الفعلي: افتح الرابط بإضافة ?confirm=1
 */

require __DIR__ . "/config.php";

// حماية: يُسمح فقط لرتبة مالك (نفس can_delete)
$can_run = (int) ($setting['can_delete'] ?? 999);
if (empty($data) || empty($data['user_id']) || (int)$data['user_rank'] < $can_run) {
	die("غير مصرح لك بتشغيل هذا السكربت.");
}

$confirm = isset($_GET['confirm']) && $_GET['confirm'] == '1';

// كل جدول: [اسم الجدول => أعمدة user_id المرتبطة]
$targets = [
	'boom_chat'         => ['user_id'],
	'boom_friends'       => ['hunter', 'target'],
	'boom_conversation'  => ['hunter', 'target'],
	'boom_private'       => ['hunter', 'target'],
	'boom_notification'  => ['notifier', 'notified'],
	'boom_users_gift'    => ['target'],
	'boom_ignore'        => ['ignorer', 'ignored'],
	'boom_post'          => ['post_user'],
	'boom_users_data'    => ['uid'],
	'boom_report'        => ['report_user', 'report_target'],
];

echo "<pre style='font-family:monospace;direction:ltr;text-align:left'>";
echo $confirm ? "=== وضع التنفيذ الفعلي (DELETE) ===\n\n" : "=== وضع التجربة (DRY RUN) — لا شيء يُحذف فعليًا ===\n\n";

$total_would_delete = 0;

foreach ($targets as $table => $columns) {
	foreach ($columns as $col) {
		// تحقق أن الجدول والعمود موجودين فعلاً قبل التنفيذ
		$check = $mysqli->query("SHOW TABLES LIKE '$table'");
		if (!$check || $check->num_rows === 0) {
			echo "[تخطي] الجدول $table غير موجود\n";
			continue 2;
		}

		$count_q = $mysqli->query("
			SELECT COUNT(*) as cnt FROM `$table`
			WHERE `$col` NOT IN (SELECT user_id FROM boom_users)
			AND `$col` != 0
			AND `$col` IS NOT NULL
		");

		if (!$count_q) {
			echo "[خطأ] $table.$col : " . $mysqli->error . "\n";
			continue;
		}

		$row = $count_q->fetch_assoc();
		$cnt = (int) $row['cnt'];
		$total_would_delete += $cnt;

		if ($cnt > 0) {
			echo "$table.$col : $cnt صف يتيم" . ($confirm ? " -- جارٍ الحذف..." : "") . "\n";

			if ($confirm && $cnt > 0) {
				$del = $mysqli->query("
					DELETE FROM `$table`
					WHERE `$col` NOT IN (SELECT user_id FROM boom_users)
					AND `$col` != 0
					AND `$col` IS NOT NULL
				");
				if (!$del) {
					echo "   [فشل الحذف] " . $mysqli->error . "\n";
				} else {
					echo "   تم حذف " . $mysqli->affected_rows . " صف.\n";
				}
			}
		}
	}
}

echo "\n----------------------------------------\n";
echo "إجمالي الصفوف اليتيمة الموجودة حاليًا: $total_would_delete\n";

if (!$confirm) {
	echo "\nهذا كان وضع تجربة فقط (Dry Run). لتنفيذ الحذف الفعلي افتح الرابط مضافًا له: ?confirm=1\n";
} else {
	if (function_exists('redisFlushAll')) {
		redisFlushAll();
		echo "\nتم تفريغ الكاش (Redis).\n";
	}
}

echo "</pre>";
