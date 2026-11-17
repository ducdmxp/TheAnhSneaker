<?php
// Sitemap động, được phục vụ tại /sitemap.xml (xem .htaccess).
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    require_once __DIR__ . '/db_connect_pdo.php';
    $images = $pdo->query("SELECT DISTINCT image_url FROM products WHERE image_url <> ''")->fetchAll();
} catch (Throwable $ex) {
    $images = [];
}

// Lấy thời gian sửa đổi mới nhất của ảnh sản phẩm làm lastmod cho trang chủ.
$homeMtime = filemtime(__DIR__ . '/index.php');
foreach ($images as $img) {
    $file = __DIR__ . '/' . $img['image_url'];
    if (is_file($file)) {
        $homeMtime = max($homeMtime, filemtime($file));
    }
}

$pages = [
    ['path' => '', 'mtime' => $homeMtime, 'changefreq' => 'daily', 'priority' => '1.0', 'images' => $images],
    ['path' => 'huong-dan-chon-size/', 'file' => 'huong-dan-chon-size/index.php', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['path' => 'chinh-sach-van-chuyen/', 'file' => 'chinh-sach-van-chuyen/index.php', 'changefreq' => 'yearly', 'priority' => '0.4'],
    ['path' => 'chinh-sach-doi-tra/', 'file' => 'chinh-sach-doi-tra/index.php', 'changefreq' => 'yearly', 'priority' => '0.4'],
    ['path' => 'chinh-sach-bao-hanh/', 'file' => 'chinh-sach-bao-hanh/index.php', 'changefreq' => 'yearly', 'priority' => '0.4'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($pages as $page):
    $mtime = $page['mtime'] ?? @filemtime(__DIR__ . '/' . $page['file']);
?>
  <url>
    <loc><?= e(site_url($page['path'])) ?></loc>
<?php if ($mtime): ?>
    <lastmod><?= date('Y-m-d', $mtime) ?></lastmod>
<?php endif; ?>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
<?php foreach ($page['images'] ?? [] as $img): ?>
    <image:image>
      <image:loc><?= e(site_url($img['image_url'])) ?></image:loc>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endforeach; ?>
</urlset>
