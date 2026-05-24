<?php
/**
 * API Endpoint: Approve Booking
 * POST /api/approve_booking.php
 * Request: {id_peminjaman: int}
 * Response: {success: bool, message: string}
 * 
 * Proses:
 * 1. Update peminjaman: status = 'Sedang Dipinjam', tgl_pinjam = NOW, tgl_kembali = NOW + 7 hari
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';

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
    // Get peminjaman data
    $peminjaman = fetch_one(
        "SELECT id_peminjaman, status FROM peminjaman WHERE id_peminjaman = ?",
        [$id_peminjaman]
    );

    if (!$peminjaman) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
        exit;
    }

    // Check status is still Menunggu Konfirmasi
    if ($peminjaman['status'] !== 'Menunggu Konfirmasi') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status booking sudah berubah. Tidak bisa disetujui.']);
        exit;
    }

    // Update peminjaman
    $tgl_pinjam = date('Y-m-d H:i:s');
    $tgl_kembali = date('Y-m-d H:i:s', strtotime('+7 days'));

    execute_action(
        "UPDATE peminjaman SET status = 'Sedang Dipinjam', tgl_pinjam = ?, tgl_kembali = ? WHERE id_peminjaman = ?",
        [$tgl_pinjam, $tgl_kembali, $id_peminjaman]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Booking berhasil disetujui. Peminjaman dimulai, jatuh tempo 7 hari ke depan.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
