<?php
$load_addons = 'quizbot';
require('../../../system/config_addons.php');
if(!canManageAddons()){
	die();
}
if(isset($_POST['room'], $_POST['status'], $_POST['type'], $_POST['quiz_file'], $_POST['scramble_file'])){
	
	$status = escape($_POST['status'], true);
	$room = escape($_POST['room'], true);
	$type = escape($_POST['type'], true);
	$quiz_file = escape($_POST['quiz_file']);
	$scramble_file = escape($_POST['scramble_file']);
	
	$mysqli->query("UPDATE boom_addons SET custom1 = '$room', custom2 = '$status', custom3 = '$type', custom4 = '$quiz_file', custom5 = '$scramble_file' WHERE addons = 'quizbot'");
	redisUpdateAddons('quizbot');
	echo 5;
	die();
}
if(isset($_POST['reset_score']) && canManageAddons()){
	$mysqli->query("UPDATE boom_users SET quiz_score = '0' WHERE user_id > 0");
	echo 1;
	die();
}
?>