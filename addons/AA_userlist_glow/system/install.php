<?php
if(!defined('BOOM')){
	die();
}
if(boomAllow(10)){
	$ad = array(
	'name' => 'AA_userlist_glow',
	'access'=> 11,
	'custom1' => 11,
	);
}
$mysqli->query("CREATE TABLE IF NOT EXISTS `userlist_glow` (
	`glow_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`glow_color` varchar(100) NOT NULL,
	PRIMARY KEY (`glow_id`),
	UNIQUE KEY `user_id` (`user_id`))");
?>
