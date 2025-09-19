<?php
// Bật hiển thị lỗi để dễ dàng chẩn đoán
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Thiết lập header để trình duyệt hiểu đây là dữ liệu JSON
header('Content-Type: application/json; charset=utf-8');

/*
 * Đảm bảo đường dẫn này là chính xác.
 * File 'fetch_products.php' nằm trong thư mục 'api',
 * nên nó cần đi ra một cấp ('../') để tìm thấy 'db_connect_pdo.php'.
*/
require_once '../db_connect_pdo.php';

try {
    // Truy vấn tất cả sản phẩm
    $sql = "SELECT id, name, category, price_new, price_old, image_url, sold_count, tag FROM products ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll();

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode($products);

} catch (PDOException $e) {
    // Nếu có lỗi, trả về một thông báo lỗi dạng JSON
    http_response_code(500); // Lỗi server
    echo json_encode(['error' => 'Lỗi truy vấn cơ sở dữ liệu: ' . $e->getMessage()]);
}
?>