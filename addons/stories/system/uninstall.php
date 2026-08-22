<?php
if (!defined('IN_SCRIPT')) { define('IN_SCRIPT', true); }
require_once __DIR__ . '/config.php';

function stories_uninstall($drop_tables = true)
{
    $db = stories_db();
    if ($drop_tables) {
        $db->query('DROP TABLE IF EXISTS `cody_stories_reactions`');
        $db->query('DROP TABLE IF EXISTS `cody_stories_views`');
        $db->query('DROP TABLE IF EXISTS `cody_stories`');
    }
    return array('success' => true);
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'uninstall.php') {
    $result = stories_uninstall();
    echo $result['success'] ? 'تم حذف إضافة الستوريات.' : 'حدث خطأ أثناء إلغاء التثبيت.';
}
