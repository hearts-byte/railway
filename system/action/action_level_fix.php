<?php
require('../config_session.php');

if(!isset($_POST['level_up']) || !isset($_POST['amount'])){
	die();
}

$target = escape($_POST['level_up'], true);
$amount = (int)$_POST['amount'];

if($amount < 1 || $amount > 1000){
	echo 0;
	die();
}

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

$mysqli->query("UPDATE boom_users SET user_level = user_level + {$amount} WHERE user_id = '{$user['user_id']}'");
$mysqli->query("UPDATE boom_exp SET exp_current = 0 WHERE uid = '{$user['user_id']}'");

clearNotifyAction($user['user_id'], 'level');
boomNotify('level', array('target'=> $user['user_id'], 'source'=> 'level', 'icon'=> 'level', 'custom'=> $user['user_level'] + $amount));
redisUpdateUser($user['user_id']);

echo 1;
