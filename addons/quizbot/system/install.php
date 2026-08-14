<?php
if(!defined('BOOM')){
	die();
}
$ad = array(
	'name' => 'quizbot',
	'bot_name'=> 'Quizbot',
	'bot_type'=> 2,
	'custom1'=> 0,
	'custom2'=> 0,
	'custom3'=> 1,
	'custom4'=> 'English1.txt',
	'custom5'=> 'Scramble_english.txt'
);
$mysqli->query("
	CREATE TABLE IF NOT EXISTS `boom_quiz` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`question` varchar(2000) NOT NULL DEFAULT '',
	`answer` varchar(2000) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1
");
				
$mysqli->query("ALTER TABLE `boom_users` ADD quiz_score int(11) NOT NULL DEFAULT '0'");
$mysqli->query("INSERT INTO boom_quiz (id) VALUES (1)");
?>