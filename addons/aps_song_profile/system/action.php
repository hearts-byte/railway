<?php
$load_addons = 'aps_song_profile';
require_once('../../../system/config_addons.php');

if (isset($_POST["get_profile_music"]) && isset($_POST["target"])) {
    echo get_profile_music();
    exit;
}
if (isset($_POST["edit_profile_music"]) && boomAllow($addons["addons_access"])) {
    echo edit_profile_music();
    exit;
}
if (isset($_POST["save_profile_music"]) && isset($_FILES["file"]) && boomAllow($addons["addons_access"])) {
    echo save_profile_music();
    exit;
}
if (isset($_POST["remove_profile_music"]) && boomAllow($addons["addons_access"])) {
    echo remove_profile_music();
    exit;
}
if (isset($_POST["save_settings"]) && boomAllow(11)) {
    echo save_settings();
    exit;
}

function save_settings(){
    global $addons;
    global $mysqli;
    $addon_access = escape($_POST["set_addon_access"]);
    $set_size = escape($_POST["set_size"]);
    $mysqli->query("UPDATE boom_addons set addons_access = '" . $addon_access . "', custom1 = '" . $set_size . "'\tWHERE addons = '" . $addons["addons"] . "' ");
    return 1;
}
function save_profile_music()
{
    global $data;
    global $addons;
    global $mysqli;
    if (0 < $_FILES["file"]["error"] || $addons["custom1"] < $_FILES["file"]["size"] / 1024 / 1024) {
        return 0;
    }
    $info = pathinfo($_FILES["file"]["name"]);
    $extension = $info["extension"];
    $f = ["audio/x-aiff", "audio/x-aac", "audio/ogg", "audio/midi", "audio/mpeg", "audio/m4a", "audio/x-ms-wma", "audio/mp4", "audio/mp3", "audio/x-mpeg", "audio/x-mp3", "audio/mpeg3", "audio/x-mpeg3", "audio/mpg", "audio/x-mpg", "audio/x-wav", "audio/x-mpegaudio", "audio/flac"];
    if (in_array($_FILES["file"]["type"], $f)) {
        $file_name = encodeFile($extension);
        move_uploaded_file($_FILES["file"]["tmp_name"], BOOM_PATH . "/upload/upload/" . $file_name);
        $mysqli->query("UPDATE boom_users SET profile_music = '" . $file_name . "' WHERE user_id = '" . $data["user_id"] . "'");
        return 1;
    }
    return 0;
}
function edit_profile_music()
{
    global $data;
    global $mysqli;
    return addonTemplate("edit_profile_music");
}
function get_profile_music()
{
    global $addons;
    global $mysqli;
    $target = escape($_POST["target"]);
    $user = userDetails($target);
    if (empty($user)) {
        return 0;
    }
    if (!empty($user["profile_music"]) && $addons["addons_access"] <= $user["user_rank"]) {
        return addonTemplate("get_profile_music", $user);
    }
    return 0;
}
function remove_profile_music()
{
    global $data;
    global $mysqli;
    if (!empty($data["profile_music"])) {
        unlinkUpload("upload", $data["profile_music"]);
        $mysqli->query("UPDATE boom_users SET profile_music = '' WHERE user_id = '" . $data["user_id"] . "'");
    }
    return 1;
}

?>
