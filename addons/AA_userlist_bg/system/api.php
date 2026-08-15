<?php
$load_addons = 'AA_userlist_bg';
require_once('../../../system/config_addons.php');

$list_bg = array();
$find_bg = $mysqli->query("SELECT user_id, bg_file FROM userlist_bg");
if($find_bg && $find_bg->num_rows > 0){
	while($row = mysqli_fetch_object($find_bg)){
		array_push($list_bg, $row);
	}
}
if($find_bg){
	mysqli_free_result($find_bg);
}

echo json_encode(array('bgUser' => $list_bg), JSON_HEX_TAG);
die();
?>
