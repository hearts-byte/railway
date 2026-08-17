<?php
require('../config_session.php');

if(!isset($_POST['level_up'])){
	die();
}

$target = escape($_POST['level_up'], true);
$user = userDetails($target);

if(empty($user) || !useLevel() || !canRankUser($user)){
	echo 0;
	die();
}

// تأكد ان المستخدم عنده صف بجدول الخبرة boom_exp
$exp = userExp($user);
if(empty($exp)){
	echo 0;
	die();
}

$mysqli->query("UPDATE boom_users SET user_level = user_level + 1 WHERE user_id = '{$user['user_id']}'");
$mysqli->query("UPDATE boom_exp SET exp_current = 0 WHERE uid = '{$user['user_id']}'");

clearNotifyAction($user['user_id'], 'level');
boomNotify('level', array('target'=> $user['user_id'], 'source'=> 'level', 'icon'=> 'level', 'custom'=> $user['user_level'] + 1));
redisUpdateUser($user['user_id']);

echo 1;
