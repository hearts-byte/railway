<?php
if(!defined('BOOM')){
	die();
}
if(boomAllow(10)){
	$ad = array(
	'name' => 'AA_global_notify',
	'access'=> 90,
	'custom1' => 0,
	);
}
$mysqli->query("CREATE TABLE IF NOT EXISTS `aa_global_notify_log` (
	`log_id` int(11) NOT NULL AUTO_INCREMENT,
	`sender_id` int(11) NOT NULL,
	`publish_as` varchar(10) NOT NULL DEFAULT 'system',
	`message` text NOT NULL,
	`recipients` int(11) NOT NULL DEFAULT 0,
	`log_date` int(11) NOT NULL,
	PRIMARY KEY (`log_id`))");
?>
