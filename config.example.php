<?php
/**
 * MẪU cấu hình. Cách dùng:
 *   1. Copy file này thành "config.php" (cùng thư mục).
 *   2. Điền thông tin database do hosting cấp (tạo trong cPanel > MySQL Databases).
 *   3. Mở https://ten-mien/admin/setup.php để tạo bảng và tài khoản admin đầu tiên.
 *
 * KHÔNG commit config.php lên git (đã có trong .gitignore).
 */
return [
    'db_host' => 'localhost',
    'db_name' => 'ten_database',
    'db_user' => 'ten_user_database',
    'db_pass' => 'mat_khau_database',
];
