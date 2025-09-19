<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập | TheAnhSneaker Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>
        :root {
            --primary-color: #ff4500; /* Màu cam nhấn của bạn */
            --primary-hover: #e03e00;
            --background-color: #ffffff;
            --panel-background: #f8f9fa;
            --text-color: #212529;
            --label-color: #495057;
            --border-color: #ced4da;
            --font-family: 'Inter', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            line-height: 1.6;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .login-image-panel {
            background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1512374382149-233c42b6a83b?q=80&w=1887&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 50px;
            color: white;
        }
        .image-panel-content h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        .image-panel-content p {
            font-size: 18px;
            max-width: 400px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .login-form-panel {
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
        }
        .form-container h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .form-container .subtitle {
            font-size: 16px;
            color: var(--label-color);
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--label-color);
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 69, 0, 0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        .error {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ae2a36;
            border: 1px solid rgba(220, 53, 69, 0.2);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* --- PHẦN MỚI THÊM VÀO --- */
        .back-to-shop {
            text-align: center;
            margin-top: 25px;
        }
        .back-to-shop a {
            color: var(--label-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s ease;
        }
        .back-to-shop a:hover {
            color: var(--primary-color);
        }
        /* --- KẾT THÚC PHẦN MỚI --- */

        @media (max-width: 992px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }
            .login-image-panel {
                display: none;
            }
            .login-form-panel {
                min-height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-image-panel">
            <div class="image-panel-content">
                <h2>TheAnhSneaker</h2>
                <p>Chào mừng bạn trở lại với trang quản trị chuyên nghiệp.</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="form-container">
                <h1>Đăng Nhập</h1>
                <p class="subtitle">Vui lòng nhập thông tin của bạn.</p>
                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="error">Tên đăng nhập hoặc mật khẩu không đúng!</div>';
                }
                ?>
                <form action="auth_pdo.php" method="post">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit">Đăng Nhập</button>
                </form>

                <div class="back-to-shop">
                    <a href="../">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại trang chủ
                    </a>
                </div>
                </div>
        </div>
    </div>
</body>
</html>