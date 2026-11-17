-- Cấu trúc database TheAnhSneaker (MySQL / MariaDB).
-- Không cần chạy tay: trang /admin/setup.php sẽ tự tạo các bảng này.

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'other',
  price_new INT NOT NULL DEFAULT 0,
  price_old INT NULL,
  image_url VARCHAR(255) NOT NULL DEFAULT '',
  sold_count INT NOT NULL DEFAULT 0,
  tag VARCHAR(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
