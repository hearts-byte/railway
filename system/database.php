<?php
// base system prefix
define('BOOM_PREFIX', 'boom_');

// optional base domain (ضع رابط موقعك على Railway)
define('BOOM_DOMAIN', 'https://sh3d.up.railway.app/');

// default redis configuration
// default redis configuration
define('REDIS_IP', 'redis.railway.internal');
define('REDIS_PORT', 6379);
define('REDIS_TIMEOUT', 0.2);
define('REDIS_PASS', 'opgumkrQxBOsndRHxjKoscnSdQentiPC');

// database configuration
define('BOOM_DHOST', 'mysql.railway.internal');
define('BOOM_DUSER', 'root');
define('BOOM_DPASS', 'oYkYKuvezhlSXJnEjXInupTdfLVvQYMw');
define('BOOM_DNAME', 'railway');

// base system main path do not modify
define('BOOM_PATH', dirname(__DIR__));

// do not modify those variables
define('BOOM_CRYPT', 'a8f9D2xL0!mP7$qZ');
define('BOOM_INSTALL', 1);
define('BOOM', 1);
?>
