<?php
if(!defined('BOOM')){
	die();
}

$mysqli->query("INSERT INTO `boom_addons` (`addons`, `addons_load`, `addons_access`, `custom1`) VALUES ('aps_song_profile', '1', '1', '5')");

$mysqli->query("ALTER TABLE `boom_users` ADD profile_music varchar(200) NOT NULL DEFAULT ''");
?>
