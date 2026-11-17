<?php
/**
 * Lưu ảnh upload "sạch":
 * - Xoay ảnh đúng chiều theo EXIF Orientation (ảnh chụp điện thoại)
 * - Ghi lại ảnh mới bằng GD => loại bỏ toàn bộ EXIF/GPS/tên máy và mọi dữ liệu lạ nhúng trong file
 * - Thu nhỏ ảnh quá lớn về tối đa 2000px để tiết kiệm dung lượng
 * Nếu hosting không có GD (hiếm) thì lưu ảnh gốc như cũ.
 */
const UPLOAD_MAX_DIMENSION = 2000;

/** Đọc giá trị EXIF Orientation (1-8) từ file JPEG mà không cần extension exif. */
function jpeg_exif_orientation(string $file): int
{
    $data = @file_get_contents($file, false, null, 0, 131072);
    if ($data === false || substr($data, 0, 2) !== "\xFF\xD8") {
        return 1;
    }
    $pos = 2;
    $len = strlen($data);
    while ($pos + 4 <= $len && $data[$pos] === "\xFF") {
        $marker = ord($data[$pos + 1]);
        $size = unpack('n', substr($data, $pos + 2, 2))[1];
        if ($marker === 0xE1 && substr($data, $pos + 4, 6) === "Exif\0\0") {
            $tiff = substr($data, $pos + 10, $size - 8);
            $le = substr($tiff, 0, 2) === 'II';
            $u16 = fn ($o) => unpack($le ? 'v' : 'n', substr($tiff, $o, 2))[1] ?? 0;
            $u32 = fn ($o) => unpack($le ? 'V' : 'N', substr($tiff, $o, 4))[1] ?? 0;
            $ifd = $u32(4);
            if ($ifd + 2 > strlen($tiff)) {
                return 1;
            }
            $count = $u16($ifd);
            for ($i = 0; $i < $count; $i++) {
                $entry = $ifd + 2 + $i * 12;
                if ($entry + 12 > strlen($tiff)) {
                    break;
                }
                if ($u16($entry) === 0x0112) {
                    $value = $u16($entry + 8);
                    return ($value >= 1 && $value <= 8) ? $value : 1;
                }
            }
            return 1;
        }
        if ($marker === 0xDA) {
            break; // bắt đầu dữ liệu ảnh, không còn header
        }
        $pos += 2 + $size;
    }
    return 1;
}

/** Áp dụng EXIF Orientation lên ảnh GD. */
function apply_orientation($img, int $orientation)
{
    switch ($orientation) {
        case 2: imageflip($img, IMG_FLIP_HORIZONTAL); break;
        case 3: $img = imagerotate($img, 180, 0); break;
        case 4: imageflip($img, IMG_FLIP_VERTICAL); break;
        case 5: $img = imagerotate($img, -90, 0); imageflip($img, IMG_FLIP_HORIZONTAL); break;
        case 6: $img = imagerotate($img, -90, 0); break;
        case 7: $img = imagerotate($img, 90, 0); imageflip($img, IMG_FLIP_HORIZONTAL); break;
        case 8: $img = imagerotate($img, 90, 0); break;
    }
    return $img;
}

/**
 * Lưu file upload $tmpPath (đã kiểm tra là ảnh, $type = IMAGETYPE_*) vào $target.
 * Trả về true nếu thành công.
 */
function store_clean_image(string $tmpPath, int $type, string $target): bool
{
    if (!is_uploaded_file($tmpPath)) {
        return false;
    }

    $canReencode = function_exists('imagecreatefromstring')
        && in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true);
    $size = @getimagesize($tmpPath);

    // GIF (có thể là ảnh động) hoặc không có GD hoặc ảnh quá lớn: lưu nguyên bản.
    if (!$canReencode || !$size || $size[0] * $size[1] > 40000000) {
        if (!function_exists('imagecreatefromstring')) {
            error_log('[upload] Hosting chưa bật PHP GD: ảnh được lưu nguyên bản (còn EXIF). Hãy bật extension gd.');
        } elseif ($canReencode) {
            error_log('[upload] Ảnh quá lớn để xử lý, lưu nguyên bản (còn EXIF): ' . basename($target));
        }
        return move_uploaded_file($tmpPath, $target);
    }

    $img = @imagecreatefromstring(file_get_contents($tmpPath));
    if (!$img) {
        return false;
    }
    if ($type === IMAGETYPE_JPEG) {
        $img = apply_orientation($img, jpeg_exif_orientation($tmpPath));
    }

    $w = imagesx($img);
    $h = imagesy($img);
    $scale = min(1, UPLOAD_MAX_DIMENSION / max($w, $h));
    if ($scale < 1) {
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $resized = imagecreatetruecolor($nw, $nh);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 255, 255, 255, 127));
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $resized;
    }

    switch ($type) {
        case IMAGETYPE_JPEG:
            imageinterlace($img, true); // JPEG progressive tải mượt hơn
            $ok = imagejpeg($img, $target, 88);
            break;
        case IMAGETYPE_PNG:
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $ok = imagepng($img, $target, 6);
            break;
        default:
            $ok = imagewebp($img, $target, 85);
    }
    imagedestroy($img);
    @unlink($tmpPath);
    return (bool) $ok;
}
