<?php
/** Khung giao diện gọn cho các trang form nhỏ của admin (cài đặt, đổi mật khẩu). */
function simple_page_start(string $title): void
{
    ?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($title) ?> | TheAnhSneaker Admin</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
               font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f1f5f9; color: #1e293b; }
        .card { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; padding: 32px;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        .brand { font-weight: 900; font-size: 1.3rem; margin-bottom: 4px; }
        .brand span { color: #ff4500; }
        h1 { font-size: 1.4rem; margin: 0 0 6px; }
        p.sub { color: #64748b; margin: 0 0 22px; font-size: 0.95rem; line-height: 1.5; }
        label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
        input { width: 100%; padding: 11px 13px; border: 1px solid #cbd5e1; border-radius: 10px; font: inherit; margin-bottom: 16px; }
        input:focus { outline: 2px solid #ff4500; border-color: transparent; }
        small.hint { display: block; color: #64748b; margin: -10px 0 16px; font-size: 0.82rem; }
        button { width: 100%; padding: 12px; border: 0; border-radius: 10px; background: #ff4500; color: #fff;
                 font: inherit; font-weight: 700; cursor: pointer; }
        button:hover { background: #e03e00; }
        .alert { padding: 11px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 0.92rem; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-ok { background: #dcfce7; color: #166534; }
        .links { margin-top: 18px; text-align: center; font-size: 0.9rem; }
        .links a { color: #ff4500; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">TheAnh<span>Sneaker</span></div>
<?php
}

function simple_page_end(): void
{
    echo "</div>\n</body>\n</html>\n";
}

/** Kiểm tra độ mạnh mật khẩu admin; trả về thông báo lỗi hoặc '' nếu hợp lệ. */
function password_problem(string $password, string $confirm, string $username = ''): string
{
    if (mb_strlen($password) < 10) return 'Mật khẩu phải có ít nhất 10 ký tự.';
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) return 'Mật khẩu phải có cả chữ và số.';
    if ($username !== '' && stripos($password, $username) !== false) return 'Mật khẩu không được chứa tên đăng nhập.';
    if (!hash_equals($password, $confirm)) return 'Hai lần nhập mật khẩu không khớp.';
    return '';
}
