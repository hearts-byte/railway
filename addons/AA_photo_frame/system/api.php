<?php
$load_addons = 'AA_photo_frame';
require_once('../../../system/config_addons.php');
$user_Frame      = array();
$find_user_Frame = $mysqli->query("SELECT photo_frames.* FROM photo_frames");
if ($find_user_Frame->num_rows > 0) {
    while ($row = mysqli_fetch_object($find_user_Frame)) {
        array_push($user_Frame, $row);
    }
}
mysqli_free_result($find_user_Frame);

// json.
$jsonco = [
    'FrameUser' => $user_Frame
];
echo json_encode($jsonco, JSON_HEX_TAG);