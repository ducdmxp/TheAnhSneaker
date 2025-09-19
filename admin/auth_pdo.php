<?php
session_start();
// Chú ý: Đường dẫn đến file kết nối cần đi ra ngoài 1 cấp
require_once '../db_connect_pdo.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        
        // --- THAY ĐỔI QUAN TRỌNG ---
        // Chuyển hướng đến trang quản lý sản phẩm thay vì dashboard
        header('Location: index.php'); 
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
}
?>