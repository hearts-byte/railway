<?php
$load_addons = 'AA_global_notify';
require_once('../../../system/config_addons.php');

function aaGlobalNotifyGetAllUsers(){
	global $mysqli;
	$list = array();
	$find = $mysqli->query("SELECT user_id FROM boom_users");
	if($find && $find->num_rows > 0){
		while($row = mysqli_fetch_object($find)){
			array_push($list, (int) $row->user_id);
		}
	}
	if($find){
		mysqli_free_result($find);
	}
	return $list;
}

function aaGlobalNotifySend($message, $publishAs){
	global $mysqli, $data, $setting;

	$message = trim(strip_tags($message));
	if($message === ''){
		return 0;
	}
	if(mb_strlen($message) > 300){
		$message = mb_substr($message, 0, 300);
	}

	// convert :059: style emoticon shortcuts to actual <img> emoticons
	$message = emoticon($message);
	// escape for safe SQL storage without breaking the emoticon <img> tags
	$message = $mysqli->real_escape_string($message);

	$publishAs = ($publishAs === 'self') ? 'self' : 'system';
	$hunter = ($publishAs === 'self') ? (int) $data['user_id'] : (int) $setting['system_id'];

	$list = aaGlobalNotifyGetAllUsers();
	if(empty($list)){
		return 0;
	}

	boomListNotify($list, 'custom', array(
		'hunter'=> $hunter,
		'source'=> 'AA_global_notify',
		'custom'=> $message,
		'icon'=> 'announce',
	));

	$mysqli->query("INSERT INTO aa_global_notify_log (sender_id, publish_as, message, recipients, log_date) VALUES ('{$data['user_id']}', '$publishAs', '$message', '" . count($list) . "', '" . time() . "')");

	return count($list);
}

// save minimum rank allowed to broadcast
if(isset($_POST['set_addon_access'])){
	if(!canManageAddons()){
		die();
	}
	$rank = escape($_POST['set_addon_access']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$rank' WHERE addons = 'AA_global_notify'");
	echo 1;
	die();
}

// send the broadcast
if(isset($_POST['global_notify_message'])){
	if(!boomAllow($addons['addons_access'])){
		die();
	}
	$publishAs = isset($_POST['global_notify_publish_as']) ? $_POST['global_notify_publish_as'] : 'system';
	$sent = aaGlobalNotifySend($_POST['global_notify_message'], $publishAs);
	echo $sent > 0 ? $sent : 0;
	die();
}

echo 0;
die();
?>
