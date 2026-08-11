<?php
// debug_action.php
// مؤقت — لا تتركه نشطاً بعد الانتهاء. يحفظ POST/GET/COOKIE/SESSION والـ headers في ملف لوق.
session_start();
header('Content-Type: text/plain; charset=utf-8');
$entry = [
    'time'    => date('c'),
    'remote'  => $_SERVER['REMOTE_ADDR'] ?? '',
    'method'  => $_SERVER['REQUEST_METHOD'] ?? '',
    'url'     => ($_SERVER['REQUEST_URI'] ?? ''),
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
    'cookie'  => $_COOKIE,
    'get'     => $_GET,
    'post'    => $_POST,
    'session' => isset($_SESSION) ? $_SESSION : [],
    'server'  => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? ''
    ]
];

$logPath = __DIR__ . '/debug_action_log.txt';
$logEntry = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($logPath, $logEntry . "\n---\n", FILE_APPEND);

// عند فتح ?show=log نطبع اللوغ كامل (للاطلاع السريع في المتصفح)
if (isset($_GET['show']) && $_GET['show'] === 'log') {
    // أمان: لا تعرض هذا الرابط للعامة
    if (file_exists($logPath)) {
        echo file_get_contents($logPath);
    } else {
        echo "no log yet\n";
    }
    exit;
}

// وإلا نطبع آخر مدخل لمعاينة سريعة
echo $logEntry;
exit;
?>
