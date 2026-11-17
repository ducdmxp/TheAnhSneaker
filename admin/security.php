<?php
/**
 * Bảo mật dùng chung cho trang quản trị:
 * - Session an toàn (HttpOnly, SameSite, Secure khi có HTTPS, hết hạn khi không hoạt động)
 * - Token chống CSRF cho mọi form thay đổi dữ liệu
 * - Giới hạn số lần đăng nhập sai theo IP
 * - Xóa file ảnh an toàn (chỉ trong thư mục uploads/)
 */

const ADMIN_IDLE_TIMEOUT = 7200;       // Tự đăng xuất sau 2 giờ không hoạt động
const LOGIN_MAX_ATTEMPTS = 5;          // Số lần đăng nhập sai tối đa...
const LOGIN_LOCK_SECONDS = 900;        // ...trong 15 phút, vượt quá thì khóa 15 phút

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('TASADMIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();

    // Hết hạn phiên khi không hoạt động quá lâu
    $now = time();
    if (!empty($_SESSION['loggedin']) && isset($_SESSION['last_seen']) && $now - $_SESSION['last_seen'] > ADMIN_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_seen'] = $now;
}

function is_logged_in(): bool
{
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function require_login(): void
{
    admin_session_start();
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    // Trang quản trị không được cache và không được nhúng iframe
    header('Cache-Control: no-store');
    header('X-Frame-Options: SAMEORIGIN');
}

// ---------------- CSRF ----------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** Chỉ chấp nhận POST có token hợp lệ, ngược lại dừng với lỗi 400/405. */
function require_post_with_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Phương thức không hợp lệ.');
    }
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(400);
        exit('Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
    }
}

// ---------------- Giới hạn đăng nhập sai ----------------

function login_attempts_file(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return rtrim(sys_get_temp_dir(), '/\\') . '/tas_login_' . hash('sha256', $ip . __DIR__) . '.json';
}

function login_attempts_read(): array
{
    $file = login_attempts_file();
    $data = is_file($file) ? json_decode((string) @file_get_contents($file), true) : null;
    if (!is_array($data) || time() - ($data['first'] ?? 0) > LOGIN_LOCK_SECONDS) {
        return ['count' => 0, 'first' => time()];
    }
    return $data;
}

/** Số giây còn bị khóa (0 = không bị khóa). */
function login_locked_seconds(): int
{
    $data = login_attempts_read();
    if ($data['count'] < LOGIN_MAX_ATTEMPTS) {
        return 0;
    }
    return max(0, LOGIN_LOCK_SECONDS - (time() - $data['first']));
}

function login_record_failure(): void
{
    $data = login_attempts_read();
    $data['count']++;
    @file_put_contents(login_attempts_file(), json_encode($data), LOCK_EX);
}

function login_reset_attempts(): void
{
    @unlink(login_attempts_file());
}

// ---------------- File ảnh ----------------

/**
 * Xóa ảnh sản phẩm (và các bản WebP thu nhỏ của nó) nếu và chỉ nếu đường dẫn
 * nằm trong thư mục uploads/. Bỏ qua mọi đường dẫn khác.
 */
function delete_product_image(?string $relPath): void
{
    if (!$relPath || !preg_match('#^uploads/[A-Za-z0-9._-]+$#', $relPath)) {
        return;
    }
    $uploadsDir = realpath(__DIR__ . '/../uploads');
    $file = realpath(__DIR__ . '/../' . $relPath);
    if (!$uploadsDir || !$file || dirname($file) !== $uploadsDir || !is_file($file)) {
        return;
    }
    @unlink($file);

    $base = pathinfo($file, PATHINFO_FILENAME);
    foreach (glob($uploadsDir . '/thumbs/' . preg_replace('/[^a-z0-9._-]/i', '_', $base) . '-*.webp') ?: [] as $thumb) {
        @unlink($thumb);
    }
}
