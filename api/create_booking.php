<?php
/**
 * API Endpoint: Create Booking
 * POST /api/create_booking.php
 * Request: {id_buku: int}
 * Response: {success: bool, message: string, id_peminjaman: int|null}
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

if (!isset($input['id_buku'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_buku diperlukan.']);
    exit;
}

$id_buku = intval($input['id_buku']);
$id_user = $_SESSION['id_user'];

// Auto-reject expired bookings first
reject_expired_bookings();

// Create booking
$result = create_booking($id_user, $id_buku);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
?>
