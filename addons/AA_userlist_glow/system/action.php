<?php
$load_addons = 'AA_userlist_glow';
require_once('../../../system/config_addons.php');

function aaGlowCleanColor($color){
	$color = trim(escape($color));
	if($color === ''){
		return '';
	}
	if(preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/', $color)){
		return $color;
	}
	if(preg_match('/^rgba?\([0-9,\.\s]+\)$/', $color)){
		return $color;
	}
	return '';
}

function aaGlowSave($target, $color){
	global $mysqli;
	$target = (int) $target;
	if($target <= 0 || $color === ''){
		return 0;
	}
	$check = $mysqli->query("SELECT glow_id FROM userlist_glow WHERE user_id = '$target' LIMIT 1");
	if($check && $check->num_rows > 0){
		$mysqli->query("UPDATE userlist_glow SET glow_color = '$color' WHERE user_id = '$target'");
	}
	else {
		$mysqli->query("INSERT INTO userlist_glow (user_id, glow_color) VALUES ('$target', '$color')");
	}
	return 1;
}

function aaGlowDelete($target){
	global $mysqli;
	$target = (int) $target;
	if($target <= 0){
		return 0;
	}
	$mysqli->query("DELETE FROM userlist_glow WHERE user_id = '$target'");
	return 1;
}

if(isset($_POST['set_addon_access'], $_POST['set_addon_access_staff'])){
	if(!canManageAddons()){
		die();
	}
	$rank = escape($_POST['set_addon_access']);
	$staff = escape($_POST['set_addon_access_staff']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$rank', custom1 = '$staff' WHERE addons = 'AA_userlist_glow'");
	echo 1;
	die();
}

if(isset($_POST['user_target'], $_POST['user_list_glow'])){
	if(!boomAllow($addons['custom1'])){
		die();
	}
	echo aaGlowSave($_POST['user_target'], aaGlowCleanColor($_POST['user_list_glow']));
	die();
}

if(isset($_POST['user_target'], $_POST['del_user_list_glow'])){
	if(!boomAllow($addons['custom1'])){
		die();
	}
	echo aaGlowDelete($_POST['user_target']);
	die();
}

if(isset($_POST['user_list_glow_user'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo aaGlowSave($data['user_id'], aaGlowCleanColor($_POST['user_list_glow_user']));
	die();
}

if(isset($_POST['del_user_list_glow_user'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo aaGlowDelete($data['user_id']);
	die();
}

echo 0;
die();
?>
