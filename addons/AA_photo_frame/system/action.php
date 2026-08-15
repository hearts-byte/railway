<?php
$load_addons = 'AA_photo_frame';
require_once('../../../system/config_addons.php');

function aaPhotoFrameAllowed($frame){
	if($frame == ''){
		return false;
	}
	if(!preg_match('/^[A-Za-z0-9_.-]+\.(png|webp|gif)$/i', $frame)){
		return false;
	}
	return file_exists(__DIR__ . '/../files/frame/' . $frame);
}

function aaPhotoFrameSave($target, $frame){
	global $mysqli;

	if(!aaPhotoFrameAllowed($frame)){
		return 0;
	}

	$target = (int) $target;
	$frame = escape($frame);
	$result = $mysqli->query("SELECT frame_id FROM photo_frames WHERE user_id = '$target' LIMIT 1");

	if($result && $result->num_rows > 0){
		$mysqli->query("UPDATE photo_frames SET frame_name = '$frame' WHERE user_id = '$target'");
	}
	else {
		$mysqli->query("INSERT INTO photo_frames (user_id, frame_name) VALUES ('$target', '$frame')");
	}

	return 1;
}

function aaPhotoFrameDelete($target){
	global $mysqli;

	$target = (int) $target;
	$result = $mysqli->query("SELECT frame_id FROM photo_frames WHERE user_id = '$target' LIMIT 1");

	if($result && $result->num_rows > 0){
		$mysqli->query("DELETE FROM photo_frames WHERE user_id = '$target'");
		return 1;
	}

	return 2;
}

if(isset($_POST['set_addon_access'], $_POST['set_addon_access_staff']) && canManageAddons()){
	$rank = escape($_POST['set_addon_access']);
	$staff = escape($_POST['set_addon_access_staff']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$rank', custom1 = '$staff' WHERE addons = 'AA_photo_frame'");
	redisUpdateAddons('AA_photo_frame');
	echo 1;
	die();
}

if(isset($_POST['frame_target_staff'], $_POST['set_photo_frame_staff'])){
	if(!boomAllow($addons['addons_access']) || !boomAllow($addons['custom1'])){
		die();
	}
	echo aaPhotoFrameSave($_POST['frame_target_staff'], $_POST['set_photo_frame_staff']);
	die();
}

if(isset($_POST['del_frame_target_staff'])){
	if(!boomAllow($addons['addons_access']) || !boomAllow($addons['custom1'])){
		die();
	}
	echo aaPhotoFrameDelete($_POST['del_frame_target_staff']);
	die();
}

if(isset($_POST['set_photo_frame'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo aaPhotoFrameSave($data['user_id'], $_POST['set_photo_frame']);
	die();
}

if(isset($_POST['del_frame_target'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	echo aaPhotoFrameDelete($data['user_id']);
	die();
}

echo 0;
?>
