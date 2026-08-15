<?php
$load_addons = 'AA_userlist_glow';
require_once('../../../system/config_addons.php');

$user_glow = array();
$find_user_glow = $mysqli->query("SELECT user_id, glow_color FROM userlist_glow");
if($find_user_glow && $find_user_glow->num_rows > 0){
	while($row = mysqli_fetch_object($find_user_glow)){
		array_push($user_glow, $row);
	}
}
if($find_user_glow){
	mysqli_free_result($find_user_glow);
}

echo json_encode(array('glowUser' => $user_glow), JSON_HEX_TAG);
die();
?>
