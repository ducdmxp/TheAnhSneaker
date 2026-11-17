<?php
/**
 * Facebook Conversions API: nhận sự kiện từ script.js (navigator.sendBeacon) và gửi lên Facebook
 * từ server, cùng event_id với Pixel để Facebook khử trùng. Bù lại sự kiện bị mất do iOS/chặn quảng cáo.
 *
 * Bật bằng cách điền 'fb_capi_token' trong config.php.
 */
require_once __DIR__ . '/../includes/tracking.php';

// Phiên bản Graph API. Facebook giữ mỗi phiên bản ~2 năm; khi hết hạn, đổi sang phiên bản mới nhất
// (xem developers.facebook.com/docs/graph-api/changelog).
const FB_GRAPH_VERSION = 'v24.0';
const ALLOWED_EVENTS = ['PageView', 'ViewContent', 'Contact'];

header('X-Robots-Tag: noindex');

function done(int $code): void
{
    http_response_code($code);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !capi_enabled()) {
    done(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 204 : 405);
}

// Chỉ nhận sự kiện từ chính website (chặn trang khác gửi rác vào số liệu quảng cáo).
$host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
if ($origin === '' || strtolower((string) parse_url($origin, PHP_URL_HOST)) !== $host) {
    done(403);
}

$raw = file_get_contents('php://input', false, null, 0, 4096);
$in = json_decode((string) $raw, true);
if (!is_array($in) || !in_array($in['event_name'] ?? '', ALLOWED_EVENTS, true)) {
    done(400);
}
$eventId = (string) ($in['event_id'] ?? '');
if (!preg_match('/^[A-Za-z0-9-]{8,64}$/', $eventId)) {
    done(400);
}

// URL trang phải thuộc website này.
$url = (string) ($in['url'] ?? '');
if (strtolower((string) parse_url($url, PHP_URL_HOST)) !== $host) {
    $url = site_base_url();
}

// custom_data: chỉ giữ các khóa hợp lệ.
$custom = [];
$c = is_array($in['custom'] ?? null) ? $in['custom'] : [];
foreach (['content_name', 'content_type', 'content_category', 'currency'] as $k) {
    if (isset($c[$k]) && is_scalar($c[$k])) {
        $custom[$k] = mb_substr((string) $c[$k], 0, 200);
    }
}
if (isset($c['value']) && is_numeric($c['value'])) {
    $custom['value'] = (float) $c['value'];
}
if (isset($c['content_ids']) && is_array($c['content_ids'])) {
    $custom['content_ids'] = array_slice(array_map(fn ($v) => mb_substr((string) $v, 0, 50), $c['content_ids']), 0, 10);
}

// user_data: thông tin kỹ thuật giúp Facebook ghép người dùng (không thu thập SĐT/email).
$user = [
    'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    'client_user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
];
if (!empty($_COOKIE['_fbp']) && preg_match('/^fb\.\d+\.\d+\.\d+$/', $_COOKIE['_fbp'])) {
    $user['fbp'] = $_COOKIE['_fbp'];
}
if (!empty($_COOKIE['_fbc']) && preg_match('/^fb\.\d+\.\d+\.[A-Za-z0-9_-]+$/', $_COOKIE['_fbc'])) {
    $user['fbc'] = $_COOKIE['_fbc'];
} else {
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    if (!empty($q['fbclid']) && preg_match('/^[A-Za-z0-9_-]+$/', $q['fbclid'])) {
        $user['fbc'] = 'fb.1.' . round(microtime(true) * 1000) . '.' . $q['fbclid'];
    }
}

$payload = [
    'data' => [[
        'event_name' => $in['event_name'],
        'event_time' => time(),
        'event_id' => $eventId,
        'event_source_url' => $url,
        'action_source' => 'website',
        'user_data' => $user,
        'custom_data' => (object) $custom,
    ]],
];
$testCode = (string) config_value('fb_test_event_code');
if ($testCode !== '') {
    $payload['test_event_code'] = $testCode;
}

// Trả lời trình duyệt ngay, gửi lên Facebook sau (không làm chậm trang).
http_response_code(204);
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

$endpoint = 'https://graph.facebook.com/' . FB_GRAPH_VERSION . '/' . FB_PIXEL_ID . '/events?access_token='
    . rawurlencode((string) config_value('fb_capi_token'));
$body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (function_exists('curl_init')) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $body,
        'timeout' => 5,
        'ignore_errors' => true,
    ]]);
    $resp = @file_get_contents($endpoint, false, $ctx);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }
}
if ($status < 200 || $status >= 300) {
    error_log('[capi] Facebook trả lỗi ' . $status . ': ' . mb_substr((string) $resp, 0, 500));
}
