<?php
/**
 * API Endpoint: Delete Book
 * POST /api/delete_book.php
 * Request: {id_buku: int}
 * Response: {success: bool, message: string}
 * 
 * Catatan: Karena ON DELETE CASCADE, semua peminjaman terkait juga akan terhapus
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/image_handler.php';

// Set JSON response header
header('Content-Type: application/json');

// Check login & role
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses hanya untuk admin.']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_buku'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_buku diperlukan.']);
    exit;
}

$id_buku = intval($input['id_buku']);

try {
    // Get buku data untuk ambil cover image
    $buku = fetch_one(
        "SELECT cover_buku FROM buku WHERE id_buku = ?",
        [$id_buku]
    );

    if (!$buku) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan.']);
        exit;
    }

    // Delete image file jika bukan default.jpg
    if ($buku['cover_buku'] !== 'default.jpg') {
        delete_image($buku['cover_buku']);
    }

    // Delete buku (CASCADE akan delete peminjaman terkait)
    execute_action(
        "DELETE FROM buku WHERE id_buku = ?",
        [$id_buku]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil dihapus. Riwayat peminjaman juga terhapus karena cascade delete.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
