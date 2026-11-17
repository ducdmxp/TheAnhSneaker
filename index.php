<?php
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/image.php';
require_once __DIR__ . '/includes/tracking.php';

// Lấy sản phẩm để render sẵn HTML phía server (Google index được ngay, không phụ thuộc JavaScript).
$products = [];
try {
    require_once __DIR__ . '/db_connect_pdo.php';
    $stmt = $pdo->query('SELECT id, name, category, price_new, price_old, image_url, sold_count, tag FROM products ORDER BY id DESC');
    $products = $stmt->fetchAll();
} catch (Throwable $ex) {
    error_log('[index] Không tải được sản phẩm: ' . $ex->getMessage());
}

$brandNames = ['nike' => 'Nike', 'adidas' => 'Adidas', 'new-balance' => 'New Balance'];
$itemsPerLoad = 8;
$year = date('Y');
$zaloUrl = 'https://zalo.me/' . SITE_PHONE;
$telUrl = 'tel:' . SITE_PHONE;

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

$totalSold = array_sum(array_map(fn ($p) => (int) $p['sold_count'], $products));
$featured = $products[0] ?? null;

$pageTitle = "Giày Sneaker Nam Nữ Hot Trend {$year} Giá Rẻ Hà Nội | TheAnhSneaker";
$pageDescription = "TheAnhSneaker chuyên giày sneaker nam nữ Nike, Adidas, New Balance hot trend {$year}, giá rẻ, full box, ship COD toàn quốc, kiểm tra hàng trước khi thanh toán, đổi size 7 ngày.";
$ogImage = $featured['image_url'] ?? 'img/logo-512.png';

$faqs = [
    [
        'q' => 'Shop có ship COD toàn quốc không?',
        'a' => 'Có. TheAnhSneaker giao hàng toàn quốc, bạn nhận hàng rồi mới thanh toán. Phí ship đồng giá 30.000đ, miễn phí vận chuyển cho đơn từ 1.000.000đ.',
    ],
    [
        'q' => 'Bao lâu thì tôi nhận được hàng?',
        'a' => 'Nội thành Hà Nội 1 - 2 ngày, các tỉnh miền Bắc 2 - 3 ngày, miền Trung và miền Nam 3 - 5 ngày làm việc (không tính Chủ nhật, ngày lễ).',
    ],
    [
        'q' => 'Tôi có được kiểm tra hàng trước khi thanh toán không?',
        'a' => 'Có. Bạn được mở hộp đồng kiểm với shipper để xem đúng mẫu, đúng size, tình trạng sản phẩm. Nếu sai mẫu hoặc lỗi, bạn có quyền từ chối nhận và không mất phí. Lưu ý chỉ kiểm tra, không đi thử vào chân.',
    ],
    [
        'q' => 'Đi không vừa có được đổi size không?',
        'a' => 'Được đổi trong vòng 7 ngày kể từ khi nhận hàng, sản phẩm còn mới 100%, chưa sử dụng, đủ tem mác và hộp. Đổi size/đổi mẫu theo nhu cầu, bạn chịu phí ship 2 chiều; nếu shop giao sai hoặc hàng lỗi, shop chịu 100% phí. Không áp dụng cho sản phẩm khuyến mãi giảm sâu.',
    ],
    [
        'q' => 'Giày được bảo hành như thế nào?',
        'a' => 'Bảo hành 3 tháng các lỗi hở keo, bung keo, đứt chỉ do nhà sản xuất. Shop sửa trong 5 - 7 ngày làm việc và hỗ trợ phí ship chiều gửi lại cho bạn.',
    ],
    [
        'q' => 'Làm sao để chọn đúng size?',
        'a' => 'Đo chiều dài bàn chân (cm), cộng thêm 0,5 - 1cm rồi đối chiếu bảng size. Hoặc nhắn Zalo số đo chân cho shop, shop tư vấn size chuẩn theo từng form giày miễn phí.',
    ],
];

$testimonials = [
    ['img' => 'img/dangvantinh.png', 'name' => 'Anh Đặng Văn Tỉnh', 'city' => 'Hà Nội', 'stars' => 5,
     'text' => 'Giày đẹp xuất sắc, còn thơm mùi keo mới. Shop tư vấn size rất chuẩn, đi vừa in. Sẽ là khách quen của TheAnhSneaker!'],
    ['img' => 'img/minhhang.png', 'name' => 'Chị Minh Hằng', 'city' => 'TP.HCM', 'stars' => 5,
     'text' => 'Giao hàng nhanh kinh khủng. Mình ở SG mà 2 ngày đã nhận được. Giày đi êm, chuẩn form, bạn bè ai cũng khen. 10 điểm cho shop!'],
    ['img' => 'img/quanghuy.png', 'name' => 'Bạn Quang Huy', 'city' => 'Đà Nẵng', 'stars' => 4,
     'text' => 'Giá quá tốt so với chất lượng. Check legit thoải mái. Mình đã mua 2 đôi và rất hài lòng. Cảm ơn shop nhiều.'],
    ['img' => 'img/phuongthao.png', 'name' => 'Chị Phương Thảo', 'city' => 'Hải Phòng', 'stars' => 5,
     'text' => 'Lần đầu mua online mà ưng ý đến vậy. Shop đóng gói cẩn thận, hộp giày không bị móp méo. Chắc chắn sẽ giới thiệu cho bạn bè.'],
];

// ---------- Dữ liệu cấu trúc (JSON-LD) ----------
$productItems = [];
foreach ($products as $i => $p) {
    $productItems[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => [
            '@type' => 'Product',
            'name' => $p['name'],
            'image' => site_url($p['image_url']),
            'sku' => 'TAS-' . $p['id'],
            'brand' => ['@type' => 'Brand', 'name' => $brandNames[$p['category']] ?? SITE_NAME],
            'offers' => [
                '@type' => 'Offer',
                'url' => site_url('#products'),
                'priceCurrency' => 'VND',
                'price' => (float) $p['price_new'],
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => ['@id' => site_url('#store')],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => 30000, 'currency' => 'VND'],
                    'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'VN'],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                        'transitTime' => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 5, 'unitCode' => 'DAY'],
                    ],
                ],
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'VN',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => 7,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                    'returnFees' => 'https://schema.org/ReturnShippingFees',
                ],
            ],
        ],
    ];
}
$graph = [
    store_schema(),
    [
        '@type' => 'WebSite',
        '@id' => site_url('#website'),
        'url' => site_url(),
        'name' => SITE_NAME,
        'inLanguage' => 'vi-VN',
        'publisher' => ['@id' => site_url('#store')],
    ],
    [
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $faqs),
    ],
];
if ($productItems) {
    $graph[] = [
        '@type' => 'ItemList',
        'name' => 'Bộ sưu tập giày sneaker TheAnhSneaker',
        'numberOfItems' => count($productItems),
        'itemListElement' => $productItems,
    ];
}

$heroImage = $featured ? optimized_image($featured['image_url'], 800) : 'img/logo-512.png';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <script>document.documentElement.classList.add("js");</script>
    <?php seo_meta([
        'title' => $pageTitle,
        'description' => $pageDescription,
        'path' => '',
        'image' => $ogImage,
    ]); ?>
    <?= json_ld(['@context' => 'https://schema.org', '@graph' => $graph]) ?>

    <link rel="preload" as="image" href="<?= e($heroImage) ?>" fetchpriority="high" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>" />
    <script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>" defer></script>
    <?php tracking_head(); ?>
  </head>
  <body>
    <?php tracking_body(); ?>
    <a class="skip-link" href="#products">Bỏ qua tới sản phẩm</a>

    <div class="topbar">
      <div class="container topbar-inner">
        <span><?= icon('truck') ?> Freeship đơn từ 1.000.000đ</span>
        <span class="topbar-hide-sm"><?= icon('wallet') ?> Ship COD toàn quốc</span>
        <span class="topbar-hide-sm"><?= icon('eye') ?> Kiểm tra hàng trước khi thanh toán</span>
      </div>
    </div>

    <header class="header">
      <div class="container header-inner">
        <a href="./" class="logo" aria-label="TheAnhSneaker - Trang chủ">TheAnh<span>Sneaker</span></a>

        <nav class="main-nav" id="main-nav" aria-label="Điều hướng chính">
          <ul>
            <li><a href="#products">Sản phẩm</a></li>
            <li><a href="#why">Cam kết</a></li>
            <li><a href="#reviews">Đánh giá</a></li>
            <li><a href="#faq">Hỏi đáp</a></li>
            <li><a href="#contact">Liên hệ</a></li>
          </ul>
        </nav>

        <a href="<?= $telUrl ?>" class="btn btn-dark btn-sm header-call" data-track="call">
          <?= icon('phone') ?><span><?= SITE_PHONE_DISPLAY ?></span>
        </a>
        <button class="nav-toggle" aria-label="Mở menu" aria-controls="main-nav" aria-expanded="false">
          <?= icon('menu', 'icon-open') ?><?= icon('close', 'icon-close') ?>
        </button>
      </div>
    </header>

    <main>
      <!-- HERO -->
      <section class="hero">
        <div class="container hero-inner">
          <div class="hero-copy">
            <p class="eyebrow"><?= icon('fire') ?> Hot trend <?= $year ?> · Nike · Adidas · New Balance</p>
            <h1>Giày sneaker nam nữ <span class="hl">đẹp chuẩn form</span>, giá cực tốt</h1>
            <p class="hero-lead">
              Full box, đủ phụ kiện. Ship COD toàn quốc, <strong>được mở hộp kiểm tra trước khi trả tiền</strong>,
              đổi size trong 7 ngày. Nhắn Zalo để shop tư vấn size miễn phí.
            </p>
            <div class="hero-actions">
              <a href="#products" class="btn btn-primary btn-lg">Xem mẫu đang có <?= icon('arrow-right') ?></a>
              <a href="<?= $zaloUrl ?>" class="btn btn-outline-light btn-lg" target="_blank" rel="nofollow noopener" data-track="zalo">
                <?= icon('chat') ?> Tư vấn qua Zalo
              </a>
            </div>
            <ul class="hero-stats">
              <?php if ($totalSold > 0): ?>
              <li><strong><?= number_format($totalSold, 0, ',', '.') ?>+</strong><span>đôi đã bán</span></li>
              <?php endif; ?>
              <?php if (count($products) > 0): ?>
              <li><strong><?= count($products) ?>+</strong><span>mẫu có sẵn</span></li>
              <?php endif; ?>
              <li><strong>7 ngày</strong><span>đổi size</span></li>
              <li><strong>3 tháng</strong><span>bảo hành keo, chỉ</span></li>
            </ul>
          </div>

          <div class="hero-visual">
            <div class="hero-blob" aria-hidden="true"></div>
            <img
              src="<?= e($heroImage) ?>"
              alt="<?= e($featured ? 'Giày ' . $featured['name'] . ' - TheAnhSneaker' : 'TheAnhSneaker') ?>"
              width="640"
              height="640"
              fetchpriority="high"
              decoding="async"
            />
            <?php if ($featured): ?>
            <div class="hero-card">
              <span class="hero-card-label">Mẫu mới về</span>
              <strong><?= e($featured['name']) ?></strong>
              <span class="hero-card-price">
                <?= format_vnd($featured['price_new']) ?>
                <?php if (format_vnd($featured['price_old'])): ?><s><?= format_vnd($featured['price_old']) ?></s><?php endif; ?>
              </span>
            </div>
            <?php endif; ?>
            <div class="hero-badge"><?= icon('shield') ?> Kiểm tra rồi mới trả tiền</div>
          </div>
        </div>
      </section>

      <!-- TRUST BAR -->
      <section class="trust" aria-label="Cam kết dịch vụ">
        <div class="container trust-grid">
          <div class="trust-item">
            <?= icon('truck') ?>
            <div><strong>Giao hàng toàn quốc</strong><span>Hà Nội 1-2 ngày</span></div>
          </div>
          <div class="trust-item">
            <?= icon('box') ?>
            <div><strong>Full box &amp; phụ kiện</strong><span>Đóng gói cẩn thận</span></div>
          </div>
          <div class="trust-item">
            <?= icon('refresh') ?>
            <div><strong>Đổi size 7 ngày</strong><span>Còn mới, đủ hộp</span></div>
          </div>
          <div class="trust-item">
            <?= icon('headset') ?>
            <div><strong>Tư vấn 8h - 22h</strong><span>Zalo &amp; hotline</span></div>
          </div>
        </div>
      </section>

      <!-- PRODUCTS -->
      <section id="products" class="section products-section">
        <div class="container">
          <div class="section-head reveal">
            <p class="section-kicker">Bộ sưu tập</p>
            <h2>Giày sneaker hot trend đang có sẵn</h2>
            <p class="section-sub">Bấm <strong>“Đặt qua Zalo”</strong>, tên mẫu sẽ được tự động sao chép, bạn chỉ cần dán vào Zalo kèm size để đặt nhanh.</p>
          </div>

          <div class="tabs reveal" role="group" aria-label="Lọc sản phẩm theo thương hiệu">
            <button class="tab-link active" data-filter="all" aria-pressed="true">Tất cả</button>
            <button class="tab-link" data-filter="nike" aria-pressed="false">Nike</button>
            <button class="tab-link" data-filter="adidas" aria-pressed="false">Adidas</button>
            <button class="tab-link" data-filter="new-balance" aria-pressed="false">New Balance</button>
            <button class="tab-link" data-filter="other" aria-pressed="false">Khác</button>
          </div>

          <div class="product-grid" data-per-load="<?= $itemsPerLoad ?>">
            <?php if (!$products): ?>
            <p class="product-empty">Hiện chưa có sản phẩm nào. Nhắn Zalo để shop gửi mẫu mới nhất nhé!</p>
            <?php endif; ?>
            <?php foreach ($products as $i => $p):
                $brand = $brandNames[$p['category']] ?? '';
                $alt = trim('Giày ' . $p['name'] . ($brand && stripos($p['name'], $brand) === false ? ' ' . $brand : '') . ' - ' . SITE_NAME);
                $discount = discount_percent($p);
            ?>
            <article class="product-card<?= $i < $itemsPerLoad ? ' show' : '' ?>" data-category="<?= e($p['category']) ?>">
              <div class="product-media">
                <img
                  src="<?= e(optimized_image($p['image_url'], 600)) ?>"
                  alt="<?= e($alt) ?>"
                  width="600"
                  height="600"
                  loading="lazy"
                  decoding="async"
                />
                <div class="product-badges">
                  <?php if (!empty($p['tag'])): ?><span class="badge badge-hot"><?= e($p['tag']) ?></span><?php endif; ?>
                  <?php if ($discount > 0): ?><span class="badge badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                </div>
              </div>
              <div class="product-body">
                <span class="product-brand"><?= e($brand ?: 'Sneaker') ?></span>
                <h3 class="product-name"><?= e($p['name']) ?></h3>
                <?php if ((int) $p['sold_count'] > 0): ?>
                <p class="product-sold"><?= icon('fire') ?> Đã bán <?= (int) $p['sold_count'] ?></p>
                <?php endif; ?>
                <div class="product-price">
                  <strong><?= format_vnd($p['price_new']) ?: 'Liên hệ' ?></strong>
                  <?php if (format_vnd($p['price_old'])): ?><s><?= format_vnd($p['price_old']) ?></s><?php endif; ?>
                </div>
                <a
                  href="<?= $zaloUrl ?>"
                  class="btn btn-primary btn-block"
                  target="_blank"
                  rel="nofollow noopener"
                  data-track="zalo"
                  data-product="<?= e($p['name']) ?>"
                  data-product-id="TAS-<?= (int) $p['id'] ?>"
                  data-price="<?= (float) $p['price_new'] ?>"
                ><?= icon('chat') ?> Đặt qua Zalo</a>
              </div>
            </article>
            <?php endforeach; ?>
          </div>

          <div class="load-more-wrap">
            <button id="load-more-btn" class="btn btn-outline btn-lg"<?= count($products) <= $itemsPerLoad ? ' hidden' : '' ?>>
              Xem thêm sản phẩm
            </button>
          </div>
        </div>
      </section>

      <!-- WHY US -->
      <section id="why" class="section section-dark">
        <div class="container">
          <div class="section-head reveal">
            <p class="section-kicker">Vì sao chọn TheAnhSneaker</p>
            <h2>Mua giày online mà yên tâm như mua tại shop</h2>
          </div>
          <div class="why-grid">
            <div class="why-card reveal">
              <span class="why-icon"><?= icon('eye') ?></span>
              <h3>Kiểm tra trước, trả tiền sau</h3>
              <p>Đồng kiểm với shipper: đúng mẫu, đúng size, không lỗi mới thanh toán. Sai mẫu được từ chối nhận, không mất phí.</p>
            </div>
            <div class="why-card reveal">
              <span class="why-icon"><?= icon('refresh') ?></span>
              <h3>Đổi size trong 7 ngày</h3>
              <p>Đi không vừa? Đổi size hoặc đổi mẫu khác trong 7 ngày, sản phẩm còn mới và đủ hộp.</p>
            </div>
            <div class="why-card reveal">
              <span class="why-icon"><?= icon('shield') ?></span>
              <h3>Bảo hành 3 tháng</h3>
              <p>Bảo hành keo, chỉ cho mọi sản phẩm. Shop sửa trong 5 - 7 ngày và hỗ trợ ship chiều về.</p>
            </div>
            <div class="why-card reveal">
              <span class="why-icon"><?= icon('tag') ?></span>
              <h3>Giá tốt, cập nhật trend</h3>
              <p>Mẫu hot trend Nike, Adidas, New Balance về liên tục với mức giá cạnh tranh nhất thị trường.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- HOW TO ORDER -->
      <section class="section steps-section">
        <div class="container">
          <div class="section-head reveal">
            <p class="section-kicker">Đặt hàng siêu nhanh</p>
            <h2>Chỉ 3 bước là có giày mới</h2>
          </div>
          <ol class="steps">
            <li class="step reveal">
              <span class="step-num">1</span>
              <h3>Chọn mẫu ưng ý</h3>
              <p>Lướt bộ sưu tập và bấm “Đặt qua Zalo” ở mẫu bạn thích.</p>
            </li>
            <li class="step reveal">
              <span class="step-num">2</span>
              <h3>Nhắn size &amp; địa chỉ</h3>
              <p>Gửi tên mẫu, size (hoặc số đo chân) và địa chỉ. Shop xác nhận ngay.</p>
            </li>
            <li class="step reveal">
              <span class="step-num">3</span>
              <h3>Nhận hàng, kiểm tra, trả tiền</h3>
              <p>Shipper giao tận nơi, bạn mở hộp kiểm tra rồi mới thanh toán.</p>
            </li>
          </ol>
          <div class="steps-cta reveal">
            <a href="<?= $zaloUrl ?>" class="btn btn-primary btn-lg" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?> Nhắn Zalo đặt hàng</a>
            <a href="./huong-dan-chon-size/" class="btn btn-ghost btn-lg"><?= icon('ruler') ?> Hướng dẫn chọn size</a>
          </div>
        </div>
      </section>

      <!-- REVIEWS -->
      <section id="reviews" class="section section-soft">
        <div class="container">
          <div class="section-head reveal">
            <p class="section-kicker">Khách hàng nói gì</p>
            <h2>Được khách hàng khắp cả nước tin tưởng</h2>
          </div>
          <div class="reviews">
            <?php foreach ($testimonials as $t): ?>
            <figure class="review-card reveal">
              <?= icon('quote', 'review-quote') ?>
              <div class="review-stars" role="img" aria-label="<?= $t['stars'] ?> trên 5 sao">
                <?php for ($s = 1; $s <= 5; $s++): ?><?= icon('star', $s <= $t['stars'] ? '' : 'star-off') ?><?php endfor; ?>
              </div>
              <blockquote><p><?= e($t['text']) ?></p></blockquote>
              <figcaption>
                <img src="<?= e(optimized_image($t['img'], 120)) ?>" alt="Ảnh khách hàng <?= e($t['name']) ?>" width="56" height="56" loading="lazy" decoding="async" />
                <span><strong><?= e($t['name']) ?></strong><small><?= e($t['city']) ?></small></span>
              </figcaption>
            </figure>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section id="faq" class="section">
        <div class="container faq-wrap">
          <div class="section-head section-head-left reveal">
            <p class="section-kicker">Hỏi đáp</p>
            <h2>Câu hỏi thường gặp</h2>
            <p class="section-sub">Chưa thấy câu trả lời? Nhắn Zalo hoặc gọi <a href="<?= $telUrl ?>" data-track="call"><?= SITE_PHONE_DISPLAY ?></a>, shop phản hồi trong vài phút.</p>
            <ul class="policy-links">
              <li><a href="./chinh-sach-van-chuyen/">Chính sách vận chuyển <?= icon('arrow-right') ?></a></li>
              <li><a href="./chinh-sach-doi-tra/">Chính sách đổi trả <?= icon('arrow-right') ?></a></li>
              <li><a href="./chinh-sach-bao-hanh/">Chính sách bảo hành <?= icon('arrow-right') ?></a></li>
              <li><a href="./huong-dan-chon-size/">Hướng dẫn chọn size <?= icon('arrow-right') ?></a></li>
            </ul>
          </div>
          <div class="faq-list reveal">
            <?php foreach ($faqs as $i => $f): ?>
            <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
              <summary><?= e($f['q']) ?><?= icon('plus') ?></summary>
              <p><?= e($f['a']) ?></p>
            </details>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- CTA BAND -->
      <section class="cta-band">
        <div class="container cta-band-inner reveal">
          <div>
            <h2>Chưa biết chọn mẫu nào, size nào?</h2>
            <p>Gửi số đo chân và phong cách bạn thích, shop gợi ý mẫu và size chuẩn miễn phí.</p>
          </div>
          <div class="cta-band-actions">
            <a href="<?= $zaloUrl ?>" class="btn btn-light btn-lg" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?> Chat Zalo ngay</a>
            <a href="<?= $telUrl ?>" class="btn btn-outline-light btn-lg" data-track="call"><?= icon('phone') ?> <?= SITE_PHONE_DISPLAY ?></a>
          </div>
        </div>
      </section>

      <!-- CONTACT -->
      <section id="contact" class="section">
        <div class="container contact-grid">
          <div class="contact-info reveal">
            <p class="section-kicker">Ghé shop</p>
            <h2>Trải nghiệm trực tiếp tại cửa hàng</h2>
            <ul class="contact-list">
              <li><?= icon('pin') ?><span><strong>Địa chỉ</strong><?= SITE_STREET ?>, <?= SITE_DISTRICT ?>, <?= SITE_CITY ?></span></li>
              <li><?= icon('phone') ?><span><strong>Hotline / Zalo</strong><a href="<?= $telUrl ?>" data-track="call"><?= SITE_PHONE_DISPLAY ?></a></span></li>
              <li><?= icon('clock') ?><span><strong>Giờ mở cửa</strong>08:00 - 22:00, tất cả các ngày</span></li>
            </ul>
            <a class="btn btn-dark" href="https://maps.google.com/?q=<?= SITE_LAT ?>,<?= SITE_LNG ?>" target="_blank" rel="noopener"><?= icon('pin') ?> Chỉ đường Google Maps</a>
          </div>
          <div class="map-container reveal">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.933315992895!2d105.83166981540024!3d20.94206018603831!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ad960b94358d%3A0x861a38489791b6f0!2zQ2jDuWEgVmnhu4d0IFnDqm4!5e0!3m2!1svi!2s!4v1663489897123!5m2!1svi!2s"
              width="600"
              height="420"
              style="border: 0"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="Bản đồ đường đến TheAnhSneaker - Chùa Việt Yên, Ngũ Hiệp, Thanh Trì, Hà Nội"
            ></iframe>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="container footer-grid">
        <div class="footer-col footer-about">
          <a href="./" class="logo logo-light">TheAnh<span>Sneaker</span></a>
          <p>Shop giày sneaker nam nữ hot trend tại Hà Nội. Chất lượng tốt, giá hợp lý, ship COD toàn quốc và hỗ trợ đổi size tận tâm.</p>
          <div class="social-icons">
            <a href="https://www.facebook.com/inhtheanh.881012" aria-label="Facebook TheAnhSneaker" target="_blank" rel="noopener"><?= icon('facebook') ?></a>
            <a href="https://www.tiktok.com/@tradebolive" aria-label="TikTok TheAnhSneaker" target="_blank" rel="noopener"><?= icon('tiktok') ?></a>
            <a href="<?= $zaloUrl ?>" aria-label="Zalo TheAnhSneaker" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?></a>
          </div>
        </div>
        <div class="footer-col">
          <p class="footer-title">Chính sách</p>
          <ul>
            <li><a href="./chinh-sach-van-chuyen/">Chính sách vận chuyển</a></li>
            <li><a href="./chinh-sach-doi-tra/">Chính sách đổi trả</a></li>
            <li><a href="./chinh-sach-bao-hanh/">Chính sách bảo hành</a></li>
            <li><a href="./huong-dan-chon-size/">Hướng dẫn chọn size</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <p class="footer-title">Liên hệ</p>
          <address>
            <?= SITE_STREET ?>, <?= SITE_DISTRICT ?>, <?= SITE_CITY ?><br />
            Hotline/Zalo: <a href="<?= $telUrl ?>" data-track="call"><?= SITE_PHONE_DISPLAY ?></a><br />
            Mở cửa: 08:00 - 22:00 hằng ngày
          </address>
        </div>
      </div>
      <div class="container footer-bottom">
        <p>© <?= $year ?> TheAnhSneaker. Bảo lưu mọi quyền.</p>
        <a href="./admin/login.php" rel="nofollow">Quản trị</a>
      </div>
    </footer>

    <!-- Nút liên hệ nổi (desktop) -->
    <div class="float-contact">
      <a href="<?= $zaloUrl ?>" class="float-btn float-zalo" target="_blank" rel="nofollow noopener" data-track="zalo" aria-label="Chat Zalo">Zalo</a>
      <a href="<?= $telUrl ?>" class="float-btn float-call" data-track="call" aria-label="Gọi <?= SITE_PHONE_DISPLAY ?>"><?= icon('phone') ?></a>
    </div>

    <!-- Thanh liên hệ cố định (mobile) -->
    <div class="mobile-cta">
      <a href="<?= $telUrl ?>" class="mobile-cta-call" data-track="call"><?= icon('phone') ?> Gọi ngay</a>
      <a href="<?= $zaloUrl ?>" class="mobile-cta-zalo" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?> Chat Zalo</a>
    </div>

    <button id="scrollToTopBtn" class="to-top" aria-label="Lên đầu trang" hidden><?= icon('arrow-up') ?></button>
    <div class="toast" role="status" aria-live="polite"></div>
  </body>
</html>
