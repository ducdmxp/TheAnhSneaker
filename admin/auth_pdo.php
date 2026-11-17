<?php
require_once __DIR__ . '/security.php';
admin_session_start();
require_post_with_csrf();

if (login_locked_seconds() > 0) {
    header('Location: login.php?error=locked');
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

try {
    require_once __DIR__ . '/../db_connect_pdo.php';
    $stmt = $pdo->prepare('SELECT username, password FROM admins WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[admin/auth] ' . $e->getMessage());
    header('Location: login.php?error=server');
    exit;
}

if ($user && password_verify($password, $user['password'])) {
    login_reset_attempts();
    session_regenerate_id(true);
    $_SESSION = [
        'loggedin' => true,
        'username' => $user['username'],
        'last_seen' => time(),
    ];
    header('Location: index.php');
    exit;
}

login_record_failure();
usleep(random_int(300000, 700000)); // làm chậm việc dò mật khẩu
header('Location: login.php?error=' . (login_locked_seconds() > 0 ? 'locked' : '1'));
exit;
