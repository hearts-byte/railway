<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2
 * @ Decoder version: 1.0.4
 * @ Release: 01/09/2021
 */

$load_addons = "AA_userlist_glow";
require_once "../../../system/config_addons.php";
$user_glow = [];
$find_user_glow = $mysqli->query("SELECT userlist_glow.* FROM userlist_glow");
if (0 < $find_user_glow->num_rows) {
    while ($row = mysqli_fetch_object($find_user_glow)) {
        array_push($user_glow, $row);
    }
}
mysqli_free_result($find_user_glow);
$jsonco = ["glowUser" => $user_glow];
echo json_encode($jsonco, JSON_HEX_TAG);

?>