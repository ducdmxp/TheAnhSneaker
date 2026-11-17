<?php
/**
 * Tạo (và cache) ảnh WebP thu nhỏ cho ảnh sản phẩm/ảnh tĩnh để trang tải nhanh.
 * Ảnh gốc 1-3MB -> WebP ~30-120KB. Cần extension GD (có sẵn trên hầu hết hosting cPanel).
 * Nếu không tạo được (không có GD, ảnh quá lớn, thư mục không ghi được...) thì trả về ảnh gốc.
 *
 * Trả về đường dẫn tương đối với thư mục gốc website.
 */
function optimized_image(string $path, int $width = 600, int $quality = 80): string
{
    $root = dirname(__DIR__);
    $src = $root . '/' . ltrim($path, '/');

    if ($path === '' || !is_file($src) || !function_exists('imagewebp')) {
        return $path;
    }

    $info = pathinfo($path);
    $thumbRel = 'uploads/thumbs/' . preg_replace('/[^a-z0-9._-]/i', '_', $info['filename']) . '-' . $width . '.webp';
    $thumb = $root . '/' . $thumbRel;

    if (is_file($thumb) && filemtime($thumb) >= filemtime($src)) {
        return $thumbRel;
    }

    $size = @getimagesize($src);
    // Bỏ qua ảnh quá lớn để tránh hết bộ nhớ PHP (40 megapixel).
    if (!$size || $size[0] * $size[1] > 40000000) {
        return $path;
    }

    $img = @imagecreatefromstring(file_get_contents($src));
    if (!$img) {
        return $path;
    }

    [$w, $h] = [$size[0], $size[1]];
    $newW = min($width, $w);
    $newH = (int) round($h * $newW / $w);

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
    imagecopyresampled($dst, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);

    if (!is_dir(dirname($thumb))) {
        @mkdir(dirname($thumb), 0755, true);
    }
    $ok = @imagewebp($dst, $thumb, $quality);
    imagedestroy($img);
    imagedestroy($dst);

    return $ok ? $thumbRel : $path;
}

/**
 * Ảnh chia sẻ mạng xã hội 1200x630 (Facebook, Zalo, Messenger hiển thị đẹp nhất):
 * nền là chính ảnh đó được làm mờ + tối, ảnh sản phẩm đặt giữa. Cache trong uploads/thumbs/.
 * Không có GD thì trả về ảnh gốc.
 */
function social_image(string $path): string
{
    $root = dirname(__DIR__);
    $src = $root . '/' . ltrim($path, '/');
    if ($path === '' || !is_file($src) || !function_exists('imagejpeg')) {
        return $path;
    }
    $name = preg_replace('/[^a-z0-9._-]/i', '_', pathinfo($path, PATHINFO_FILENAME));
    $rel = 'uploads/thumbs/og-' . $name . '.jpg';
    $out = $root . '/' . $rel;
    if (is_file($out) && filemtime($out) >= filemtime($src)) {
        return $rel;
    }
    $size = @getimagesize($src);
    if (!$size || $size[0] * $size[1] > 40000000) {
        return $path;
    }
    $img = @imagecreatefromstring(file_get_contents($src));
    if (!$img) {
        return $path;
    }
    [$w, $h] = [$size[0], $size[1]];
    [$W, $H] = [1200, 630];
    $canvas = imagecreatetruecolor($W, $H);

    // Nền: phủ kín khung (cover), làm mờ bằng cách thu nhỏ rồi phóng to, sau đó phủ lớp tối.
    $scale = max($W / $w, $H / $h);
    $cw = (int) ceil($W / $scale);
    $ch = (int) ceil($H / $scale);
    // Thu nhỏ -> làm mờ -> phóng to qua 2 bước để nền mịn, không bị vỡ ô vuông.
    $small = imagecreatetruecolor(60, 32);
    imagecopyresampled($small, $img, 0, 0, (int) (($w - $cw) / 2), (int) (($h - $ch) / 2), 60, 32, $cw, $ch);
    for ($i = 0; $i < 4; $i++) {
        imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
    }
    $mid = imagecreatetruecolor(300, 158);
    imagecopyresampled($mid, $small, 0, 0, 0, 0, 300, 158, 60, 32);
    for ($i = 0; $i < 12; $i++) {
        imagefilter($mid, IMG_FILTER_GAUSSIAN_BLUR);
    }
    imagecopyresampled($canvas, $mid, 0, 0, 0, 0, $W, $H, 300, 158);
    imagedestroy($small);
    imagedestroy($mid);
    imagefilledrectangle($canvas, 0, 0, $W, $H, imagecolorallocatealpha($canvas, 14, 14, 16, 50));

    // Ảnh chính: vừa khung (contain) cao tối đa 570px, đặt giữa.
    $fit = min(($W - 120) / $w, 570 / $h);
    $fw = (int) round($w * $fit);
    $fh = (int) round($h * $fit);
    imagecopyresampled($canvas, $img, (int) (($W - $fw) / 2), (int) (($H - $fh) / 2), 0, 0, $fw, $fh, $w, $h);

    if (!is_dir(dirname($out))) {
        @mkdir(dirname($out), 0755, true);
    }
    $ok = @imagejpeg($canvas, $out, 85);
    imagedestroy($img);
    imagedestroy($canvas);
    return $ok ? $rel : $path;
}
