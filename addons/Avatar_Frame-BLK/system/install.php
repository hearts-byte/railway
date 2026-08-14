<?php
if (!defined('BOOM')){
  die();
}
$ad = array(
  'name' => basename(dirname(dirname(__FILE__))),
  'access' => 100,
  'custom1' => 70,
);
function insertFrames(){
	global $mysqli;
	$mysqli->query("ALTER TABLE `boom_users` ADD `avatar_frame` int(11) NOT NULL DEFAULT 0; ");
	$mysqli->query("CREATE TABLE IF NOT EXISTS `avatar_frame` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`price` int(11) NOT NULL DEFAULT 0,
	`rank` int(11) NOT NULL DEFAULT 1,
	`method` int(11) NOT NULL DEFAULT 1,
	`tumb` varchar(200) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1");

	$path = BOOM_PATH . '/addons/'.basename(dirname(dirname(__FILE__))).'/files/frames/';
	$dir = glob($path.'*');
	foreach($dir as $file) { 
		$file_tumb = str_replace($path, '', $file);
		$mysqli->query("INSERT INTO `avatar_frame` (`price`, `tumb`) VALUES ('10','$file_tumb')");
	}
}
insertFrames();
?>