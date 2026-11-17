<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/simple_page.php';
require_login();
require_once __DIR__ . '/../db_connect_pdo.php';

$error = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf();
    $current = (string) ($_POST['current_password'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    $stmt = $pdo->prepare('SELECT id, password FROM admins WHERE username = :u');
    $stmt->execute([':u' => $_SESSION['username']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password'])) {
        $error = 'Mật khẩu hiện tại không đúng.';
    } elseif (hash_equals($current, $password)) {
        $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
    } elseif ($problem = password_problem($password, $confirm, $_SESSION['username'])) {
        $error = $problem;
    } else {
        $pdo->prepare('UPDATE admins SET password = :p WHERE id = :id')
            ->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
        session_regenerate_id(true);
        $ok = true;
    }
}

simple_page_start('Đổi mật khẩu');
?>
    <h1>Đổi mật khẩu</h1>
    <p class="sub">Tài khoản: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
    <?php if ($ok): ?><div class="alert alert-ok">Đã đổi mật khẩu thành công.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label for="current_password">Mật khẩu hiện tại</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">

        <label for="password">Mật khẩu mới</label>
        <input type="password" id="password" name="password" required minlength="10" autocomplete="new-password">
        <small class="hint">Ít nhất 10 ký tự, có cả chữ và số.</small>

        <label for="password_confirm">Nhập lại mật khẩu mới</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="10" autocomplete="new-password">

        <button type="submit">Lưu mật khẩu mới</button>
    </form>
    <div class="links"><a href="index.php">← Quay lại quản lý sản phẩm</a></div>
<?php
simple_page_end();
