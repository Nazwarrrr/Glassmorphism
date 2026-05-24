<?php
/**
 * API Endpoint: Cancel Booking
 * POST /api/cancel_booking.php
 * Request: {id_peminjaman: int}
 * Response: {success: bool, message: string}
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';

// Set JSON response header
header('Content-Type: application/json');

// Check login
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

// Check role
if ($_SESSION['role'] !== 'siswa') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses hanya untuk siswa.']);
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
$id_user = $_SESSION['id_user'];

// Cancel booking
$result = cancel_booking($id_peminjaman, $id_user);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
?>
