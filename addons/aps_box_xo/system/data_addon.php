<?php
$load_addons = 'aps_box_xo';
require_once('../../../system/config_addons.php');
$xo = getDataXo($data['user_Xo']);
$user  = userDetails($xo['uid'] ?? 0);
$userw = userDetails($xo['win_id'] ?? 0);
if(!is_array($user))  { $user  = array(); }
if(!is_array($userw)) { $userw = array(); }
echo json_encode( array(
	"my"=> $data['user_name'],
	"user"=> $user['user_name'] ?? '',
	"A1"=> $xo['A1'] ?? '',
	"A2"=> $xo['A2'] ?? '',
	"A3"=> $xo['A3'] ?? '',
	"A4"=> $xo['A4'] ?? '',
	"A5"=> $xo['A5'] ?? '',
	"A6"=> $xo['A6'] ?? '',
	"A7"=> $xo['A7'] ?? '',
	"A8"=> $xo['A8'] ?? '',
	"A9"=> $xo['A9'] ?? '',
	"turn"=> $xo['turn'] ?? 0,
	"uid"=> $xo['uid'] ?? 0,
	"win"=> $xo['win'] ?? 0,
	"win_user"=> $userw['user_name'] ?? '',
), JSON_UNESCAPED_UNICODE);
die();
?>
