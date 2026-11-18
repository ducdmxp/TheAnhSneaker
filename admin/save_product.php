<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/image_upload.php';
require_login();

// Tổng dung lượng gửi lên vượt post_max_size => PHP bỏ toàn bộ dữ liệu form, báo lỗi rõ ràng thay vì lỗi CSRF.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    exit('Lỗi: Tổng dung lượng ảnh quá lớn (tối đa ' . htmlspecialchars((string) ini_get('post_max_size')) . ' mỗi lần lưu). '
        . 'Hãy chọn ít ảnh hơn, lưu, rồi thêm tiếp. <a href="javascript:history.back()">Quay lại</a>');
}

require_post_with_csrf();
require_once __DIR__ . '/../db_connect_pdo.php';
require_once __DIR__ . '/../includes/catalog.php';

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
$savedFiles = []; // các file mới đã ghi ra đĩa, để dọn dẹp nếu có lỗi

/** Báo lỗi và xóa các ảnh mới đã lưu trong lần này. */
function fail_cleanup(string $message): void
{
    global $savedFiles;
    foreach ($savedFiles as $f) delete_product_image($f);
    fail($message);
}

/** Kiểm tra + lưu một file ảnh upload, trả về đường dẫn 'uploads/...'. */
function save_upload(array $upload, string $label): string
{
    global $savedFiles;
    if ($upload['error'] === UPLOAD_ERR_INI_SIZE || $upload['error'] === UPLOAD_ERR_FORM_SIZE) fail_cleanup($label . ' vượt quá 10MB.');
    if ($upload['error'] !== UPLOAD_ERR_OK) fail_cleanup('Upload ' . $label . ' thất bại (mã lỗi ' . (int) $upload['error'] . ').');
    if ($upload['size'] > 10 * 1024 * 1024) fail_cleanup($label . ' vượt quá 10MB.');

    // Kiểm tra nội dung thật sự là ảnh; đuôi file lấy theo loại ảnh, không theo tên file gửi lên.
    $info = @getimagesize($upload['tmp_name']);
    $extByType = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($extByType[$info[2]])) fail_cleanup($label . ': chỉ cho phép ảnh JPG, PNG, GIF hoặc WEBP.');

    $fileName = uniqid('product_', true) . '.' . $extByType[$info[2]];
    // Lưu ảnh đã xoay đúng chiều và xóa sạch EXIF (vị trí GPS, tên máy...).
    if (!store_clean_image($upload['tmp_name'], $info[2], __DIR__ . '/../uploads/' . $fileName)) fail_cleanup('Không thể lưu ' . $label . '.');
    $savedFiles[] = 'uploads/' . $fileName;
    return 'uploads/' . $fileName;
}

/** Chuyển $_FILES['x'] dạng mảng (input multiple) thành danh sách từng file, bỏ ô trống. */
function normalize_files(?array $field): array
{
    if (!$field || !is_array($field['name'] ?? null)) return [];
    $files = [];
    foreach ($field['name'] as $i => $name) {
        if ((int) $field['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $files[] = [
            'name' => $name,
            'tmp_name' => $field['tmp_name'][$i],
            'error' => (int) $field['error'][$i],
            'size' => (int) $field['size'][$i],
        ];
    }
    return $files;
}

// Ảnh phụ hiện có (luôn lấy từ database) và những ảnh được đánh dấu xóa.
ensure_product_images_table($pdo);
$existingGallery = $id ? fetch_product_images($pdo, $id) : [];
$deleteIds = array_map('intval', (array) ($_POST['delete_gallery'] ?? []));
$toDelete = array_values(array_filter($existingGallery, fn ($g) => in_array((int) $g['id'], $deleteIds, true)));
$keptCount = count($existingGallery) - count($toDelete);

$galleryUploads = normalize_files($_FILES['gallery_images'] ?? null);
// +1 khi chưa có ảnh chính: ảnh phụ đầu tiên sẽ thành ảnh chính.
$allowed = PRODUCT_GALLERY_MAX - $keptCount + ($imageUrl === '' ? 1 : 0);
if (count($galleryUploads) > $allowed) {
    fail('Mỗi sản phẩm tối đa ' . PRODUCT_GALLERY_MAX . ' ảnh phụ. Hiện còn ' . $keptCount . ' ảnh, bạn đang thêm ' . count($galleryUploads) . ' ảnh.');
}

$upload = $_FILES['product_image'] ?? null;
if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
    $imageUrl = save_upload($upload, 'Ảnh chính');
    $newImageFile = $imageUrl;
}

$newGallery = [];
foreach ($galleryUploads as $i => $file) {
    $newGallery[] = save_upload($file, 'Ảnh phụ thứ ' . ($i + 1));
}

// Sản phẩm chưa có ảnh chính: lấy ảnh phụ đầu tiên làm ảnh chính.
if ($imageUrl === '' && $newGallery) {
    $imageUrl = array_shift($newGallery);
    $newImageFile = $imageUrl;
}
if ($keptCount + count($newGallery) > PRODUCT_GALLERY_MAX) {
    fail_cleanup('Mỗi sản phẩm tối đa ' . PRODUCT_GALLERY_MAX . ' ảnh phụ.');
}

if ($imageUrl === '') fail('Vui lòng chọn ảnh cho sản phẩm mới.');

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
    $pdo->beginTransaction();
    if ($id) {
        $params[':id'] = $id;
        $sql = 'UPDATE products SET name=:name, category=:category, price_new=:price_new, price_old=:price_old, image_url=:image_url, sold_count=:sold_count, tag=:tag WHERE id=:id';
    } else {
        $sql = 'INSERT INTO products (name, category, price_new, price_old, image_url, sold_count, tag) VALUES (:name, :category, :price_new, :price_old, :image_url, :sold_count, :tag)';
    }
    $pdo->prepare($sql)->execute($params);
    $productId = $id ?: (int) $pdo->lastInsertId();

    if ($toDelete) {
        $del = $pdo->prepare('DELETE FROM product_images WHERE id = :id AND product_id = :pid');
        foreach ($toDelete as $g) $del->execute([':id' => $g['id'], ':pid' => $productId]);
    }
    if ($newGallery) {
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM product_images WHERE product_id = :pid');
        $stmt->execute([':pid' => $productId]);
        $order = (int) $stmt->fetchColumn();
        $ins = $pdo->prepare('INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:pid, :url, :ord)');
        foreach ($newGallery as $url) $ins->execute([':pid' => $productId, ':url' => $url, ':ord' => ++$order]);
    }
    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[admin/save_product] ' . $e->getMessage());
    fail_cleanup('Không lưu được sản phẩm, vui lòng thử lại.');
}

// Chỉ xóa file ảnh phụ sau khi database đã lưu thành công.
foreach ($toDelete as $g) delete_product_image($g['image_url']);

// Chỉ xóa ảnh cũ sau khi đã lưu thành công ảnh mới.
if ($newImageFile && $oldImage !== '' && $oldImage !== $newImageFile) {
    delete_product_image($oldImage);
}

header('Location: index.php');
exit;
