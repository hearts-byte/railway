<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat

if (!defined('BOOM')){
  die();
}

$ad = array(
	'name' => 'roll_the_dice',
	'bot_name'=> 'Dice Bot',
	'bot_type'=> 2,
	'custom1'=> 0,
	'custom2'=> 0,
	'custom3'=> 1
);

$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_dicegame_scores` (
				`dicegame_id` int(10) NOT NULL AUTO_INCREMENT,
				`winner` int(11) NOT NULL,
				`looser` int(11) NOT NULL,
				PRIMARY KEY (`dicegame_id`)
				) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1");
	
?>