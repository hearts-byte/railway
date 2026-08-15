<?php
$load_addons = "AA_photo_frame";
require_once "../../../system/config_addons.php";
$user_Frame = [];
$find_user_Frame = $mysqli->query("SELECT photo_frames.* FROM photo_frames");
if (0 < $find_user_Frame->num_rows) {
    while ($row = mysqli_fetch_object($find_user_Frame)) {
        array_push($user_Frame, $row);
    }
}
mysqli_free_result($find_user_Frame);
$chat_det = [];
$find_chat_det = $mysqli->query("SELECT user_id FROM boom_chat");
if (0 < $find_chat_det->num_rows) {
    while ($row = mysqli_fetch_object($find_chat_det)) {
        array_push($chat_det, $row);
    }
}
mysqli_free_result($find_chat_det);
$jsonco = ["FrameUser" => $user_Frame, "chatDetails" => $chat_det];
echo json_encode($jsonco, JSON_HEX_TAG);

?>