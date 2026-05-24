<?php
/**
 * API Endpoint: Edit Book
 * POST /api/edit_book.php
 * Params: id_buku, id_kategori, judul, penulis, penerbit, tahun_terbit, stok, sinopsis, cover_buku (file optional)
 * Response: {success: bool, message: string}
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

// Get form data
$id_buku = isset($_POST['id_buku']) ? intval($_POST['id_buku']) : 0;
$id_kategori = isset($_POST['id_kategori']) && $_POST['id_kategori'] !== '' ? intval($_POST['id_kategori']) : null;
$judul = trim($_POST['judul'] ?? '');
$penulis = trim($_POST['penulis'] ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$tahun_terbit = isset($_POST['tahun_terbit']) && $_POST['tahun_terbit'] !== '' ? intval($_POST['tahun_terbit']) : null;
$stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
$sinopsis = trim($_POST['sinopsis'] ?? '');

// Validasi
if ($id_buku <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID buku tidak valid.']);
    exit;
}

if (empty($judul) || empty($penulis)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Judul dan penulis harus diisi.']);
    exit;
}

if ($stok < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Stok tidak boleh negatif.']);
    exit;
}

try {
    // Get existing book
    $existing_book = fetch_one(
        "SELECT cover_buku FROM buku WHERE id_buku = ?",
        [$id_buku]
    );

    if (!$existing_book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan.']);
        exit;
    }

    $cover_filename = $existing_book['cover_buku'];

    // Handle image upload jika ada
    if (isset($_FILES['cover_buku']) && $_FILES['cover_buku']['error'] === UPLOAD_ERR_OK) {
        $image_result = handle_image_upload($_FILES['cover_buku'], $id_buku);
        
        if ($image_result['success']) {
            // Delete old file jika bukan default.jpg
            if ($existing_book['cover_buku'] !== 'default.jpg') {
                delete_image($existing_book['cover_buku']);
            }
            $cover_filename = $image_result['filename'];
        } else {
            // Image upload gagal, keep existing cover
            error_log('Image upload failed for buku #' . $id_buku . ': ' . $image_result['error']);
        }
    }

    // Update buku
    $query = "UPDATE buku SET id_kategori = ?, judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, stok = ?, sinopsis = ?, cover_buku = ? 
              WHERE id_buku = ?";
    
    execute_action(
        $query,
        [$id_kategori, $judul, $penulis, $penerbit, $tahun_terbit, $stok, $sinopsis, $cover_filename, $id_buku]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil diperbarui.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
