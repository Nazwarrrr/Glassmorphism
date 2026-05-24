<?php
/**
 * API Endpoint: Add Book
 * POST /api/add_book.php
 * Params: id_kategori, judul, penulis, penerbit, tahun_terbit, stok, sinopsis, cover_buku (file)
 * Response: {success: bool, message: string, id_buku: int|null}
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
$id_kategori = isset($_POST['id_kategori']) && $_POST['id_kategori'] !== '' ? intval($_POST['id_kategori']) : null;
$judul = trim($_POST['judul'] ?? '');
$penulis = trim($_POST['penulis'] ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$tahun_terbit = isset($_POST['tahun_terbit']) && $_POST['tahun_terbit'] !== '' ? intval($_POST['tahun_terbit']) : null;
$stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
$sinopsis = trim($_POST['sinopsis'] ?? '');

// Validasi
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
    // Insert buku
    $query = "INSERT INTO buku (id_kategori, judul, penulis, penerbit, tahun_terbit, stok, sinopsis, cover_buku) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'default.jpg')";
    $stmt = $GLOBALS['pdo']->prepare($query);
    $stmt->execute([$id_kategori, $judul, $penulis, $penerbit, $tahun_terbit, $stok, $sinopsis]);
    
    $id_buku = $GLOBALS['pdo']->lastInsertId();

    // Handle image upload jika ada
    $cover_filename = 'default.jpg';
    if (isset($_FILES['cover_buku']) && $_FILES['cover_buku']['error'] === UPLOAD_ERR_OK) {
        $image_result = handle_image_upload($_FILES['cover_buku'], $id_buku);
        
        if ($image_result['success']) {
            $cover_filename = $image_result['filename'];
            // Update database dengan nama file yang benar
            execute_action(
                "UPDATE buku SET cover_buku = ? WHERE id_buku = ?",
                [$cover_filename, $id_buku]
            );
        } else {
            // Image upload gagal, tapi buku tetap dibuat dengan default.jpg
            error_log('Image upload failed for buku #' . $id_buku . ': ' . $image_result['error']);
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil ditambahkan.',
        'id_buku' => $id_buku
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
