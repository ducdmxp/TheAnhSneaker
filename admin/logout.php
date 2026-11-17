<?php
require_once __DIR__ . '/security.php';
admin_session_start();

// Chỉ đăng xuất bằng POST có token, tránh trang lạ đăng xuất bạn bằng một đường link.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: index.php');
    exit;
}
require_post_with_csrf();

$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $params['path'],
    'secure' => $params['secure'],
    'httponly' => true,
    'samesite' => $params['samesite'] ?: 'Lax',
]);
session_destroy();

header('Location: login.php');
exit;
