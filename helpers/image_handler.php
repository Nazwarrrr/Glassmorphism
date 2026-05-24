<?php
/**
 * Image Handler
 * Menangani upload, validasi, dan konversi gambar cover buku
 */

require_once __DIR__ . '/../config/koneksi.php';

// Konstanta image
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('IMAGE_UPLOAD_DIR', __DIR__ . '/../assets/img/');

/**
 * Validate uploaded image
 * @param array $file $_FILES array
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_image($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'File tidak dipilih.'];
    }

    // Check file size
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['valid' => false, 'error' => 'Ukuran file terlalu besar (max 2 MB).'];
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ALLOWED_MIME_TYPES)) {
        return ['valid' => false, 'error' => 'Format file tidak diizinkan. Gunakan JPG atau PNG.'];
    }

    // Additional check: file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Ekstensi file tidak valid.'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Convert PNG to JPG if needed
 * @param string $source_path Path ke file source
 * @param string $dest_path Path ke file destination
 * @return bool Success
 */
function convert_png_to_jpg($source_path, $dest_path) {
    try {
        // Check if GD library is available
        if (!extension_loaded('gd')) {
            throw new Exception('GD library tidak tersedia di server.');
        }

        // Get image dimensions untuk detect format
        $image_info = getimagesize($source_path);
        if ($image_info === false) {
            throw new Exception('File bukan gambar yang valid.');
        }

        $mime_type = $image_info['mime'];

        // Load image berdasarkan format
        if ($mime_type === 'image/png') {
            $source_image = imagecreatefrompng($source_path);
        } elseif ($mime_type === 'image/jpeg') {
            $source_image = imagecreatefromjpeg($source_path);
        } else {
            throw new Exception('Format gambar tidak didukung.');
        }

        if ($source_image === false) {
            throw new Exception('Gagal membaca gambar.');
        }

        // Jika PNG, buat background putih untuk replace transparency
        if ($mime_type === 'image/png') {
            $width = imagesx($source_image);
            $height = imagesy($source_image);
            
            // Create white background
            $bg = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            
            // Copy PNG ke background
            imagecopy($bg, $source_image, 0, 0, 0, 0, $width, $height);
            imagedestroy($source_image);
            $source_image = $bg;
        }

        // Save sebagai JPG dengan quality 90
        if (!imagejpeg($source_image, $dest_path, 90)) {
            throw new Exception('Gagal menyimpan gambar JPG.');
        }

        imagedestroy($source_image);
        return true;
    } catch (Exception $e) {
        error_log('Image conversion error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Handle image upload untuk buku
 * @param array $file $_FILES array
 * @param int $id_buku ID buku untuk naming
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function handle_image_upload($file, $id_buku) {
    // Validate image
    $validation = validate_image($file);
    if (!$validation['valid']) {
        return ['success' => false, 'filename' => null, 'error' => $validation['error']];
    }

    // Ensure upload directory exists
    if (!is_dir(IMAGE_UPLOAD_DIR)) {
        mkdir(IMAGE_UPLOAD_DIR, 0755, true);
    }

    try {
        // Target filename: buku_[id].jpg
        $filename = 'buku_' . $id_buku . '.jpg';
        $dest_path = IMAGE_UPLOAD_DIR . $filename;

        // Delete old file if exists
        if (file_exists($dest_path)) {
            @unlink($dest_path);
        }

        // Get source file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Convert if PNG, atau copy directly if JPG
        if ($ext === 'png') {
            if (!convert_png_to_jpg($file['tmp_name'], $dest_path)) {
                return ['success' => false, 'filename' => null, 'error' => 'Gagal mengkonversi gambar PNG ke JPG.'];
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                return ['success' => false, 'filename' => null, 'error' => 'Gagal mengunggah gambar.'];
            }
        }

        return ['success' => true, 'filename' => $filename, 'error' => null];
    } catch (Exception $e) {
        return ['success' => false, 'filename' => null, 'error' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}

/**
 * Delete image file
 * @param string $filename Nama file
 * @return bool
 */
function delete_image($filename) {
    if (empty($filename) || $filename === 'default.jpg') {
        return true; // Don't delete default.jpg
    }

    $file_path = IMAGE_UPLOAD_DIR . $filename;
    if (file_exists($file_path)) {
        return @unlink($file_path);
    }

    return true;
}

/**
 * Create default.jpg placeholder jika belum ada
 * Fungsi ini harus dijalankan sekali saat setup awal
 */
function create_default_image() {
    $default_path = IMAGE_UPLOAD_DIR . 'default.jpg';
    
    if (file_exists($default_path)) {
        return true;
    }

    try {
        if (!extension_loaded('gd')) {
            error_log('GD library tidak tersedia untuk membuat default image');
            return false;
        }

        // Create 150x200 image (book cover size)
        $img = imagecreatetruecolor(150, 200);
        
        // Colors
        $bg_color = imagecolorallocate($img, 200, 200, 200);
        $text_color = imagecolorallocate($img, 80, 80, 80);
        
        // Fill background
        imagefill($img, 0, 0, $bg_color);
        
        // Add border
        imagerectangle($img, 0, 0, 149, 199, $text_color);
        
        // Add text "E-PERPUS"
        $font_size = 3;
        $text = "E-PERPUS";
        $text_bbox = imagettfbbox($font_size, 0, 2, $text);
        $text_width = $text_bbox[2] - $text_bbox[0];
        $text_height = $text_bbox[1] - $text_bbox[7];
        $x = (150 - $text_width) / 2;
        $y = (100 - $text_height) / 2;
        imagestring($img, $font_size, $x, $y, $text, $text_color);
        
        // Save
        if (!imagejpeg($img, $default_path, 90)) {
            imagedestroy($img);
            return false;
        }
        
        imagedestroy($img);
        return true;
    } catch (Exception $e) {
        error_log('Failed to create default image: ' . $e->getMessage());
        return false;
    }
}
?>
