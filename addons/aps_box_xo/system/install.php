<?php
if (!defined('BOOM')){
  die();
}

$ad = array(
	'name' => 'aps_box_xo',
	'access' => 0,
);
$mysqli->query("ALTER TABLE `boom_users` ADD `user_Xo` INT(11) NULL DEFAULT '0';");
$mysqli->query("CREATE TABLE `ps_box_xo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `turn` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `win_id` int(11) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL DEFAULT 0,
  `A1` varchar(5) NOT NULL DEFAULT '',
  `A2` varchar(5) NOT NULL DEFAULT '',
  `A3` varchar(5) NOT NULL DEFAULT '',
  `A4` varchar(5) NOT NULL DEFAULT '',
  `A5` varchar(5) NOT NULL DEFAULT '',
  `A6` varchar(5) NOT NULL DEFAULT '',
  `A7` varchar(5) NOT NULL DEFAULT '',
  `A8` varchar(5) NOT NULL DEFAULT '',
  `A9` varchar(5) NOT NULL DEFAULT '',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
?>