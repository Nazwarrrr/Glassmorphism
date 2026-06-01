<?php
/**
 * Debug - Cek database dan upload issue
 */

require_once __DIR__ . '/config/koneksi.php';

try {
    echo "=== DATABASE STATUS ===\n\n";
    
    // Check total books
    $totalBooks = fetch_one("SELECT COUNT(*) as count FROM buku")['count'];
    echo "📚 Total Buku di Database: $totalBooks\n\n";
    
    if ($totalBooks > 0) {
        // List all books with cover info
        echo "📖 Daftar Buku:\n";
        $books = fetch_all("SELECT id_buku, judul, penulis, cover_buku FROM buku ORDER BY id_buku");
        foreach ($books as $book) {
            $cover_path = '/Glassmorphism/assets/img/' . $book['cover_buku'];
            echo "  ID: {$book['id_buku']} | Judul: {$book['judul']} | Cover: {$book['cover_buku']}\n";
        }
    }
    
    echo "\n=== FILE UPLOAD FOLDER STATUS ===\n\n";
    echo "📁 Files in /assets/img/:\n";
    $uploadDir = __DIR__ . '/assets/img/';
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $size = filesize($uploadDir . $file);
            echo "  ✓ $file (" . number_format($size / 1024, 2) . " KB)\n";
        }
    }
    
    echo "\n=== FOLDER PERMISSIONS ===\n";
    $perms = fileperms($uploadDir);
    $perms_octal = decoct($perms & 0777);
    echo "📋 Permissions: $perms_octal\n";
    echo "✓ Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "\n";
    
    echo "\n=== IMAGE HANDLER TEST ===\n";
    $image_upload_dir = __DIR__ . '/../assets/img/';
    echo "Upload directory defined in image_handler.php: {$image_upload_dir}\n";
    echo "✓ Directory exists: " . (is_dir($image_upload_dir) ? "YES" : "NO") . "\n";
    echo "✓ Is writable: " . (is_writable($image_upload_dir) ? "YES" : "NO") . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
