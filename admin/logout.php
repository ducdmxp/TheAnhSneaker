<?php
session_start();

// Hủy tất cả các biến session
$_SESSION = [];

// Hủy session
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập
header('Location: login.php');
exit;
?>