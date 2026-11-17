<?php
/**
 * Đọc cấu hình bí mật từ config.php ở thư mục gốc (không nằm trong git).
 * Xem config.example.php để biết các khóa.
 */
function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $file = dirname(__DIR__) . '/config.php';
        if (!is_file($file)) {
            throw new RuntimeException('Chưa có config.php. Hãy copy config.example.php thành config.php và điền thông tin database.');
        }
        $config = require $file;
    }
    return $config;
}

/** Lấy một khóa cấu hình, không ném lỗi nếu thiếu file/khóa. */
function config_value(string $key, $default = '')
{
    try {
        return app_config()[$key] ?? $default;
    } catch (Throwable $e) {
        return $default;
    }
}
