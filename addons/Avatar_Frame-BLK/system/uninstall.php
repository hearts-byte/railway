<?php
if (!defined('BOOM')){
  die();
}
//$mysqli->query("DROP TABLE `avatar_frame`");
$mysqli->query("ALTER TABLE `boom_users` DROP `avatar_frame` ");
?>