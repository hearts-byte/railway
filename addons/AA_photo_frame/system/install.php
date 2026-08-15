<?php
if(!defined('BOOM')){
	die();
}
if(boomAllow(10)){
	$ad = array(
	'name' => 'AA_photo_frame',
	'access'=> 11,
	);
}
$mysqli->query("CREATE TABLE IF NOT EXISTS `photo_frames` (
	`frame_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`frame_name` varchar(100) NOT NULL,
	PRIMARY KEY (`frame_id`))");
?>