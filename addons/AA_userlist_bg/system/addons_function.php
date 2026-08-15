<?php
function getMyListBg($id){
	global $mysqli;
	$id = (int) $id;
	$bg = array('bg_file' => '');
	$get = $mysqli->query("SELECT bg_file FROM userlist_bg WHERE user_id = '$id' LIMIT 1");
	if($get && $get->num_rows > 0){
		$bg = $get->fetch_assoc();
	}
	return $bg;
}
?>
