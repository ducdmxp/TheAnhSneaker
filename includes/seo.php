<?php
/**
 * Cấu hình & hàm tiện ích SEO dùng chung cho các trang public.
 *
 * Nếu biết chắc tên miền chính thức, hãy điền vào SITE_URL (không có "/" ở cuối),
 * ví dụ: 'https://theanhsneaker.vn'. Để trống thì hệ thống tự nhận diện theo request.
 */
if (!defined('SITE_URL')) {
    define('SITE_URL', '');
}

const SITE_NAME   = 'TheAnhSneaker';
const SITE_PHONE  = '0981936021';
const SITE_PHONE_DISPLAY = '0981.936.021';
const SITE_STREET = 'Chùa Việt Yên, Ngũ Hiệp';
const SITE_DISTRICT = 'Thanh Trì';
const SITE_CITY   = 'Hà Nội';
const SITE_LAT    = 20.94206;
const SITE_LNG    = 105.83167;
const SITE_SOCIALS = [
    'https://www.facebook.com/inhtheanh.881012',
    'https://www.tiktok.com/@tradebolive',
];

/** URL gốc tuyệt đối của website, luôn kết thúc bằng "/". */
function site_base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    if (SITE_URL !== '') {
        return $base = rtrim(SITE_URL, '/') . '/';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $https ? 'https' : 'http';
    $host = preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');

    // Đường dẫn con (nếu website không đặt ở thư mục gốc của domain).
    $path = '';
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(__DIR__ . '/..');
    if ($docRoot && $appRoot && stripos($appRoot, $docRoot) === 0) {
        $path = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
    }
    $path = '/' . trim($path, '/');

    return $base = $scheme . '://' . $host . rtrim($path, '/') . '/';
}

/** Tạo URL tuyệt đối từ đường dẫn tương đối với thư mục gốc website. */
function site_url(string $path = ''): string
{
    return site_base_url() . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function json_ld(array $data): string
{
    return '<script type="application/ld+json">'
        . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
        . '</script>' . "\n";
}

/**
 * In ra các thẻ meta SEO: description, canonical, robots, Open Graph, Twitter, favicon.
 *
 * $opts: title, description, path (đường dẫn canonical), image (đường dẫn ảnh), type, prefix (tiền tố tương đối tới thư mục gốc cho favicon).
 */
function seo_meta(array $opts): void
{
    $title       = $opts['title'];
    $description = $opts['description'];
    $canonical   = site_url($opts['path'] ?? '');
    $image       = site_url($opts['image'] ?? 'img/logo-512.png');
    $type        = $opts['type'] ?? 'website';
    $prefix      = $opts['prefix'] ?? './';
    ?>
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>" />
    <meta name="robots" content="index, follow, max-image-preview:large" />
    <link rel="canonical" href="<?= e($canonical) ?>" />
    <link rel="alternate" hreflang="vi-VN" href="<?= e($canonical) ?>" />
    <meta name="theme-color" content="#111111" />

    <meta property="og:locale" content="vi_VN" />
    <meta property="og:type" content="<?= e($type) ?>" />
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>" />
    <meta property="og:title" content="<?= e($title) ?>" />
    <meta property="og:description" content="<?= e($description) ?>" />
    <meta property="og:url" content="<?= e($canonical) ?>" />
    <meta property="og:image" content="<?= e($image) ?>" />
    <meta property="og:image:alt" content="<?= e($title) ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($title) ?>" />
    <meta name="twitter:description" content="<?= e($description) ?>" />
    <meta name="twitter:image" content="<?= e($image) ?>" />

    <link rel="icon" href="<?= e($prefix) ?>img/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e($prefix) ?>img/favicon-32.png" />
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e($prefix) ?>img/favicon-192.png" />
    <link rel="apple-touch-icon" href="<?= e($prefix) ?>img/apple-touch-icon.png" />
    <?php
}

/** Dữ liệu cấu trúc Organization/Store dùng lại ở nhiều trang. */
function store_schema(): array
{
    return [
        '@type' => 'ShoeStore',
        '@id' => site_url('#store'),
        'name' => SITE_NAME,
        'url' => site_url(),
        'logo' => site_url('img/logo-512.png'),
        'image' => site_url('img/logo-512.png'),
        'telephone' => '+84' . substr(SITE_PHONE, 1),
        'priceRange' => '₫₫',
        'currenciesAccepted' => 'VND',
        'paymentAccepted' => 'Cash, Bank Transfer',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => SITE_STREET,
            'addressLocality' => SITE_DISTRICT,
            'addressRegion' => SITE_CITY,
            'postalCode' => '100000',
            'addressCountry' => 'VN',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => SITE_LAT,
            'longitude' => SITE_LNG,
        ],
        'hasMap' => 'https://maps.google.com/?q=' . SITE_LAT . ',' . SITE_LNG,
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '08:00',
            'closes' => '22:00',
        ]],
        'sameAs' => SITE_SOCIALS,
    ];
}

/** BreadcrumbList cho trang con: [['Tên', 'đường-dẫn/'], ...] */
function breadcrumb_schema(array $items): array
{
    $list = [['name' => 'Trang chủ', 'path' => '']];
    foreach ($items as [$name, $path]) {
        $list[] = ['name' => $name, 'path' => $path];
    }
    $elements = [];
    foreach ($list as $i => $item) {
        $elements[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => site_url($item['path']),
        ];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $elements];
}
