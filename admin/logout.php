<?php
require_once __DIR__ . '/security.php';
admin_session_start();

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
