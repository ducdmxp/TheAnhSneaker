<?php
require_once __DIR__ . '/security.php';
require_login();
require_post_with_csrf();
require_once __DIR__ . '/../db_connect_pdo.php';

function fail(string $message): void
{
    http_response_code(400);
    exit('Lỗi: ' . htmlspecialchars($message) . ' <a href="javascript:history.back()">Quay lại</a>');
}

$allowedCategories = ['nike', 'adidas', 'new-balance', 'other'];

// ---------- Kiểm tra dữ liệu ----------
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$name = trim((string) ($_POST['name'] ?? ''));
$category = (string) ($_POST['category'] ?? '');
$priceNew = filter_var($_POST['price_new'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$priceOldRaw = trim((string) ($_POST['price_old'] ?? ''));
$priceOld = $priceOldRaw === '' ? null : filter_var($priceOldRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$soldRaw = trim((string) ($_POST['sold_count'] ?? ''));
$soldCount = $soldRaw === '' ? 0 : filter_var($soldRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$tag = trim((string) ($_POST['tag'] ?? ''));

if ($name === '' || mb_strlen($name) > 255) fail('Tên sản phẩm không được để trống và tối đa 255 ký tự.');
if (!in_array($category, $allowedCategories, true)) fail('Danh mục không hợp lệ.');
if ($priceNew === false) fail('Giá mới phải là số nguyên không âm.');
if ($priceOld === false) fail('Giá cũ phải là số nguyên không âm.');
if ($soldCount === false) fail('Số lượng đã bán phải là số nguyên không âm.');
if (mb_strlen($tag) > 50) fail('Tag tối đa 50 ký tự.');

// Ảnh hiện tại luôn lấy từ database, KHÔNG tin dữ liệu gửi lên từ form.
$oldImage = '';
if ($id) {
    $stmt = $pdo->prepare('SELECT image_url FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) fail('Không tìm thấy sản phẩm.');
    $oldImage = (string) $row['image_url'];
}
$imageUrl = $oldImage;
$newImageFile = null;

// ---------- Upload ảnh ----------
$upload = $_FILES['product_image'] ?? null;
if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($upload['error'] !== UPLOAD_ERR_OK) fail('Upload ảnh thất bại (mã lỗi ' . (int) $upload['error'] . ').');
    if ($upload['size'] > 10 * 1024 * 1024) fail('Ảnh tối đa 10MB.');

    // Kiểm tra nội dung thật sự là ảnh; đuôi file lấy theo loại ảnh, không theo tên file gửi lên.
    $info = @getimagesize($upload['tmp_name']);
    $extByType = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($extByType[$info[2]])) fail('Chỉ cho phép ảnh JPG, PNG, GIF hoặc WEBP.');

    $fileName = uniqid('product_', true) . '.' . $extByType[$info[2]];
    if (!move_uploaded_file($upload['tmp_name'], __DIR__ . '/../uploads/' . $fileName)) fail('Không thể lưu file ảnh.');
    $imageUrl = 'uploads/' . $fileName;
    $newImageFile = $imageUrl;
}

if (!$id && $imageUrl === '') fail('Vui lòng chọn ảnh cho sản phẩm mới.');

// ---------- Lưu database ----------
$params = [
    ':name' => $name,
    ':category' => $category,
    ':price_new' => $priceNew,
    ':price_old' => $priceOld,
    ':image_url' => $imageUrl,
    ':sold_count' => $soldCount,
    ':tag' => $tag,
];
try {
    if ($id) {
        $params[':id'] = $id;
        $sql = 'UPDATE products SET name=:name, category=:category, price_new=:price_new, price_old=:price_old, image_url=:image_url, sold_count=:sold_count, tag=:tag WHERE id=:id';
    } else {
        $sql = 'INSERT INTO products (name, category, price_new, price_old, image_url, sold_count, tag) VALUES (:name, :category, :price_new, :price_old, :image_url, :sold_count, :tag)';
    }
    $pdo->prepare($sql)->execute($params);
} catch (PDOException $e) {
    error_log('[admin/save_product] ' . $e->getMessage());
    if ($newImageFile) delete_product_image($newImageFile);
    fail('Không lưu được sản phẩm, vui lòng thử lại.');
}

// Chỉ xóa ảnh cũ sau khi đã lưu thành công ảnh mới.
if ($newImageFile && $oldImage !== '' && $oldImage !== $newImageFile) {
    delete_product_image($oldImage);
}

header('Location: index.php');
exit;
