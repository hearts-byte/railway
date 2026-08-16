<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat

$load_addons = 'roll_the_dice';
require('../../../system/config_addons.php');


// config
if(isset($_POST['status']) && isset($_POST['room']) && isset($_POST['show_scores']) && boomAllow(10)){
	$status = $_POST['status'];
	$room = $_POST['room'];
	$showScores = $_POST['show_scores'];
	$mysqli->query("UPDATE boom_addons SET custom1 = '$room' , custom2 = '$status', custom3 = '$showScores' WHERE addons = '$load_addons'");
	echo 5;
	die();
}

if(isset($_POST['reset_score']) && boomAllow(10)){
	$mysqli->query("DELETE FROM boom_dicegame_scores");
	echo 1;
	die();
}

// game
if(isset($_POST["user"]) && ($addons['custom2'] == 1) && ($data['user_roomid'] == $addons['custom1']) ){
	rollTheDice($_POST["user"]);
	die();
}


?>