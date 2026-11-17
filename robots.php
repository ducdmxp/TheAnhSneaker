<?php
// robots.txt động (được phục vụ tại /robots.txt qua .htaccess) để dòng Sitemap luôn là URL tuyệt đối đúng tên miền.
require_once __DIR__ . '/includes/seo.php';
header('Content-Type: text/plain; charset=utf-8');
$base = parse_url(site_base_url(), PHP_URL_PATH) ?: '/';
?>
User-agent: *
Allow: /
Disallow: <?= $base ?>admin/
Disallow: <?= $base ?>includes/
Disallow: <?= $base ?>api/

Sitemap: <?= site_url('sitemap.xml') ?>

