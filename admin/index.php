<?php
// Bắt đầu session và kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Kết nối CSDL bằng PDO
require_once '../db_connect_pdo.php';

// Lấy danh sách sản phẩm
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// Lấy các số liệu thống kê
$total_products = count($products);
$category_query = $pdo->query("SELECT COUNT(DISTINCT category) as count FROM products");
$total_categories = $category_query->fetchColumn();
$total_sold_query = $pdo->query("SELECT SUM(sold_count) as total FROM products");
$total_sold = $total_sold_query->fetchColumn() ?: 0;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển - Quản Lý Sản Phẩm</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
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
            display: none; /* Chỉ hiện trên mobile */
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

        /* --- STATS CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: var(--content-bg);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-card .icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .stat-card .icon.products { background-color: #dbeafe; color: #3b82f6; }
        .stat-card .icon.categories { background-color: #dcfce7; color: #16a34a; }
        .stat-card .icon.sold { background-color: #ffedd5; color: #f97316; }

        .stat-card .info h3 {
            font-size: 24px;
            font-weight: 700;
        }
        .stat-card .info p {
            color: var(--text-light);
            font-weight: 500;
        }

        /* --- TABLE CONTAINER --- */
        .table-container {
            background-color: var(--content-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .table-header h2 { font-size: 22px; margin: 0; }
        .btn-add {
            background-color: var(--primary-color);
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }
        .btn-add:hover { background-color: #2563eb; }
        
        .table-wrapper { overflow-x: auto; } /* Cho phép cuộn ngang trên mobile */

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; vertical-align: middle; }
        thead { border-bottom: 1px solid var(--border-color); }
        thead th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-light);
        }
        tbody tr { border-bottom: 1px solid var(--border-color); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background-color: #f8fafc; }
        
        .action-links a {
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            color: #fff;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s ease;
        }
        .action-links a:hover { opacity: 0.85; }
        .edit-link { background-color: var(--warning-color); color: #fff; }
        .delete-link { background-color: var(--danger-color); }

        img.product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
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
        @media (max-width: 576px) {
            body { padding: 15px; }
            .main-content { padding: 15px; }
            .header-admin { padding-bottom: 15px; margin-bottom: 15px; }
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
                <li><a href="#" class="active"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
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

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon products"><i class="fas fa-box"></i></div>
                <div class="info">
                    <h3><?= $total_products ?></h3>
                    <p>Tổng sản phẩm</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon categories"><i class="fas fa-tags"></i></div>
                <div class="info">
                    <h3><?= $total_categories ?></h3>
                    <p>Danh mục</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon sold"><i class="fas fa-fire"></i></div>
                <div class="info">
                    <h3><?= number_format($total_sold) ?></h3>
                    <p>Lượt bán</p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Danh sách sản phẩm</h2>
                <a href="edit_product.php" class="btn-add"><i class="fas fa-plus"></i> Thêm Sản Phẩm</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Giá Mới</th>
                            <th>Danh Mục</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong><?= $product['id'] ?></strong></td>
                                <td><img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumb"></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><strong><?= number_format($product['price_new']) ?> VND</strong></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td class="action-links">
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="edit-link"><i class="fas fa-pencil-alt"></i> Sửa</a>
                                    <a href="delete_product.php?id=<?= $product['id'] ?>" class="delete-link" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"><i class="fas fa-trash-alt"></i> Xóa</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">Chưa có sản phẩm nào. Hãy thêm sản phẩm mới!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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