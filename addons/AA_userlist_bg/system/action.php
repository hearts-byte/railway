<?php
$load_addons = 'AA_userlist_bg';
require_once('../../../system/config_addons.php');

function aaListBgDir(){
	return BOOM_PATH . '/addons/AA_userlist_bg/files/background/';
}

function aaListBgPath($file){
	return aaListBgDir() . $file;
}

function aaListBgAllowedFile($file){
	return preg_match('/^ulbg_[0-9]+_[0-9]+\.(jpg|jpeg|png|gif|webp)$/i', $file);
}

function aaListBgDeleteFile($file){
	if($file != '' && aaListBgAllowedFile($file)){
		$path = aaListBgPath($file);
		if(file_exists($path)){
			@unlink($path);
		}
	}
}

function aaListBgCurrent($target){
	global $mysqli;
	$target = (int) $target;
	$get = $mysqli->query("SELECT bg_file FROM userlist_bg WHERE user_id = '$target' LIMIT 1");
	if($get && $get->num_rows > 0){
		$row = $get->fetch_assoc();
		return $row['bg_file'];
	}
	return '';
}

function aaListBgDelete($target){
	global $mysqli;
	$target = (int) $target;
	if($target <= 0){
		return array('code' => 0);
	}
	aaListBgDeleteFile(aaListBgCurrent($target));
	$mysqli->query("DELETE FROM userlist_bg WHERE user_id = '$target'");
	return array('code' => 1, 'target' => $target);
}

function aaListBgUpload($target){
	global $mysqli, $setting;
	$target = (int) $target;
	if($target <= 0 || !isset($_FILES['file'])){
		return array('code' => 0);
	}
	if($_FILES['file']['error'] !== UPLOAD_ERR_OK || $_FILES['file']['size'] <= 0){
		return array('code' => 0);
	}
	if($_FILES['file']['size'] > 2097152){
		return array('code' => 3);
	}

	$info = @getimagesize($_FILES['file']['tmp_name']);
	if($info === false){
		return array('code' => 0);
	}

	$mime = $info['mime'];
	$ext = '';
	if($mime == 'image/jpeg'){
		$ext = 'jpg';
	}
	else if($mime == 'image/png'){
		$ext = 'png';
	}
	else if($mime == 'image/gif'){
		$ext = 'gif';
	}
	else if($mime == 'image/webp'){
		$ext = 'webp';
	}
	else {
		return array('code' => 0);
	}

	$dir = aaListBgDir();
	if(!is_dir($dir)){
		@mkdir($dir, 0755, true);
	}
	if(!is_writable($dir)){
		return array('code' => 0);
	}

	$file = 'ulbg_' . $target . '_' . time() . '.' . $ext;
	$path = aaListBgPath($file);

	if(!move_uploaded_file($_FILES['file']['tmp_name'], $path)){
		return array('code' => 0);
	}

	aaListBgDeleteFile(aaListBgCurrent($target));
	$safe = escape($file);
	$now = time();
	$check = $mysqli->query("SELECT bg_id FROM userlist_bg WHERE user_id = '$target' LIMIT 1");
	if($check && $check->num_rows > 0){
		$mysqli->query("UPDATE userlist_bg SET bg_file = '$safe', bg_date = '$now' WHERE user_id = '$target'");
	}
	else {
		$mysqli->query("INSERT INTO userlist_bg (user_id, bg_file, bg_date) VALUES ('$target', '$safe', '$now')");
	}

	return array('code' => 1, 'target' => $target, 'file' => $safe);
}

if(isset($_POST['set_addon_access'], $_POST['set_addon_access_staff'])){
	if(!canManageAddons()){
		die();
	}
	$rank = escape($_POST['set_addon_access']);
	$staff = escape($_POST['set_addon_access_staff']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$rank', custom1 = '$staff' WHERE addons = 'AA_userlist_bg'");
	redisUpdateAddons('AA_userlist_bg');
	echo 1;
	die();
}

if(isset($_POST['target_upload_bg'])){
	if(!boomAllow($addons['custom1'])){
		die();
	}
	echo json_encode(aaListBgUpload($_POST['target_upload_bg']), JSON_HEX_TAG);
	die();
}

if(isset($_POST['target_delete_bg'])){
	if(!boomAllow($addons['custom1'])){
		die();
	}
	echo json_encode(aaListBgDelete($_POST['target_delete_bg']), JSON_HEX_TAG);
	die();
}

if(isset($_POST['upload_my_bg'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo json_encode(aaListBgUpload($data['user_id']), JSON_HEX_TAG);
	die();
}

if(isset($_POST['delete_my_bg'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo json_encode(aaListBgDelete($data['user_id']), JSON_HEX_TAG);
	die();
}

echo json_encode(array('code' => 0), JSON_HEX_TAG);
die();
?>
