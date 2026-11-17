<?php
require_once __DIR__ . '/includes/seo.php';

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

function format_vnd($price): string
{
    return is_numeric($price) && $price > 0 ? number_format((float) $price, 0, ',', '.') . ' ₫' : '';
}

$pageTitle = "Giày Sneaker Nam Nữ Hot Trend {$year} Giá Rẻ Hà Nội | TheAnhSneaker";
$pageDescription = "TheAnhSneaker chuyên giày sneaker nam nữ Nike, Adidas, New Balance hot trend {$year}, giá rẻ, full box, ship COD toàn quốc, bảo hành uy tín. Ghé shop tại Ngũ Hiệp, Thanh Trì, Hà Nội.";
$ogImage = !empty($products[0]['image_url']) ? $products[0]['image_url'] : 'img/logo-512.png';

// Dữ liệu cấu trúc (JSON-LD)
$productItems = [];
foreach ($products as $i => $p) {
    $offer = [
        '@type' => 'Offer',
        'url' => site_url('#products'),
        'priceCurrency' => 'VND',
        'price' => (float) $p['price_new'],
        'availability' => 'https://schema.org/InStock',
        'itemCondition' => 'https://schema.org/NewCondition',
        'seller' => ['@id' => site_url('#store')],
    ];
    $productItems[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => [
            '@type' => 'Product',
            'name' => $p['name'],
            'image' => site_url($p['image_url']),
            'sku' => 'TAS-' . $p['id'],
            'brand' => ['@type' => 'Brand', 'name' => $brandNames[$p['category']] ?? SITE_NAME],
            'offers' => $offer,
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
];
if ($productItems) {
    $graph[] = [
        '@type' => 'ItemList',
        'name' => 'Bộ sưu tập sneaker TheAnhSneaker',
        'numberOfItems' => count($productItems),
        'itemListElement' => $productItems,
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php seo_meta([
        'title' => $pageTitle,
        'description' => $pageDescription,
        'path' => '',
        'image' => $ogImage,
    ]); ?>
    <meta
      name="keywords"
      content="sneaker giá rẻ, theanhsneaker, giày sneaker hà nội, sneaker hot trend, giày nike, giày adidas, giày new balance"
    />
    <?= json_ld(['@context' => 'https://schema.org', '@graph' => $graph]) ?>
    <link rel="preconnect" href="https://images.unsplash.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <link rel="stylesheet" href="style.css" />
    <script src="script.js" defer></script>

<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1228037999368255');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1228037999368255&ev=PageView&noscript=1"
    />
  </noscript>

  </head>
  <body>
    <header class="header">
      <div class="container">
        <a href="./" class="logo" aria-label="TheAnhSneaker - Trang chủ">TheAnhSneaker</a>
        <div class="nav-wrapper">
          <nav class="main-nav">
            <ul>
              <li><a href="#products">Sản Phẩm</a></li>
              <li><a href="#testimonials">Feedback</a></li>
              <li><a href="#contact">Liên Hệ</a></li>
            </ul>
          </nav>
          <!-- <a href="tel:0981936021" class="hotline">
            <i class="fa-solid fa-phone"></i>
            <span>0981.936.021</span>
          </a> -->

          <a href="./admin/login.php" class="hotline" rel="nofollow">
            <span>Đăng Nhập</span>
          </a>
        </div>
        <button class="mobile-nav-toggle" aria-label="Toggle navigation">
          <span class="hamburger"></span>
        </button>
      </div>
    </header>

    <main>
    <section class="hero">
      <div class="container">
        <h1>SĂN SALE GIÀY SNEAKER<br />HOT NHẤT <?= $year ?></h1>
        <p>
          Bắt trọn mọi trend giày đỉnh cao với mức giá rẻ nhất thị trường.
          <br />Cam kết chất lượng, bảo hành uy tín.
        </p>
        <a href="#products" class="cta-button">Khám Phá Ngay</a>
      </div>
    </section>

    <section class="commitments">
      <div class="container reveal">
        <div class="commitment-item">
          <i class="fa-solid fa-truck-fast"></i>
          <h3>Giao Hàng Siêu Tốc</h3>
          <p>Ship COD toàn quốc</p>
        </div>
        <div class="commitment-item">
          <i class="fa-solid fa-box-open"></i>
          <h3>Full Box & Phụ Kiện</h3>
          <p>Đầy đủ như chính hãng</p>
        </div>
        <div class="commitment-item">
          <i class="fa-solid fa-check-circle"></i>
          <h3>Cam Kết Giá Rẻ Nhất</h3>
          <p>Chất lượng vượt trội</p>
        </div>
        <div class="commitment-item">
          <i class="fa-solid fa-headset"></i>
          <h3>Hỗ Trợ Tư Vấn 24/7</h3>
          <p>Nhiệt tình, chu đáo</p>
        </div>
      </div>
    </section>

    <section id="products" class="products-section">
      <div class="container">
        <h2 class="reveal">BỘ SƯU TẬP GIÀY SNEAKER ĐỈNH CAO</h2>

        <div class="tabs reveal" role="group" aria-label="Lọc sản phẩm theo thương hiệu">
          <button class="tab-link active" data-filter="all">Tất Cả</button>
          <button class="tab-link" data-filter="nike">Nike</button>
          <button class="tab-link" data-filter="adidas">Adidas</button>
          <button class="tab-link" data-filter="new-balance">
            New Balance
          </button>
          <button class="tab-link" data-filter="other">Khác</button>
        </div>

        <div class="product-grid" data-per-load="<?= $itemsPerLoad ?>">
          <?php if (!$products): ?>
          <p class="product-empty" style="text-align: center; width: 100%;">Hiện chưa có sản phẩm nào.</p>
          <?php endif; ?>
          <?php foreach ($products as $i => $p):
              $brand = $brandNames[$p['category']] ?? '';
              $alt = trim('Giày ' . $p['name'] . ($brand && stripos($p['name'], $brand) === false ? ' ' . $brand : '') . ' - ' . SITE_NAME);
          ?>
          <article class="product-card<?= $i < $itemsPerLoad ? ' show' : '' ?>" data-category="<?= e($p['category']) ?>">
            <div class="product-image">
              <img
                src="<?= e($p['image_url']) ?>"
                alt="<?= e($alt) ?>"
                width="400"
                height="400"
                <?= $i < 4 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                decoding="async"
              />
              <?php if (!empty($p['tag'])): ?><span class="product-tag"><?= e($p['tag']) ?></span><?php endif; ?>
            </div>
            <div class="product-info">
              <h3><?= e($p['name']) ?></h3>
              <div class="product-rating" aria-hidden="true">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-alt"></i>
              </div>
              <?php if ((int) $p['sold_count'] > 0): ?>
              <div class="product-sold-count">
                <i class="fa-solid fa-fire" aria-hidden="true"></i>
                <span>Đã bán <?= (int) $p['sold_count'] ?></span>
              </div>
              <?php endif; ?>
              <div class="product-price">
                <span class="new-price"><?= format_vnd($p['price_new']) ?></span>
                <?php if (format_vnd($p['price_old'])): ?><span class="old-price"><?= format_vnd($p['price_old']) ?></span><?php endif; ?>
              </div>
              <a href="https://zalo.me/<?= SITE_PHONE ?>" class="buy-button" target="_blank" rel="nofollow noopener">Mua Ngay Qua Zalo</a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <button id="load-more-btn" class="reveal"<?= count($products) <= $itemsPerLoad ? ' style="display: none"' : '' ?>>Xem Thêm Sản Phẩm</button>
      </div>
    </section>
    <section id="testimonials" class="testimonials-section">
      <div class="container">
        <h2 class="reveal">KHÁCH HÀNG TIN TƯỞNG VÀ ĐÁNH GIÁ</h2>

        <div class="testimonial-slider-wrapper reveal">
          <div class="testimonial-slider">
            <div class="testimonial-track">
              <div class="testimonial-card">
                <img
                  src="./img/dangvantinh.png"
                  alt="Ảnh feedback của anh Đặng Văn Tỉnh"
                />
                <h4>Anh Đặng Văn Tỉnh (Hà Nội)</h4>
                <div class="testimonial-rating">
                  <i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i>
                </div>
                <p>
                  "Giày đẹp xuất sắc, còn thơm mùi keo mới. Shop tư vấn size rất
                  chuẩn, đi vừa in. Sẽ là khách quen của TheAnhSneaker!"
                </p>
              </div>

              <div class="testimonial-card">
                <img
                  src="./img/minhhang.png"
                  alt="Ảnh feedback của chị Minh Hằng"
                />
                <h4>Chị Minh Hằng (TP.HCM)</h4>
                <div class="testimonial-rating">
                  <i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i>
                </div>
                <p>
                  "Giao hàng nhanh kinh khủng. Mình ở SG mà 2 ngày đã nhận được.
                  Giày đi êm, chuẩn form, bạn bè ai cũng khen. 10 điểm cho
                  shop!"
                </p>
              </div>

              <div class="testimonial-card">
                <img
                  src="./img/quanghuy.png"
                  alt="Ảnh feedback của bạn Quang Huy"
                />
                <h4>Bạn Quang Huy (Đà Nẵng)</h4>
                <div class="testimonial-rating">
                  <i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star-half-alt"></i>
                </div>
                <p>
                  "Giá quá tốt so với chất lượng. Check legit thoải mái. Mình đã
                  mua 2 đôi và rất hài lòng. Cảm ơn shop nhiều."
                </p>
              </div>

              <div class="testimonial-card">
                <img
                  src="./img/phuongthao.png"
                  alt="Ảnh feedback của chị Phương Thảo"
                />
                <h4>Chị Phương Thảo (Hải Phòng)</h4>
                <div class="testimonial-rating">
                  <i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i
                  ><i class="fa-solid fa-star"></i>
                </div>
                <p>
                  "Lần đầu mua online mà ưng ý đến vậy. Shop đóng gói cẩn thận,
                  hộp giày không bị móp méo. Chắc chắn sẽ giới thiệu cho bạn
                  bè."
                </p>
              </div>
            </div>
          </div>

          <button class="slider-btn prev-btn" aria-label="Previous testimonial">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button class="slider-btn next-btn" aria-label="Next testimonial">
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          <div class="slider-dots"></div>
        </div>
      </div>
    </section>
    <section id="contact" class="contact-section">
      <div class="container">
        <h2 class="reveal">GHÉ SHOP TRẢI NGHIỆM TRỰC TIẾP</h2>
        <p class="address reveal">
          <i class="fa-solid fa-map-marker-alt"></i> Chùa Việt Yên, Ngũ Hiệp,
          Thanh Trì, Hà Nội
        </p>
        <div class="map-container reveal">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.933315992895!2d105.83166981540024!3d20.94206018603831!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ad960b94358d%3A0x861a38489791b6f0!2zQ2jDuWEgVmnhu4d0IFnDqm4!5e0!3m2!1svi!2s!4v1663489897123!5m2!1svi!2s"
            width="100%"
            height="450"
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
      <div class="container">
        <div class="footer-grid">
          <div class="footer-col">
            <h3>VỀ THEANHSNEAKER</h3>
            <p>
              Địa chỉ uy tín chuyên cung cấp các mẫu giày sneaker hot trend, giá
              tốt nhất. Chúng tôi luôn đặt chất lượng và sự hài lòng của khách
              hàng lên hàng đầu.
            </p>
            <p><strong>Hotline:</strong> <a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE_DISPLAY ?></a></p>
            <p>
              <strong>Địa chỉ:</strong> Chùa Việt Yên, Ngũ Hiệp, Thanh Trì, Hà
              Nội
            </p>
          </div>
          <div class="footer-col">
            <h3>CHÍNH SÁCH</h3>
            <ul>
              <li>
                <a href="./chinh-sach-van-chuyen/"
                  >Chính sách vận chuyển</a
                >
              </li>
              <li>
                <a href="./chinh-sach-doi-tra/"
                  >Chính sách đổi trả</a
                >
              </li>
              <li>
                <a href="./chinh-sach-bao-hanh/"
                  >Chính sách bảo hành</a
                >
              </li>
              <li>
                <a href="./huong-dan-chon-size/"
                  >Hướng dẫn chọn size</a
                >
              </li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>KẾT NỐI VỚI CHÚNG TÔI</h3>
            <div class="social-icons">
              <a
                href="https://www.facebook.com/inhtheanh.881012"
                aria-label="Facebook"
                target="_blank"
                rel="noopener"
                ><i class="fab fa-facebook-f"></i
              ></a>
              <a href="https://www.tiktok.com/@tradebolive" aria-label="Tiktok" target="_blank" rel="noopener"
                ><i class="fab fa-tiktok"></i
              ></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>© <?= $year ?> Bản quyền thuộc về TheAnhSneaker.</p>
        </div>
      </div>
    </footer>

    <a
      href="https://zalo.me/0981936021"
      class="zalo-chat-button"
      target="_blank"
      rel="nofollow noopener"
      aria-label="Chat Zalo"
    >
      <img
        src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-Zalo-Arc.png"
        alt="Chat Zalo"
        width="60"
        height="60"
        loading="lazy"
      />
    </a>

    <button id="scrollToTopBtn" title="Lên đầu trang" aria-label="Lên đầu trang">
      <i class="fa-solid fa-arrow-up"></i>
    </button>
  </body>
</html>
