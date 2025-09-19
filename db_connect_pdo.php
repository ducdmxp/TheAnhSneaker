<?php
// Thông tin kết nối CSDL
$host = 'localhost';
$dbname = 'theanhgi_data'; // Thay bằng tên database của bạn
$user = 'root';          // Thay bằng username database của bạn
$pass = '';              // Thay bằng mật khẩu database của bạn
$charset = 'utf8mb4';

// Cấu hình DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Cấu hình các tùy chọn cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi khi có lỗi
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về dạng mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Tạo một đối tượng PDO mới
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Nếu kết nối thất bại, hiển thị lỗi và dừng chương trình
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>