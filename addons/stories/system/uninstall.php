<?php
if (!defined('BOOM')) {
  die();
}

$ad = array(
	'name' => 'stories',
	'access' => 0,
);

$mysqli->query("DROP TABLE IF EXISTS `cody_stories_reactions`;");
$mysqli->query("DROP TABLE IF EXISTS `cody_stories_views`;");
$mysqli->query("DROP TABLE IF EXISTS `cody_stories`;");
