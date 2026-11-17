<?php
/**
 * Cài đặt lần đầu: tạo bảng và tài khoản admin đầu tiên.
 * - Chỉ dùng được khi CHƯA có tài khoản admin nào.
 * - Phải nhập đúng mật khẩu database (trong config.php) để chứng minh bạn là chủ website.
 */
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/simple_page.php';
admin_session_start();
header('X-Frame-Options: SAMEORIGIN');
header('Cache-Control: no-store');

$error = '';
try {
    require_once __DIR__ . '/../db_connect_pdo.php';
} catch (Throwable $e) {
    error_log('[admin/setup] ' . $e->getMessage());
    simple_page_start('Cài đặt');
    echo '<h1>Chưa kết nối được database</h1><p class="sub">Hãy tạo file <code>config.php</code> từ <code>config.example.php</code> và điền đúng thông tin database do hosting cấp, rồi tải lại trang này.</p>';
    simple_page_end();
    exit;
}

$tablesExist = (bool) $pdo->query("SHOW TABLES LIKE 'admins'")->fetchColumn();
$hasAdmin = $tablesExist && (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;

if ($hasAdmin) {
    simple_page_start('Cài đặt');
    echo '<h1>Website đã được cài đặt</h1><p class="sub">Đã có tài khoản quản trị. Trang cài đặt bị khóa để bảo vệ website.</p><div class="links"><a href="login.php">Đi tới trang đăng nhập</a></div>';
    simple_page_end();
    exit;
}

$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf();
    $setupKey = (string) ($_POST['setup_key'] ?? '');
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if (login_locked_seconds() > 0) {
        $error = 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 15 phút.';
    } elseif (!hash_equals((string) app_config()['db_pass'], $setupKey)) {
        login_record_failure();
        $error = 'Mã cài đặt không đúng (là mật khẩu database trong config.php).';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
        $error = 'Tên đăng nhập 4-50 ký tự, chỉ gồm chữ không dấu, số, dấu chấm, gạch dưới, gạch ngang.';
    } elseif ($problem = password_problem($password, $confirm, $username)) {
        $error = $problem;
    } else {
        $schema = preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/../database/schema.sql'));
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            $pdo->exec($statement);
        }
        $pdo->beginTransaction();
        // Kiểm tra lại trong transaction để tránh 2 người cài cùng lúc.
        if ((int) $pdo->query('SELECT COUNT(*) FROM admins FOR UPDATE')->fetchColumn() > 0) {
            $pdo->rollBack();
            header('Location: setup.php');
            exit;
        }
        $pdo->prepare('INSERT INTO admins (username, password) VALUES (:u, :p)')
            ->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_DEFAULT)]);
        $pdo->commit();

        login_reset_attempts();
        session_regenerate_id(true);
        $_SESSION = ['loggedin' => true, 'username' => $username, 'last_seen' => time()];
        header('Location: index.php');
        exit;
    }
}

simple_page_start('Cài đặt');
?>
    <h1>Tạo tài khoản quản trị</h1>
    <p class="sub">Bước cuối để bắt đầu dùng website. Trang này tự khóa sau khi tạo xong tài khoản đầu tiên.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <label for="setup_key">Mã cài đặt</label>
        <input type="password" id="setup_key" name="setup_key" required>
        <small class="hint">Là mật khẩu database bạn đã điền trong file config.php.</small>

        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required minlength="4" maxlength="50" autocomplete="username">

        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required minlength="10" autocomplete="new-password">
        <small class="hint">Ít nhất 10 ký tự, có cả chữ và số.</small>

        <label for="password_confirm">Nhập lại mật khẩu</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="10" autocomplete="new-password">

        <button type="submit">Tạo tài khoản &amp; bắt đầu</button>
    </form>
<?php
simple_page_end();
