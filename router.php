<?php
/**
 * CHỈ dùng khi chạy local bằng server có sẵn của PHP:
 *   php -S 127.0.0.1:8080 router.php
 * Mô phỏng các rule rewrite trong .htaccess (Apache trên hosting không dùng file này).
 */
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Chặn file/thư mục ẩn và file cấu hình như trên hosting
if (preg_match('#(^|/)\.(?!well-known/)|^/(config|config\.example|db_connect_pdo|router)\.php$|^/(includes|database)/#', $path)) {
    http_response_code(404);
    echo 'Not Found';
    return true;
}

$routes = [
    '#^/robots\.txt$#' => 'robots.php',
    '#^/sitemap\.xml$#' => 'sitemap.php',
];
foreach ($routes as $pattern => $script) {
    if (preg_match($pattern, $path)) {
        require __DIR__ . '/' . $script;
        return true;
    }
}

if (preg_match('#^/san-pham/([a-z0-9-]+-[0-9]+)$#', $path, $m)) {
    header('Location: /san-pham/' . $m[1] . '/', true, 301);
    return true;
}
if (preg_match('#^/san-pham/([a-z0-9-]+)-([0-9]+)/$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    $_GET['id'] = $m[2];
    require __DIR__ . '/san-pham.php';
    return true;
}

// URL thư mục -> index.php của thư mục đó (vd: /chinh-sach-doi-tra/)
if (substr($path, -1) === '/' && is_file(__DIR__ . $path . 'index.php')) {
    return false;
}

return false; // file tĩnh hoặc file .php có thật: để server PHP xử lý như bình thường
