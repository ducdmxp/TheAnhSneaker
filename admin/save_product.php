<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Đảm bảo sử dụng file kết nối PDO, file này sẽ tạo ra biến $pdo
require_once '../db_connect_pdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price_new = $_POST['price_new'];
    $price_old = !empty($_POST['price_old']) ? $_POST['price_old'] : NULL;
    $sold_count = !empty($_POST['sold_count']) ? $_POST['sold_count'] : 0;
    $tag = $_POST['tag'];
    
    // SỬA LỖI 1: Lấy đúng tên ô input ẩn 'current_image' thay vì 'image_url'
    $image_url = $_POST['current_image']; // Mặc định lấy ảnh cũ

    // === PHẦN XỬ LÝ UPLOAD ẢNH ===
    // Kiểm tra xem có file nào được tải lên không và không có lỗi
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $upload_dir = '../uploads/';
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_file_name = uniqid('product_', true) . '.' . $file_extension;
        $target_file = $upload_dir . $new_file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $image_url = 'uploads/' . $new_file_name;

                if (!empty($_POST['current_image'])) {
                    $old_image_path = '../' . $_POST['current_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                die("Lỗi: Không thể upload file.");
            }
        } else {
            die("Lỗi: Chỉ cho phép upload file ảnh (JPG, JPEG, PNG, GIF).");
        }
    }

    // === PHẦN LƯU VÀO DATABASE ===
    // SỬA LỖI 2: Sử dụng biến kết nối $pdo thay vì $conn
    if (empty($id)) {
        // Thêm mới sản phẩm
        $sql = "INSERT INTO products (name, category, price_new, price_old, image_url, sold_count, tag) VALUES (:name, :category, :price_new, :price_old, :image_url, :sold_count, :tag)";
        $stmt = $pdo->prepare($sql);
    } else {
        // Cập nhật sản phẩm
        $sql = "UPDATE products SET name=:name, category=:category, price_new=:price_new, price_old=:price_old, image_url=:image_url, sold_count=:sold_count, tag=:tag WHERE id=:id";
        $stmt = $pdo->prepare($sql);
    }

    // Bind các tham số
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':price_new', $price_new);
    $stmt->bindParam(':price_old', $price_old);
    $stmt->bindParam(':image_url', $image_url);
    $stmt->bindParam(':sold_count', $sold_count);
    $stmt->bindParam(':tag', $tag);
    if (!empty($id)) {
        $stmt->bindParam(':id', $id);
    }
    
    // Thực thi và chuyển hướng
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Lỗi khi lưu sản phẩm.";
    }
}
?>