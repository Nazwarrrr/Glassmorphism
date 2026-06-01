<?php
/**
 * Test Image Upload - Debug detailed
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/helpers/image_handler.php';

// Simulate file upload dengan contoh
echo "=== IMAGE UPLOAD TEST ===\n\n";

// Check konstanta
echo "📋 Konstanta IMAGE_UPLOAD_DIR:\n";
echo "   Value: " . IMAGE_UPLOAD_DIR . "\n";
echo "   ✓ Directory exists: " . (is_dir(IMAGE_UPLOAD_DIR) ? "YES" : "NO") . "\n";
echo "   ✓ Is writable: " . (is_writable(IMAGE_UPLOAD_DIR) ? "YES" : "NO") . "\n";

// Check GD library
echo "\n📚 PHP Extensions:\n";
echo "   ✓ GD Library: " . (extension_loaded('gd') ? "YES" : "NO") . "\n";
echo "   ✓ finfo: " . (extension_loaded('finfo') ? "YES" : "NO") . "\n";

// List files
echo "\n📁 Files in " . IMAGE_UPLOAD_DIR . ":\n";
$files = scandir(IMAGE_UPLOAD_DIR);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "   ✓ $file\n";
    }
}

// Check latest book in database
echo "\n📚 Latest book in database:\n";
$latest = fetch_one("SELECT id_buku, judul, cover_buku FROM buku ORDER BY id_buku DESC LIMIT 1");
if ($latest) {
    echo "   ID: {$latest['id_buku']}\n";
    echo "   Judul: {$latest['judul']}\n";
    echo "   Cover: {$latest['cover_buku']}\n";
    
    // Check if cover file exists
    $cover_file = IMAGE_UPLOAD_DIR . $latest['cover_buku'];
    echo "   File exists: " . (file_exists($cover_file) ? "YES" : "NO") . "\n";
    if (file_exists($cover_file)) {
        echo "   File size: " . filesize($cover_file) . " bytes\n";
    }
} else {
    echo "   Tidak ada buku di database\n";
}

echo "\n=== END TEST ===\n";
?>
