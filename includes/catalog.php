<?php
/**
 * Hàm dùng chung cho sản phẩm: định dạng giá, slug/URL thân thiện, truy vấn database.
 */
require_once __DIR__ . '/seo.php';

const BRAND_NAMES = ['nike' => 'Nike', 'adidas' => 'Adidas', 'new-balance' => 'New Balance'];

function brand_name(?string $category): string
{
    return BRAND_NAMES[$category ?? ''] ?? '';
}

function format_vnd($price): string
{
    return is_numeric($price) && $price > 0 ? number_format((float) $price, 0, ',', '.') . 'đ' : '';
}

function discount_percent(array $p): int
{
    $old = (float) ($p['price_old'] ?? 0);
    $new = (float) ($p['price_new'] ?? 0);
    return ($old > $new && $new > 0) ? (int) round(($old - $new) / $old * 100) : 0;
}

/** Chuyển tên tiếng Việt thành slug không dấu: "Giày Đỏ Mới" -> "giay-do-moi". */
function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    if (class_exists('Normalizer')) {
        $text = Normalizer::normalize($text, Normalizer::FORM_C);
    }
    $groups = [
        'a' => 'àáạảãâầấậẩẫăằắặẳẵ',
        'e' => 'èéẹẻẽêềếệểễ',
        'i' => 'ìíịỉĩ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡ',
        'u' => 'ùúụủũưừứựửữ',
        'y' => 'ỳýỵỷỹ',
        'd' => 'đ',
    ];
    foreach ($groups as $ascii => $chars) {
        $text = preg_replace('/[' . $chars . ']/u', $ascii, $text);
    }
    $text = preg_replace('/\p{Mn}+/u', '', $text);           // dấu tổ hợp còn sót
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim(substr($text, 0, 80), '-');
    return $text !== '' ? $text : 'san-pham';
}

/** Đường dẫn tương đối (với thư mục gốc) của trang sản phẩm: san-pham/ten-san-pham-12/ */
function product_path(array $p): string
{
    return 'san-pham/' . slugify($p['name']) . '-' . (int) $p['id'] . '/';
}

/** URL tuyệt đối của trang sản phẩm. */
function product_url(array $p): string
{
    return site_url(product_path($p));
}

/** Đường dẫn để đặt trong href (tính từ gốc tên miền). */
function product_href(array $p): string
{
    return site_base_path() . product_path($p);
}

function fetch_products(PDO $pdo): array
{
    return $pdo->query('SELECT id, name, category, price_new, price_old, image_url, sold_count, tag FROM products ORDER BY id DESC')->fetchAll();
}

function fetch_product(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, category, price_new, price_old, image_url, sold_count, tag FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/** Số ảnh phụ tối đa cho mỗi sản phẩm (không tính ảnh chính). */
const PRODUCT_GALLERY_MAX = 12;

/** Tạo bảng ảnh phụ nếu website được cài trước khi có tính năng nhiều ảnh. */
function ensure_product_images_table(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        KEY idx_product (product_id, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

/** Ảnh phụ của sản phẩm: [['id' => .., 'image_url' => ..], ...]. Trả về [] nếu chưa có bảng. */
function fetch_product_images(PDO $pdo, int $productId): array
{
    try {
        $stmt = $pdo->prepare('SELECT id, image_url FROM product_images WHERE product_id = :id ORDER BY sort_order, id');
        $stmt->execute([':id' => $productId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return []; // bảng chưa được tạo (chưa lưu sản phẩm nào kể từ khi cập nhật)
    }
}

/** Sản phẩm liên quan: ưu tiên cùng thương hiệu, đủ $limit thì dừng. */
function fetch_related(PDO $pdo, array $product, int $limit = 4): array
{
    $stmt = $pdo->prepare('SELECT id, name, category, price_new, price_old, image_url, sold_count, tag FROM products
        WHERE id <> :id ORDER BY (category = :cat) DESC, sold_count DESC, id DESC LIMIT ' . (int) $limit);
    $stmt->execute([':id' => $product['id'], ':cat' => $product['category']]);
    return $stmt->fetchAll();
}

/** Mô tả ngắn cho trang sản phẩm (không cam kết điều shop không đảm bảo được). */
function product_description(array $p): string
{
    $brand = brand_name($p['category']);
    $intro = $p['name'] . ($brand && stripos($p['name'], $brand) === false ? ' (' . $brand . ')' : '');
    return $intro . ' – mẫu sneaker được yêu thích nhờ thiết kế dễ phối đồ, phù hợp đi học, đi làm và dạo phố. '
        . 'Tại TheAnhSneaker, sản phẩm được giao full box, đủ phụ kiện; ship COD toàn quốc, '
        . 'bạn được mở hộp kiểm tra trước khi thanh toán và hỗ trợ đổi size trong 7 ngày.';
}

/** Product + Offer schema cho một sản phẩm. */
function product_schema(array $p, bool $full = true, array $images = []): array
{
    $images = $images ?: [$p['image_url']];
    $data = [
        '@type' => 'Product',
        '@id' => product_url($p) . '#product',
        'name' => $p['name'],
        'url' => product_url($p),
        'image' => count($images) > 1 ? array_map('site_url', $images) : site_url($images[0]),
        'sku' => 'TAS-' . $p['id'],
        'brand' => ['@type' => 'Brand', 'name' => brand_name($p['category']) ?: SITE_NAME],
        'offers' => [
            '@type' => 'Offer',
            'url' => product_url($p),
            'priceCurrency' => 'VND',
            'price' => (float) $p['price_new'],
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => ['@id' => site_url('#store')],
        ],
    ];
    if ($full) {
        $data['description'] = product_description($p);
        $data['offers']['shippingDetails'] = [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => 30000, 'currency' => 'VND'],
            'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'VN'],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                'transitTime' => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 5, 'unitCode' => 'DAY'],
            ],
        ];
        $data['offers']['hasMerchantReturnPolicy'] = [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => 'VN',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => 7,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/ReturnShippingFees',
        ];
    }
    return $data;
}
