<?php
/**
 * API Endpoint: Return Book
 * POST /api/return_book.php
 * Request: {id_peminjaman: int}
 * Response: {success: bool, message: string, denda: int, denda_formatted: string}
 * 
 * Proses:
 * 1. Get peminjaman data
 * 2. Hitung denda berdasarkan tgl_kembali vs NOW
 * 3. Update peminjaman: status = 'Selesai', tgl_dikembalikan = NOW, denda = calculated
 * 4. Restore stok buku (stok + 1)
 * 5. Return dengan transaction safety
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';

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

if (!isset($input['id_peminjaman'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_peminjaman diperlukan.']);
    exit;
}

$id_peminjaman = intval($input['id_peminjaman']);

try {
    global $pdo;

    // Start transaction
    $pdo->beginTransaction();

    // Get peminjaman & lock buku
    $query = "SELECT p.id_peminjaman, p.id_buku, p.tgl_kembali, p.status 
              FROM peminjaman p WHERE p.id_peminjaman = ? 
              LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_peminjaman]);
    $peminjaman = $stmt->fetch();

    if (!$peminjaman) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Peminjaman tidak ditemukan.']);
        exit;
    }

    // Check status harus Sedang Dipinjam atau Terlambat
    if ($peminjaman['status'] !== 'Sedang Dipinjam' && $peminjaman['status'] !== 'Terlambat') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status peminjaman tidak valid untuk dikembalikan.']);
        exit;
    }

    // Calculate fine
    $denda = calculate_fine($id_peminjaman, $peminjaman['tgl_kembali']);

    // Update peminjaman
    $tgl_dikembalikan = date('Y-m-d H:i:s');
    $update_query = "UPDATE peminjaman 
                     SET status = 'Selesai', tgl_dikembalikan = ?, denda = ? 
                     WHERE id_peminjaman = ?";
    $pdo->prepare($update_query)->execute([$tgl_dikembalikan, $denda, $id_peminjaman]);

    // Restore stok buku
    $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id_buku = ?")
        ->execute([$peminjaman['id_buku']]);

    // Commit transaction
    $pdo->commit();

    // Format denda
    $denda_formatted = 'Rp' . number_format($denda, 0, ',', '.');

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Pengembalian buku berhasil dicatat.',
        'denda' => $denda,
        'denda_formatted' => $denda_formatted
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
