<?php

$load_addons = "AA_photo_frame";
require_once "../../../system/config_addons.php";
if (isset($_POST["set_addon_access"]) && isset($_POST["set_addon_access_staff"])) {
    echo exdynmthisaddon1();
    exit;
}
if (isset($_POST["frame_target_staff"]) && isset($_POST["set_photo_frame_staff"])) {
    echo exdynmthisaddon2();
    exit;
}
if (isset($_POST["del_frame_target_staff"])) {
    echo exdynmthisaddon4();
    exit;
}
if (isset($_POST["set_photo_frame"])) {
    echo exdynmthisaddon3();
    exit;
}
if (isset($_POST["del_frame_target"])) {
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
    $mysqli->query("UPDATE boom_addons SET addons_access = '" . $rank . "', custom1 = '" . $staff . "' WHERE addons = 'AA_photo_frame'");
    return 1;
}

function exDynmThisAddon2()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $target = escape($_POST["frame_target_staff"]);
    $frame = escape($_POST["set_photo_frame_staff"]);
    if (!boomAllow($data["addons_access"]) || !boomAllow($data["custom1"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM photo_frames WHERE user_id = '" . $target . "'");
    if ($result->num_rows == 0) {
        $mysqli->query("INSERT INTO `photo_frames` (user_id, frame_name) VALUES ('" . $target . "', '" . $frame . "')");
        echo 1;
        exit;
    }
    $mysqli->query("UPDATE photo_frames SET frame_name = '" . $frame . "' WHERE user_id = '" . $target . "'");
    echo 1;
    exit;
}

function exDynmThisAddon4()
{
    global $mysqli;
    global $data;
    global $lang;
    global $cody;
    $target = escape($_POST["del_frame_target_staff"]);
    if (!boomAllow($data["addons_access"]) || !boomAllow($data["custom1"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM photo_frames WHERE user_id = '" . $target . "'");
    if (0 < $result->num_rows) {
        $mysqli->query("DELETE FROM photo_frames WHERE user_id = '" . $target . "'");
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

    $frame = escape($_POST["set_photo_frame"]);
    if (!boomAllow($data["addons_access"])) {
        exit;
    }
    $result = $mysqli->query("SELECT * FROM photo_frames WHERE user_id = '" . $data["user_id"] . "'");
    if ($result->num_rows == 0) {
        $mysqli->query("INSERT INTO `photo_frames` (user_id, frame_name) VALUES ('" . $data["user_id"] . "', '" . $frame . "')");
        echo 1;
        exit;
    }
    $mysqli->query("UPDATE photo_frames SET frame_name = '" . $frame . "' WHERE user_id = '" . $data["user_id"] . "'");
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
    $result = $mysqli->query("SELECT * FROM photo_frames WHERE user_id = '" . $data["user_id"] . "'");
    if (0 < $result->num_rows) {
        $mysqli->query("DELETE FROM photo_frames WHERE user_id = '" . $data["user_id"] . "'");
        echo 1;
        exit;
    }
    echo 2;
    exit;
}

?>