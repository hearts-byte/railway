<?php
if (!defined('IN_SCRIPT')) { define('IN_SCRIPT', true); }
require_once __DIR__ . '/config.php';

function stories_install()
{
    $db = stories_db();

    $sql_stories = "CREATE TABLE IF NOT EXISTS `cody_stories` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED NOT NULL,
        `type` ENUM('text','image','video') NOT NULL DEFAULT 'text',
        `content` TEXT NOT NULL,
        `bg_color` VARCHAR(20) NOT NULL DEFAULT '#6c5ce7',
        `text_color` VARCHAR(20) NOT NULL DEFAULT '#ffffff',
        `views` INT UNSIGNED NOT NULL DEFAULT 0,
        `gold_cost` INT UNSIGNED NOT NULL DEFAULT 0,
        `status` TINYINT NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `expires_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_user` (`user_id`),
        KEY `idx_expires` (`expires_at`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $sql_views = "CREATE TABLE IF NOT EXISTS `cody_stories_views` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `story_id` INT UNSIGNED NOT NULL,
        `viewer_id` INT UNSIGNED NOT NULL,
        `viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_view` (`story_id`,`viewer_id`),
        KEY `idx_story` (`story_id`),
        CONSTRAINT `fk_stories_views_story` FOREIGN KEY (`story_id`) REFERENCES `cody_stories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $sql_reactions = "CREATE TABLE IF NOT EXISTS `cody_stories_reactions` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `story_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `type` ENUM('emoji','message') NOT NULL DEFAULT 'emoji',
        `content` VARCHAR(255) NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_story_reaction` (`story_id`),
        CONSTRAINT `fk_stories_reactions_story` FOREIGN KEY (`story_id`) REFERENCES `cody_stories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    foreach (array($sql_stories, $sql_views, $sql_reactions) as $query) {
        if (!$db->query($query)) {
            return array('success' => false, 'error' => $db->error);
        }
    }

    if (!is_dir(STORIES_UPLOAD_DIR)) {
        @mkdir(STORIES_UPLOAD_DIR, 0755, true);
    }
    // تعطيل تنفيذ PHP داخل مجلد الرفع كطبقة حماية إضافية ضد رفع ملفات ضارة
    @file_put_contents(STORIES_UPLOAD_DIR . '.htaccess', "php_flag engine off\n");
    @file_put_contents(STORIES_UPLOAD_DIR . 'index.php', "<?php header('Location: /'); exit;");

    return array('success' => true);
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'install.php') {
    $result = stories_install();
    echo $result['success']
        ? 'تم تثبيت إضافة الستوريات بنجاح.'
        : 'حدث خطأ أثناء التثبيت: ' . $result['error'];
}
