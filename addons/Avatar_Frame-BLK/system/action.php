<?php
// Reconstructed action.php for Avatar_Frame-BLK
// The original action.php ships ionCube-encoded and requires the
// ionCube Loader PHP extension, which is not installed on this server.
// This file re-implements the same operations based on the field names
// and templates used by config.php, install.php and the *.php templates
// of this addon (all of which are plain, unencoded PHP).

$load_addons = basename(dirname(dirname(__FILE__)));
require_once('../../../system/config_addons.php');
define('ADDON', $load_addons);

// ================= ADMIN ACTIONS =================

// Save addon settings (access rank + price)
if(isset($_POST['save_settings']) && canManageAddons()){
	$access = escape($_POST['set_access']);
	$price  = escape($_POST['set_price']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$access', custom1 = '$price' WHERE addons = '" . $addons['addons'] . "'");
	echo 1;
	exit;
}

// Show "add frame" modal
if(isset($_POST['gift_add_box']) && canManageAddons()){
	echo addonTemplate('frame_add');
	exit;
}

// Add a new frame (file upload)
if(isset($_POST['add_frame']) && isset($_FILES['file']) && canManageAddons()){
	if(0 < $_FILES['file']['error']){
		echo 2;
		exit;
	}
	$info = pathinfo($_FILES['file']['name']);
	$extension = strtolower($info['extension']);
	$allowed = ['png','gif','webp'];
	if(!in_array($extension, $allowed)){
		echo 2;
		exit;
	}
	$rank   = escape($_POST['add_rank']);
	$method = escape($_POST['add_method']);
	$price  = escape($_POST['add_price']);
	$file_name = encodeFile($extension);
	move_uploaded_file($_FILES['file']['tmp_name'], BOOM_PATH . '/addons/' . $addons['addons'] . '/files/frames/' . $file_name);
	$mysqli->query("INSERT INTO avatar_frame (price, `rank`, method, tumb) VALUES ('$price', '$rank', '$method', '$file_name')");
	redisDel('avatar_frame:list');
	echo 1;
	exit;
}

// Show "edit frame" modal
if(isset($_POST['edit_frame']) && canManageAddons()){
	$id = (int) $_POST['edit_frame'];
	$get = $mysqli->query("SELECT * FROM avatar_frame WHERE id = '$id'");
	if($get->num_rows > 0){
		$boom = $get->fetch_assoc();
		echo addonTemplate('frame_edit_box', $boom);
	}
	exit;
}

// Save edits to an existing frame
if(isset($_POST['save_frame']) && canManageAddons()){
	$id     = (int) $_POST['save_frame'];
	$price  = escape($_POST['save_price']);
	$rank   = escape($_POST['save_rank']);
	$method = escape($_POST['save_method']);
	$mysqli->query("UPDATE avatar_frame SET price = '$price', `rank` = '$rank', method = '$method' WHERE id = '$id'");
	redisDel('avatar_frame:list');
	echo 1;
	exit;
}

// Delete a frame
if(isset($_POST['delete_frame']) && canManageAddons()){
	$id = (int) $_POST['delete_frame'];
	$get = $mysqli->query("SELECT * FROM avatar_frame WHERE id = '$id'");
	if($get->num_rows > 0){
		$frame = $get->fetch_assoc();
		@unlink(BOOM_PATH . '/addons/' . $addons['addons'] . '/files/frames/' . $frame['tumb']);
		$mysqli->query("UPDATE boom_users SET avatar_frame = 0 WHERE avatar_frame = '$id'");
		$mysqli->query("DELETE FROM avatar_frame WHERE id = '$id'");
		redisDel('avatar_frame:list');
		echo 1;
	} else {
		echo 0;
	}
	exit;
}

// ================= USER ACTIONS =================

// Show the frame shop / "my frame" box
if(isset($_POST['get_box']) && boomAllow($addons['addons_access'])){
	echo addonTemplate('frame_box');
	exit;
}

// Equip a frame (deducts currency)
if(isset($_POST['set_frame']) && boomAllow($addons['addons_access'])){
	$id = (int) $_POST['set_frame'];
	$get = $mysqli->query("SELECT * FROM avatar_frame WHERE id = '$id'");
	if($get->num_rows == 0){
		echo 0;
		exit;
	}
	$frame = $get->fetch_assoc();
	if(!boomAllow($frame['rank'])){
		echo 0;
		exit;
	}
	if($frame['method'] == 1){
		if($data['user_gold'] < $frame['price']){
			echo 0;
			exit;
		}
		removeGold($data, $frame['price']);
	} else {
		if($data['user_ruby'] < $frame['price']){
			echo 0;
			exit;
		}
		removeRuby($data, $frame['price']);
	}
	$mysqli->query("UPDATE boom_users SET avatar_frame = '$id' WHERE user_id = '" . $data['user_id'] . "'");
	redisUpdateUser($data['user_id']);
	echo 1;
	exit;
}

// Remove the currently equipped frame (no refund)
if(isset($_POST['unset_frame']) && boomAllow($addons['addons_access'])){
	$mysqli->query("UPDATE boom_users SET avatar_frame = 0 WHERE user_id = '" . $data['user_id'] . "'");
	redisUpdateUser($data['user_id']);
	echo 1;
	exit;
}

// Return the list of {user_id, tumb} for every user with an active frame,
// used by aps_song_profile.js-style polling in Avatar_Frame-BLK.php
if(isset($_POST['get_user_frames_list'])){
	$list = [];
	$get = $mysqli->query("SELECT boom_users.user_id, avatar_frame.tumb FROM boom_users INNER JOIN avatar_frame ON avatar_frame.id = boom_users.avatar_frame WHERE boom_users.avatar_frame != 0");
	if($get->num_rows > 0){
		while($row = $get->fetch_assoc()){
			$list[] = ['user_id' => (string) $row['user_id'], 'tumb' => $row['tumb']];
		}
	}
	echo json_encode($list);
	exit;
}

?>
