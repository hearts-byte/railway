<?php
if(!defined('BOOM')){
	die();
}

$ad = array(
	'name' => 'aps_song_profile',
	'access'=> 1,
	'custom1'=> 5,
);

$mysqli->query("ALTER TABLE `boom_users` ADD profile_music varchar(200) NOT NULL DEFAULT ''");
?>