<?php
/**
 * Trang chi tiết sản phẩm: /san-pham/{ten-san-pham}-{id}/
 * (.htaccess chuyển URL thân thiện về san-pham.php?id=..&slug=..)
 */
require_once __DIR__ . '/includes/layout.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = null;
$related = [];
$gallery = [];
try {
    require_once __DIR__ . '/db_connect_pdo.php';
    if ($id) {
        $product = fetch_product($pdo, $id);
        if ($product) {
            $gallery = fetch_product_images($pdo, (int) $product['id']);
            $related = fetch_related($pdo, $product, 4);
        }
    }
} catch (Throwable $ex) {
    error_log('[san-pham] ' . $ex->getMessage());
}

$base = site_base_path();

// Không tìm thấy -> 404 thật (để Google không index trang rỗng).
if (!$product) {
    http_response_code(404);
    ?><!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>Không tìm thấy sản phẩm | TheAnhSneaker</title>
    <?php site_assets(); ?>
  </head>
  <body>
    <?php site_header(); ?>
    <main id="main" class="section">
      <div class="container not-found">
        <h1>Không tìm thấy sản phẩm</h1>
        <p class="section-sub">Mẫu này có thể đã hết hàng hoặc đường dẫn không đúng.</p>
        <p><a class="btn btn-primary btn-lg" href="<?= $base ?>#products">Xem các mẫu đang có</a></p>
      </div>
    </main>
    <?php site_footer(); ?>
  </body>
</html>
<?php
    exit;
}

// URL sai tên (đổi tên sản phẩm, gõ thiếu...) -> chuyển 301 về URL chuẩn.
$canonicalPath = product_path($product);
$requestedSlug = (string) ($_GET['slug'] ?? '');
if ($requestedSlug !== '' && $requestedSlug !== slugify($product['name'])) {
    header('Location: ' . product_url($product), true, 301);
    exit;
}

$brand = brand_name($product['category']);
$discount = discount_percent($product);
$priceText = format_vnd($product['price_new']);
$description = product_description($product);
$metaDescription = mb_substr(
    'Giày ' . $product['name'] . ($priceText ? ' giá ' . $priceText : '')
    . '. Full box, ship COD toàn quốc, kiểm tra hàng trước khi trả tiền, đổi size 7 ngày.',
    0, 158
);
$mainImage = optimized_image($product['image_url'], 900);
$shareImage = social_image($product['image_url']);
$alt = 'Giày ' . $product['name'] . ' - ' . SITE_NAME;

// Ảnh chính + ảnh phụ cho bộ sưu tập ảnh.
$allImages = array_merge([$product['image_url']], array_column($gallery, 'image_url'));
$slides = [];
foreach ($allImages as $i => $url) {
    $slides[] = [
        'full' => $i === 0 ? $mainImage : optimized_image($url, 900),
        'thumb' => optimized_image($url, 160),
        'alt' => $alt . ($i > 0 ? ' - ảnh ' . ($i + 1) : ''),
    ];
}

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        product_schema($product, true, $allImages),
        breadcrumb_schema([['Sản phẩm', '#products'], [$product['name'], $canonicalPath]]),
    ],
];

// Dữ liệu cho sự kiện ViewContent (Facebook/TikTok/Google) trong script.js
$viewContent = [
    'id' => 'TAS-' . (int) $product['id'],
    'name' => $product['name'],
    'category' => $brand ?: 'Sneaker',
    'price' => (float) $product['price_new'],
];
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <script>document.documentElement.classList.add("js");</script>
    <?php seo_meta([
        'title' => 'Giày ' . $product['name'] . ($priceText ? ' – ' . $priceText : '') . ' | TheAnhSneaker',
        'description' => $metaDescription,
        'path' => $canonicalPath,
        'image' => $shareImage,
        'image_width' => $shareImage !== $product['image_url'] ? 1200 : null,
        'image_height' => $shareImage !== $product['image_url'] ? 630 : null,
        'type' => 'product',
        'prefix' => $base,
    ]); ?>
    <?php if ((float) $product['price_new'] > 0): ?>
    <meta property="product:price:amount" content="<?= (float) $product['price_new'] ?>" />
    <meta property="product:price:currency" content="VND" />
    <meta property="product:availability" content="in stock" />
    <meta property="product:retailer_item_id" content="TAS-<?= (int) $product['id'] ?>" />
    <?php endif; ?>
    <?= json_ld($schema) ?>
    <link rel="preload" as="image" href="<?= e($base . $mainImage) ?>" fetchpriority="high" />
    <?php site_assets(); ?>
  </head>
  <body data-view-content="<?= e(json_encode($viewContent, JSON_UNESCAPED_UNICODE)) ?>">
    <?php site_header(); ?>

    <main id="main">
      <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
          <a href="<?= $base ?>">Trang chủ</a>
          <span aria-hidden="true">›</span>
          <a href="<?= $base ?>#products">Sản phẩm</a>
          <span aria-hidden="true">›</span>
          <span aria-current="page"><?= e($product['name']) ?></span>
        </nav>

        <article class="pdp">
          <div class="pdp-gallery" data-gallery>
            <div class="pdp-media">
              <img
                src="<?= e($base . $mainImage) ?>"
                alt="<?= e($alt) ?>"
                width="900"
                height="900"
                fetchpriority="high"
                decoding="async"
                data-gallery-main
              />
              <span class="product-badges">
                <?php if (!empty($product['tag'])): ?><span class="badge badge-hot"><?= e($product['tag']) ?></span><?php endif; ?>
                <?php if ($discount > 0): ?><span class="badge badge-sale">-<?= $discount ?>%</span><?php endif; ?>
              </span>
              <?php if (count($slides) > 1): ?>
              <button type="button" class="pdp-nav pdp-prev" data-gallery-prev aria-label="Ảnh trước">‹</button>
              <button type="button" class="pdp-nav pdp-next" data-gallery-next aria-label="Ảnh tiếp theo">›</button>
              <span class="pdp-counter" data-gallery-counter>1 / <?= count($slides) ?></span>
              <?php endif; ?>
            </div>
            <?php if (count($slides) > 1): ?>
            <div class="pdp-thumbs" role="list">
              <?php foreach ($slides as $i => $sl): ?>
              <button
                type="button"
                class="pdp-thumb<?= $i === 0 ? ' is-active' : '' ?>"
                role="listitem"
                data-full="<?= e($base . $sl['full']) ?>"
                data-alt="<?= e($sl['alt']) ?>"
                aria-label="Xem ảnh <?= $i + 1 ?>"
                <?= $i === 0 ? 'aria-current="true"' : '' ?>
              >
                <img src="<?= e($base . $sl['thumb']) ?>" alt="" width="80" height="80" loading="lazy" decoding="async" />
              </button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <div class="pdp-info">
            <span class="product-brand"><?= e($brand ?: 'Sneaker') ?></span>
            <h1 class="pdp-title"><?= e($product['name']) ?></h1>
            <?php if ((int) $product['sold_count'] > 0): ?>
            <p class="product-sold"><?= icon('fire') ?> Đã bán <?= (int) $product['sold_count'] ?></p>
            <?php endif; ?>

            <div class="pdp-price">
              <strong><?= $priceText ?: 'Liên hệ' ?></strong>
              <?php if (format_vnd($product['price_old'])): ?><s><?= format_vnd($product['price_old']) ?></s><?php endif; ?>
              <?php if ($discount > 0): ?><span class="badge badge-sale">Tiết kiệm <?= $discount ?>%</span><?php endif; ?>
            </div>

            <p class="pdp-desc"><?= e($description) ?></p>

            <div class="pdp-actions">
              <a href="<?= zalo_url() ?>" class="btn btn-primary btn-lg btn-block" target="_blank" rel="nofollow noopener" <?= product_track_attrs($product) ?>>
                <?= icon('chat') ?> Đặt qua Zalo – tư vấn size miễn phí
              </a>
              <a href="<?= tel_url() ?>" class="btn btn-dark btn-lg btn-block" data-track="call" data-product="<?= e($product['name']) ?>" data-product-id="TAS-<?= (int) $product['id'] ?>" data-price="<?= (float) $product['price_new'] ?>">
                <?= icon('phone') ?> Gọi <?= SITE_PHONE_DISPLAY ?>
              </a>
            </div>
            <p class="pdp-note">Bấm “Đặt qua Zalo”, tên mẫu được tự động sao chép, bạn chỉ cần dán vào Zalo kèm size.</p>

            <ul class="pdp-perks">
              <li><?= icon('eye') ?><span><strong>Kiểm tra trước, trả tiền sau</strong>Mở hộp đồng kiểm với shipper</span></li>
              <li><?= icon('truck') ?><span><strong>Ship COD toàn quốc</strong>30.000đ · Freeship đơn từ 1.000.000đ</span></li>
              <li><?= icon('refresh') ?><span><strong>Đổi size 7 ngày</strong>Sản phẩm còn mới, đủ hộp</span></li>
              <li><?= icon('shield') ?><span><strong>Bảo hành keo, chỉ 3 tháng</strong>Hỗ trợ ship chiều gửi lại</span></li>
            </ul>

            <p class="pdp-links">
              <a href="<?= $base ?>huong-dan-chon-size/"><?= icon('ruler') ?> Hướng dẫn chọn size</a>
              <a href="<?= $base ?>chinh-sach-doi-tra/"><?= icon('refresh') ?> Chính sách đổi trả</a>
            </p>
          </div>
        </article>
      </div>

      <?php if ($related): ?>
      <section class="section section-soft related">
        <div class="container">
          <div class="section-head">
            <p class="section-kicker">Có thể bạn thích</p>
            <h2>Mẫu tương tự</h2>
          </div>
          <div class="product-grid">
            <?php foreach ($related as $r) { product_card($r, true, 'h3'); } ?>
          </div>
        </div>
      </section>
      <?php endif; ?>
    </main>

    <?php site_footer(); ?>
  </body>
</html>
