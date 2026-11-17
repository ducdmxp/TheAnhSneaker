<?php
/**
 * Kết nối CSDL. Thông tin đăng nhập đọc từ config.php (không nằm trong git).
 * Xem config.example.php để biết cách tạo config.php.
 */
require_once __DIR__ . '/includes/config.php';

$config = app_config();
$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']),
    $config['db_user'],
    $config['db_pass'],
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);
