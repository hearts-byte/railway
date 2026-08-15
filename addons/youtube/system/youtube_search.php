<?php
$load_addons = 'youtube';
require('../../../system/config_addons.php');

if(mainBlocked()){
	die();
}

session_write_close();

if(!isset($_POST['youtube_search'], $_POST['type'])){
	die();
}

$content = escape($_POST['youtube_search']);
$type = escape($_POST['type'], true);

if($content == ''){
	die();
}
if(!boomAllow($addons['addons_access'])){
	die();
}

$content = urlencode($content);
$youcall = doCurl('https://www.googleapis.com/youtube/v3/search?part=snippet,id&q=' . $content . '&maxResults=8&type=video&key=' . $addons['custom1']);
echo $youcall;
die();
?>