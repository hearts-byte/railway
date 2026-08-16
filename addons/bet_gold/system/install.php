<?php
if(!defined('BOOM')){
 die();
}
$result = $mysqli->query("SELECT * FROM boom_addons WHERE addons = 'bet_gold'");
if ($result->num_rows > 0) {
    echo '<h1 style="color:green;text-align:center">This Addons is already installed.</h1>'; 
    die();
}
$now = time();
$mysqli->query("insert into boom_addons (addons, addons_load, addons_access, custom1) values ('bet_gold', 0, 100, 0)");
echo '<h1 style="color:green;text-align:center">The Addons has been successfully installed.</h1>';
?>