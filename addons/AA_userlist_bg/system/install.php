<?php
if(!defined('BOOM')){
	die();
}
if(boomAllow(10)){
	$ad = array(
		'name' => 'AA_userlist_bg',
		'access'=> 11,
		'custom1' => 11,
	);
}
$mysqli->query("CREATE TABLE IF NOT EXISTS `userlist_bg` (
	`bg_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`bg_file` varchar(160) NOT NULL,
	`bg_date` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`bg_id`),
	UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
?>
