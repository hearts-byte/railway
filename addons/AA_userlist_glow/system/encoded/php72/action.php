<?php


$load_addons = "AA_userlist_glow";
require_once "../../../system/config_addons.php";
if (isset($_POST["set_addon_access"]) && isset($_POST["set_addon_access_staff"])) {
    echo exdynmthisaddon1();
    exit;
}
if (isset($_POST["user_target"]) && isset($_POST["user_list_glow"])) {
    echo exdynmthisaddon2();
    exit;
}
if (isset($_POST["user_target"]) && isset($_POST["del_user_list_glow"])) {
    echo exdynmthisaddon4();
    exit;
}
if (isset($_POST["user_list_glow_user"])) {
    echo exdynmthisaddon3();
    exit;
}
if (isset($_POST["del_user_list_glow_user"])) {
    echo exdynmthisaddon5();
    exit;
}
function exDynmThisAddon1()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $rank = escape($_POST["set_addon_access"]);
    $staff = escape($_POST["set_addon_access_staff"]);
    if (!boomAllow(10)) {
        exit;
    }
    $mysqli->query("UPDATE boom_addons SET addons_access = '" . $rank . "', custom1 = '" . $staff . "' WHERE addons = 'AA_userlist_glow'");
    return 1;
}
function exDynmThisAddon2()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $target = escape($_POST["user_target"]);
    $glow = escape($_POST["user_list_glow"]);
    if (!boomAllow($data["addons_access"]) || !boomAllow($data["custom1"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM userlist_glow WHERE user_id = '" . $target . "'");
    if ($result->num_rows == 0) {
        $mysqli->query("INSERT INTO `userlist_glow` (user_id, glow_color) VALUES ('" . $target . "', '" . $glow . "')");
        echo 1;
        exit;
    }
    $mysqli->query("UPDATE userlist_glow SET glow_color = '" . $glow . "' WHERE user_id = '" . $target . "'");
    echo 1;
    exit;
}
function exDynmThisAddon4()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $target = escape($_POST["user_target"]);
    if (!boomAllow($data["addons_access"]) || !boomAllow($data["custom1"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM userlist_glow WHERE user_id = '" . $target . "'");
    if (0 < $result->num_rows) {
        $mysqli->query("DELETE FROM userlist_glow WHERE user_id = '" . $target . "'");
        echo 1;
        exit;
    }
    echo 2;
    exit;
}
function exDynmThisAddon3()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $glow = escape($_POST["user_list_glow_user"]);
    if (!boomAllow($data["addons_access"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM userlist_glow WHERE user_id = '" . $data["user_id"] . "'");
    if ($result->num_rows == 0) {
        $mysqli->query("INSERT INTO `userlist_glow` (user_id, glow_color) VALUES ('" . $data["user_id"] . "', '" . $glow . "')");
        echo 1;
        exit;
    }
    $mysqli->query("UPDATE userlist_glow SET glow_color = '" . $glow . "' WHERE user_id = '" . $data["user_id"] . "'");
    echo 1;
    exit;
}
function exDynmThisAddon5()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    if (!boomAllow($data["addons_access"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM userlist_glow WHERE user_id = '" . $data["user_id"] . "'");
    if (0 < $result->num_rows) {
        $mysqli->query("DELETE FROM userlist_glow WHERE user_id = '" . $data["user_id"] . "'");
        echo 1;
        exit;
    }
    echo 2;
    exit;
}

?>