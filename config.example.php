<?php
/**
 * MẪU cấu hình. Cách dùng:
 *   1. Copy file này thành "config.php" (cùng thư mục).
 *   2. Điền thông tin database do hosting cấp (tạo trong cPanel > MySQL Databases).
 *      - Hosting cPanel thường: db_host là 'localhost'.
 *      - InfinityFree: db_host dạng 'sql###.infinityfree.com' (xem ở mục MySQL Databases),
 *        db_user là 'if0_...', db_pass là mật khẩu tài khoản hosting (vPanel password).
 *   3. Mở https://ten-mien/admin/setup.php để tạo bảng và tài khoản admin đầu tiên.
 *
 * KHÔNG commit config.php lên git (đã có trong .gitignore).
 */
return [
    // Tên miền chính thức, không có "/" ở cuối. Để trống = tự nhận theo địa chỉ đang truy cập.
    // Chỉ điền "https://..." SAU KHI đã bật SSL thành công.
    'site_url' => '',

    'db_host' => 'localhost',
    'db_name' => 'ten_database',
    'db_user' => 'ten_user_database',
    'db_pass' => 'mat_khau_database',

    // Facebook Conversions API (gửi sự kiện từ server, bù cho Pixel bị chặn trên iOS/trình duyệt).
    // Lấy tại: Events Manager > chọn Pixel > Cài đặt > Conversions API > Tạo mã truy cập.
    // Để trống = tắt.
    'fb_capi_token' => '',
    // Mã kiểm tra (tab "Kiểm tra sự kiện" trong Events Manager), chỉ điền khi đang test, xong thì xóa.
    'fb_test_event_code' => '',
];
