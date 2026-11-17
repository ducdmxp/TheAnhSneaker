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
