<?php
/**
 * Phần giao diện dùng chung cho trang chủ và trang sản phẩm.
 */
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/image.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/tracking.php';

function zalo_url(): string
{
    return 'https://zalo.me/' . SITE_PHONE;
}

function tel_url(): string
{
    return 'tel:' . SITE_PHONE;
}

/** CSS, JS và mã đo lường trong <head>. */
function site_assets(): void
{
    $base = site_base_path();
    $root = dirname(__DIR__);
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="<?= $base ?>style.css?v=<?= filemtime($root . '/style.css') ?>" />
    <script src="<?= $base ?>script.js?v=<?= filemtime($root . '/script.js') ?>" defer></script>
    <?php tracking_head();
}

function site_header(): void
{
    $base = site_base_path();
    tracking_body();
    ?>
    <a class="skip-link" href="#main">Bỏ qua tới nội dung chính</a>

    <div class="topbar">
      <div class="container topbar-inner">
        <span><?= icon('truck') ?> Freeship đơn từ 1.000.000đ</span>
        <span class="topbar-hide-sm"><?= icon('wallet') ?> Ship COD toàn quốc</span>
        <span class="topbar-hide-sm"><?= icon('eye') ?> Kiểm tra hàng trước khi thanh toán</span>
      </div>
    </div>

    <header class="header">
      <div class="container header-inner">
        <a href="<?= $base ?>" class="logo" aria-label="TheAnhSneaker - Trang chủ">TheAnh<span>Sneaker</span></a>

        <nav class="main-nav" id="main-nav" aria-label="Điều hướng chính">
          <ul>
            <li><a href="<?= $base ?>#products">Sản phẩm</a></li>
            <li><a href="<?= $base ?>#why">Cam kết</a></li>
            <li><a href="<?= $base ?>#reviews">Đánh giá</a></li>
            <li><a href="<?= $base ?>#faq">Hỏi đáp</a></li>
            <li><a href="<?= $base ?>#contact">Liên hệ</a></li>
          </ul>
        </nav>

        <a href="<?= tel_url() ?>" class="btn btn-dark btn-sm header-call" data-track="call">
          <?= icon('phone') ?><span><?= SITE_PHONE_DISPLAY ?></span>
        </a>
        <button class="nav-toggle" aria-label="Mở menu" aria-controls="main-nav" aria-expanded="false">
          <?= icon('menu', 'icon-open') ?><?= icon('close', 'icon-close') ?>
        </button>
      </div>
    </header>
    <?php
}

function site_footer(): void
{
    $base = site_base_path();
    $year = date('Y');
    ?>
    <footer class="footer">
      <div class="container footer-grid">
        <div class="footer-col footer-about">
          <a href="<?= $base ?>" class="logo logo-light">TheAnh<span>Sneaker</span></a>
          <p>Shop giày sneaker nam nữ hot trend tại Hà Nội. Chất lượng tốt, giá hợp lý, ship COD toàn quốc và hỗ trợ đổi size tận tâm.</p>
          <div class="social-icons">
            <a href="https://www.facebook.com/inhtheanh.881012" aria-label="Facebook TheAnhSneaker" target="_blank" rel="noopener"><?= icon('facebook') ?></a>
            <a href="https://www.tiktok.com/@tradebolive" aria-label="TikTok TheAnhSneaker" target="_blank" rel="noopener"><?= icon('tiktok') ?></a>
            <a href="<?= zalo_url() ?>" aria-label="Zalo TheAnhSneaker" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?></a>
          </div>
        </div>
        <div class="footer-col">
          <p class="footer-title">Chính sách</p>
          <ul>
            <li><a href="<?= $base ?>chinh-sach-van-chuyen/">Chính sách vận chuyển</a></li>
            <li><a href="<?= $base ?>chinh-sach-doi-tra/">Chính sách đổi trả</a></li>
            <li><a href="<?= $base ?>chinh-sach-bao-hanh/">Chính sách bảo hành</a></li>
            <li><a href="<?= $base ?>huong-dan-chon-size/">Hướng dẫn chọn size</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <p class="footer-title">Liên hệ</p>
          <address>
            <?= SITE_STREET ?>, <?= SITE_DISTRICT ?>, <?= SITE_CITY ?><br />
            Hotline/Zalo: <a href="<?= tel_url() ?>" data-track="call"><?= SITE_PHONE_DISPLAY ?></a><br />
            Mở cửa: 08:00 - 22:00 hằng ngày
          </address>
        </div>
      </div>
      <div class="container footer-bottom">
        <p>© <?= $year ?> TheAnhSneaker. Bảo lưu mọi quyền.</p>
        <a href="<?= $base ?>admin/login.php" rel="nofollow">Quản trị</a>
      </div>
    </footer>

    <!-- Nút liên hệ nổi (desktop) -->
    <div class="float-contact">
      <a href="<?= zalo_url() ?>" class="float-btn float-zalo" target="_blank" rel="nofollow noopener" data-track="zalo" aria-label="Chat Zalo">Zalo</a>
      <a href="<?= tel_url() ?>" class="float-btn float-call" data-track="call" aria-label="Gọi <?= SITE_PHONE_DISPLAY ?>"><?= icon('phone') ?></a>
    </div>

    <!-- Thanh liên hệ cố định (mobile) -->
    <div class="mobile-cta">
      <a href="<?= tel_url() ?>" class="mobile-cta-call" data-track="call"><?= icon('phone') ?> Gọi ngay</a>
      <a href="<?= zalo_url() ?>" class="mobile-cta-zalo" target="_blank" rel="nofollow noopener" data-track="zalo"><?= icon('chat') ?> Chat Zalo</a>
    </div>

    <button id="scrollToTopBtn" class="to-top" aria-label="Lên đầu trang" hidden><?= icon('arrow-up') ?></button>
    <div class="toast" role="status" aria-live="polite"></div>
    <?php
}

/** Thuộc tính data-* cho nút Zalo gắn với một sản phẩm (dùng cho đo lường + copy tin nhắn). */
function product_track_attrs(array $p): string
{
    return 'data-track="zalo" data-product="' . e($p['name']) . '" data-product-id="TAS-' . (int) $p['id']
        . '" data-price="' . (float) $p['price_new'] . '"';
}

/** Thẻ sản phẩm trong lưới. */
function product_card(array $p, bool $show = true, string $headingTag = 'h3'): void
{
    $brand = brand_name($p['category']);
    $alt = trim('Giày ' . $p['name'] . ($brand && stripos($p['name'], $brand) === false ? ' ' . $brand : '') . ' - ' . SITE_NAME);
    $discount = discount_percent($p);
    $href = product_href($p);
    ?>
    <article class="product-card<?= $show ? ' show' : '' ?>" data-category="<?= e($p['category']) ?>">
      <a class="product-media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <img
          src="<?= e(site_base_path() . optimized_image($p['image_url'], 600)) ?>"
          alt="<?= e($alt) ?>"
          width="600"
          height="600"
          loading="lazy"
          decoding="async"
        />
        <span class="product-badges">
          <?php if (!empty($p['tag'])): ?><span class="badge badge-hot"><?= e($p['tag']) ?></span><?php endif; ?>
          <?php if ($discount > 0): ?><span class="badge badge-sale">-<?= $discount ?>%</span><?php endif; ?>
        </span>
      </a>
      <div class="product-body">
        <span class="product-brand"><?= e($brand ?: 'Sneaker') ?></span>
        <<?= $headingTag ?> class="product-name"><a href="<?= e($href) ?>"><?= e($p['name']) ?></a></<?= $headingTag ?>>
        <?php if ((int) $p['sold_count'] > 0): ?>
        <p class="product-sold"><?= icon('fire') ?> Đã bán <?= (int) $p['sold_count'] ?></p>
        <?php endif; ?>
        <div class="product-price">
          <strong><?= format_vnd($p['price_new']) ?: 'Liên hệ' ?></strong>
          <?php if (format_vnd($p['price_old'])): ?><s><?= format_vnd($p['price_old']) ?></s><?php endif; ?>
        </div>
        <a href="<?= zalo_url() ?>" class="btn btn-primary btn-block" target="_blank" rel="nofollow noopener" <?= product_track_attrs($p) ?>><?= icon('chat') ?> Đặt qua Zalo</a>
      </div>
    </article>
    <?php
}
