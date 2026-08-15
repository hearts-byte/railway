<?php
$load_addons = 'youtube';
require(__DIR__ . '/../../../system/config_addons.php');

function saveYoutube(){
	global $mysqli, $data, $lang;
	$youtube_access = escape($_POST['set_youtube_access'], true);
	$youtube_key = escape($_POST['set_youtube_key']);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$youtube_access', custom1 = '$youtube_key' WHERE addons = 'youtube'");
	redisUpdateAddons('youtube');
	return 5;
}
if(isset($_POST['set_youtube_access'], $_POST['set_youtube_key']) && canManageAddons()){
	echo saveYoutube();
	die();
}
else {
	die();
}
?>