<?php
require_once __DIR__ . '/security.php';
require_login();
require_post_with_csrf();
require_once __DIR__ . '/../db_connect_pdo.php';

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Lỗi: ID sản phẩm không hợp lệ.');
}

try {
    $stmt = $pdo->prepare('SELECT image_url FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    if (!$product) {
        http_response_code(404);
        exit('Lỗi: Không tìm thấy sản phẩm để xóa.');
    }

    $pdo->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $id]);
    delete_product_image($product['image_url']);
} catch (PDOException $e) {
    error_log('[admin/delete_product] ' . $e->getMessage());
    http_response_code(500);
    exit('Lỗi: Không xóa được sản phẩm, vui lòng thử lại.');
}

header('Location: index.php');
exit;
