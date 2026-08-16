<?php
if(!defined('BOOM')){
	die();
}

$ad = array(
	'name' => 'aps_song_profile',
	'access'=> 1,
	'custom1'=> 5,
);

$check_column = $mysqli->query("SHOW COLUMNS FROM `boom_users` LIKE 'profile_music'");
if($check_column && $check_column->num_rows == 0){
	$mysqli->query("ALTER TABLE `boom_users` ADD profile_music varchar(200) NOT NULL DEFAULT ''");
}
?>
