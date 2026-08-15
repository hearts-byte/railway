<?php
function getMyGlow($id){
	global $mysqli;
	$id = (int) $id;
	$glow = array('glow_color' => '');
	$getuser = $mysqli->query("SELECT * FROM userlist_glow WHERE user_id = '$id'");
	if($getuser && $getuser->num_rows > 0){
		$glow = $getuser->fetch_assoc();
	}
	return $glow;
}
