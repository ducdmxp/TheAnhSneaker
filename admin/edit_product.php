<?php
// Bắt đầu session và kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../db_connect_pdo.php';

// Khởi tạo biến
$product = [
    'id' => '', 'name' => '', 'category' => 'nike', 'price_new' => '',
    'price_old' => '', 'image_url' => '', 'sold_count' => 0, 'tag' => ''
];
$pageTitle = 'Thêm Sản Phẩm Mới';
$form_action = 'save_product.php';

// Lấy dữ liệu nếu là trang sửa
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    
    if ($product) {
        $pageTitle = 'Chỉnh Sửa Sản Phẩm';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Bảng điều khiển</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* --- COPY TOÀN BỘ CSS TỪ DASHBOARD INDEX.PHP --- */
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-text-hover: #ffffff;
            --sidebar-active-bg: #334155;
            --main-bg: #f1f5f9;
            --content-bg: #ffffff;
            --primary-color: #3b82f6;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --font-family: 'Inter', sans-serif;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --border-radius: 0.75rem;
            --border-color: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-family);
            background-color: var(--main-bg);
            color: var(--text-dark);
            display: flex;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            padding: 20px;
            transition: transform 0.3s ease-in-out;
            z-index: 100;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .sidebar-header h2 {
            color: #fff;
            font-size: 24px;
        }
        .sidebar-nav ul { list-style: none; }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--sidebar-text);
            font-weight: 500;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-nav li a:hover {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-text-hover);
        }
        .sidebar-nav li a.active {
            background-color: var(--primary-color);
            color: #fff;
        }
        .sidebar-nav i { width: 20px; text-align: center; }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease-in-out;
        }
        
        /* --- HEADER --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-logout {
            background-color: var(--danger-color);
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }
        .btn-logout:hover { background-color: #b91c1c; }

        /* --- CSS DÀNH RIÊNG CHO FORM (MỚI) --- */
        .form-container {
            background-color: var(--content-bg);
            padding: 30px 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .form-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .form-header h1 {
            font-size: 22px;
            margin: 0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Chia form thành 2 cột */
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 10px;
        }
        
        /* Trường input chiếm toàn bộ chiều rộng khi cần */
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
            font-family: var(--font-family);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="file"] { padding: 8px; }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .current-image {
            margin-top: 10px;
            font-size: 14px;
        }
        .current-image img {
            max-width: 100px;
            height: auto;
            border-radius: 6px;
            margin-top: 5px;
        }
        
        .form-actions {
            grid-column: 1 / -1; /* Nút bấm luôn chiếm full chiều rộng */
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-secondary {
            background-color: #fff;
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }


        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 992px) {
            body { display: block; }
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            body.sidebar-open .sidebar {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
        }
        @media (max-width: 768px) {
            /* Chuyển form thành 1 cột trên màn hình nhỏ */
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 576px) {
            body { padding: 15px; }
            .main-content { padding: 15px; }
            .form-container { padding: 20px; }
            .user-info span { display: none; }
        }
    </style>
</head>
<body class="">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>TheAnhSneaker</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Quản lý người dùng</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Cài đặt</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button>
            <div class="user-info">
                <span>Chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
            </div>
        </header>

        <div class="form-container">
            <div class="form-header">
                 <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
            
            <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Tên sản phẩm</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Danh mục</label>
                        <select id="category" name="category">
                            <option value="nike" <?= ($product['category'] == 'nike') ? 'selected' : '' ?>>Nike</option>
                            <option value="adidas" <?= ($product['category'] == 'adidas') ? 'selected' : '' ?>>Adidas</option>
                            <option value="new-balance" <?= ($product['category'] == 'new-balance') ? 'selected' : '' ?>>New Balance</option>
                            <option value="other" <?= ($product['category'] == 'other') ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price_new">Giá mới (VND)</label>
                        <input type="number" id="price_new" name="price_new" value="<?= htmlspecialchars($product['price_new']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price_old">Giá cũ (VND)</label>
                        <input type="number" id="price_old" name="price_old" value="<?= htmlspecialchars($product['price_old']) ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="product_image">Ảnh sản phẩm</label>
                        <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/gif">
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_url']) ?>">
                        
                        <?php if (!empty($product['image_url'])): ?>
                            <div class="current-image">
                                <span>Ảnh hiện tại:</span>
                                <img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="Ảnh sản phẩm">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="sold_count">Số lượng đã bán</label>
                        <input type="number" id="sold_count" name="sold_count" value="<?= htmlspecialchars($product['sold_count']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tag">Tag (ví dụ: Hot, New)</label>
                        <input type="text" id="tag" name="tag" value="<?= htmlspecialchars($product['tag']) ?>">
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">Lưu Sản Phẩm</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const body = document.body;

        menuToggle.addEventListener('click', () => {
            body.classList.toggle('sidebar-open');
        });
    </script>
</body>
</html>