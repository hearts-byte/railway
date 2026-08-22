<?php
$load_addons = 'stories';
require_once('../../../system/config_addons.php');
if (!boomAllow(9)) {
    die();
}
require_once __DIR__ . '/functions.php';

stories_save_settings($_POST);
echo json_encode(array('success' => true), JSON_UNESCAPED_UNICODE);
