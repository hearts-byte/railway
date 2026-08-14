<?php
$load_addons = 'aps_box_xo';
require_once('../../../system/config_addons.php');
function UpdataDataXo($id,$type) {
	global $mysqli,$data;
	$mysqli->query("UPDATE ps_box_xo SET A1='', A2='', A3='', A4='', A5='', A6='', A7='', A8='', A9='', win = '$type', win_id = '{$data['user_id']}' WHERE id = '$id'");
}

if(isset($_POST['EndGameXo'],$_POST['id'],$_POST['type'])){
   $id = escape($_POST['id']);
   $type = escape($_POST['type']);
   UpdataDataXo($id,$type);
   $xo = getDataXo($id);
   $user = userDetails($xo['uid']);
   if($type == 1){
	$like_msg = str_replace(array('%data%','%user%'), array($data['user_name']) , '<p>الفائز بالجولة %data% ('.userDetails($xo['tid'])['user_name'].' <b class="p_Xred"> VS </b> '.userDetails($xo['uid'])['user_name'].')</p>');
	if($xo['type'] == 1){
		systemPostChat($data['user_roomid'], $like_msg); 
	}else{
		postPrivate($data["user_id"], $xo['uid'], $like_msg, 1);
	}
   }else{
	$like_msg = str_replace(array('%data%','%user%'), array($data['user_name']) , 'تعادل (<p class="p_X">'.userDetails($xo['tid'])['user_name'].'<b class="p_Xred"> VS </b>'.userDetails($xo['uid'])['user_name'].')</p>');
	if($xo['type'] == 1){
		systemPostChat($data['user_roomid'], $like_msg); 
	}else{
		postPrivate($data["user_id"], $xo['uid'], $like_msg, 1);
	}
   }
   
   echo 1;
   die();
}
if(isset($_POST['EndXO'],$_POST['id'])){
	$id = escape($_POST['id']);
	$mysqli->query("DELETE FROM ps_box_xo WHERE tid = '{$data['user_id']}' AND id = '$id'");
	echo 1;
	die();
}
if(isset($_POST['EndUserXO'],$_POST['id'])){
	$id = escape($_POST['id']);
	$mysqli->query("UPDATE ps_box_xo SET uid = '' WHERE id = '$id'");
	echo 1;
	die();
}
if(isset($_POST['set_Like_access'])){
    $rank = escape($_POST['set_Like_access']);
    if(!boomAllow(9)){
        die();
    }
	$mysqli->query("UPDATE boom_addons SET addons_access = '$rank' WHERE addons = 'AA_profile_like'");
	echo 5;
	die();
}
if(isset($_POST['saveMoveXouser'],$_POST['id'],$_POST['uid'])){
	$id = escape($_POST['id']);
	$uid = escape($_POST['uid']);
	$mysqli->query("UPDATE ps_box_xo SET $id = 'o' ,turn = '$uid' WHERE id = '{$data['user_Xo']}'");
	echo 1;
	die();
}
if(isset($_POST['saveMoveXo'],$_POST['id'],$_POST['uid'])){
	$id = escape($_POST['id']);
	$uid = escape($_POST['uid']);
	$mysqli->query("UPDATE ps_box_xo SET $id = 'x' ,turn = '$uid' WHERE id = '{$data['user_Xo']}'");
	echo 1;
	die();
}
if(isset($_POST['startXoUser'],$_POST['id'])){
	$id = escape($_POST['id']);
	$xo = getDataXo($id);
	if($xo['uid'] != 0){
		die();
	}
	$mysqli->query("UPDATE boom_users SET user_Xo = '$id' WHERE user_id = '{$data['user_id']}'");
    $mysqli->query("UPDATE ps_box_xo SET uid = '{$data['user_id']}' WHERE id = '$id'");
	echo 1;
	die();
}
if(isset($_POST['startXO'])){
	$mysqli->query("INSERT INTO `ps_box_xo` (tid,turn,type) VALUES ('{$data['user_id']}','{$data['user_id']}',1)");
	$xos = $mysqli->insert_id;
	$mysqli->query("UPDATE boom_users SET user_Xo = '$xos' WHERE user_id = '{$data['user_id']}'");
	$like_msg = str_replace(array('%data%'), array($data['user_name']) , ' '.$lang['user_start_public'].' <b onclick="userBoxXO('.$xos.','.$data['user_id'].');" class="p_Xred">'.$lang['user_start_click'].'</b> %data%');
	systemPostChat($data['user_roomid'], $like_msg);
	echo 1;
	die();
}
if(isset($_POST['startXOPriv'],$_POST['target'])){
	$mysqli->query("INSERT INTO `ps_box_xo` (tid,turn,type) VALUES ('{$data['user_id']}','{$data['user_id']}',2)");
	$xos = $mysqli->insert_id;
	$mysqli->query("UPDATE boom_users SET user_Xo = '$xos' WHERE user_id = '{$data['user_id']}'");
	$target = escape($_POST['target']);
	$hunter = $data["user_id"];
	$like_msg =  ''.$lang['user_start'].' <b onclick="userBoxXO('.$xos.','.$data['user_id'].');" class="p_Xred">'.$lang['user_start_click'].'</b>';
	echo postPrivate($hunter, $target, $like_msg, 1);
	die();
}
?>
