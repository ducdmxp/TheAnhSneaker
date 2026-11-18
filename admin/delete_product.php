<?php
require_once __DIR__ . '/security.php';
require_login();
require_post_with_csrf();
require_once __DIR__ . '/../db_connect_pdo.php';
require_once __DIR__ . '/../includes/catalog.php';

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

    $gallery = fetch_product_images($pdo, $id);
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $id]);
    if ($gallery) {
        $pdo->prepare('DELETE FROM product_images WHERE product_id = :id')->execute([':id' => $id]);
    }
    $pdo->commit();
    delete_product_image($product['image_url']);
    foreach ($gallery as $g) delete_product_image($g['image_url']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[admin/delete_product] ' . $e->getMessage());
    http_response_code(500);
    exit('Lỗi: Không xóa được sản phẩm, vui lòng thử lại.');
}

header('Location: index.php');
exit;
