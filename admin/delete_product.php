<?php
// Bắt đầu session và kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Kết nối CSDL bằng PDO
require_once '../db_connect_pdo.php';

// Kiểm tra xem ID sản phẩm có được gửi đến không
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // --- BƯỚC 1: Lấy đường dẫn ảnh trước khi xóa khỏi DB ---
        $stmt_select = $pdo->prepare("SELECT image_url FROM products WHERE id = :id");
        $stmt_select->execute([':id' => $id]);
        $product = $stmt_select->fetch();

        if ($product) {
            $image_path = '../' . $product['image_url'];

            // --- BƯỚC 2: Xóa file ảnh khỏi server ---
            // Kiểm tra xem file có tồn tại không trước khi xóa
            if (file_exists($image_path)) {
                unlink($image_path); // Hàm xóa file
            }

            // --- BƯỚC 3: Xóa sản phẩm khỏi cơ sở dữ liệu ---
            $stmt_delete = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt_delete->execute([':id' => $id]);

            // --- BƯỚC 4: Chuyển hướng về trang danh sách ---
            header("Location: index.php");
            exit();
        } else {
            die("Lỗi: Không tìm thấy sản phẩm để xóa.");
        }

    } catch (PDOException $e) {
        die("Lỗi CSDL: " . $e->getMessage());
    }
} else {
    // Nếu không có ID, báo lỗi
    die("Lỗi: Không có ID sản phẩm được cung cấp.");
}
?>