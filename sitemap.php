<?php
// Sitemap động, được phục vụ tại /sitemap.xml (xem .htaccess).
require_once __DIR__ . '/includes/catalog.php';

header('Content-Type: application/xml; charset=utf-8');

$products = [];
try {
    require_once __DIR__ . '/db_connect_pdo.php';
    $products = fetch_products($pdo);
} catch (Throwable $ex) {
    error_log('[sitemap] ' . $ex->getMessage());
}

$mtimeOf = function (string $rel): ?int {
    $file = __DIR__ . '/' . $rel;
    return is_file($file) ? filemtime($file) : null;
};

// Trang chủ: lastmod = lần thay đổi gần nhất của trang hoặc ảnh sản phẩm.
$homeMtime = $mtimeOf('index.php');
foreach ($products as $p) {
    $homeMtime = max($homeMtime, $mtimeOf($p['image_url']) ?? 0);
}

$pages = [
    ['path' => '', 'mtime' => $homeMtime, 'changefreq' => 'daily', 'priority' => '1.0', 'images' => []],
];
foreach ($products as $p) {
    $pages[] = [
        'path' => product_path($p),
        'mtime' => max($mtimeOf('san-pham.php') ?? 0, $mtimeOf($p['image_url']) ?? 0) ?: null,
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'images' => $p['image_url'] ? [$p['image_url']] : [],
    ];
}
foreach (['huong-dan-chon-size' => '0.6', 'chinh-sach-van-chuyen' => '0.4', 'chinh-sach-doi-tra' => '0.4', 'chinh-sach-bao-hanh' => '0.4'] as $dir => $priority) {
    $pages[] = ['path' => $dir . '/', 'mtime' => $mtimeOf($dir . '/index.php'), 'changefreq' => 'monthly', 'priority' => $priority, 'images' => []];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?= e(site_url($page['path'])) ?></loc>
<?php if ($page['mtime']): ?>
    <lastmod><?= date('Y-m-d', $page['mtime']) ?></lastmod>
<?php endif; ?>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
<?php foreach ($page['images'] as $img): ?>
    <image:image>
      <image:loc><?= e(site_url($img)) ?></image:loc>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endforeach; ?>
</urlset>
