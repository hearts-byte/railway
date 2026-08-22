<?php
if (!defined('BOOM')) {
    die();
}

$ad = array(
    'name'   => 'الستوريات (Stories)',
    'access' => 0,
);

$mysqli->query("CREATE TABLE IF NOT EXISTS `cody_stories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS `cody_stories_views` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `story_id` INT UNSIGNED NOT NULL,
    `viewer_id` INT UNSIGNED NOT NULL,
    `viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_view` (`story_id`,`viewer_id`),
    KEY `idx_story` (`story_id`),
    CONSTRAINT `fk_stories_views_story` FOREIGN KEY (`story_id`) REFERENCES `cody_stories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS `cody_stories_reactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `story_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('emoji','message') NOT NULL DEFAULT 'emoji',
    `content` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_story_reaction` (`story_id`),
    CONSTRAINT `fk_stories_reactions_story` FOREIGN KEY (`story_id`) REFERENCES `cody_stories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stories_upload_dir = dirname(__DIR__, 3) . '/uploads/stories/';
if (!is_dir($stories_upload_dir)) {
    @mkdir($stories_upload_dir, 0755, true);
}
// تعطيل تنفيذ PHP داخل مجلد الرفع كطبقة حماية إضافية ضد رفع ملفات ضارة
@file_put_contents($stories_upload_dir . '.htaccess', "php_flag engine off\n");
@file_put_contents($stories_upload_dir . 'index.php', "<?php header('Location: /'); exit;");
?>
