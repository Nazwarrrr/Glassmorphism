<?php
/**
 * Toggle Book Favorite Status
 * POST endpoint to add/remove a book from user's favorites
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';

header('Content-Type: application/json');

// Authentication check
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

// Role check - only siswa can use favorites
if ($_SESSION['role'] !== 'siswa') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Hanya siswa yang bisa menggunakan fitur favorit.']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate id_buku parameter
if (!isset($input['id_buku']) || empty($input['id_buku'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_buku diperlukan.']);
    exit;
}

$id_buku = intval($input['id_buku']);
$id_user = $_SESSION['id_user'];

// Verify book exists
$book = fetch_one(
    "SELECT id_buku FROM buku WHERE id_buku = ?",
    [$id_buku]
);

if (!$book) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan.']);
    exit;
}

// Check if book is already in favorites
$existing = fetch_one(
    "SELECT id_favorit FROM favorit WHERE id_user = ? AND id_buku = ?",
    [$id_user, $id_buku]
);

try {
    if ($existing) {
        // Remove from favorites
        $result = execute_action(
            "DELETE FROM favorit WHERE id_user = ? AND id_buku = ?",
            [$id_user, $id_buku]
        );
        
        if ($result) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Buku dihapus dari favorit.',
                'is_favorited' => false
            ]);
        } else {
            throw new Exception('Gagal menghapus favorit.');
        }
    } else {
        // Add to favorites
        $result = execute_action(
            "INSERT INTO favorit (id_user, id_buku, tgl_ditambahkan) VALUES (?, ?, NOW())",
            [$id_user, $id_buku]
        );
        
        if ($result) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Buku ditambahkan ke favorit.',
                'is_favorited' => true
            ]);
        } else {
            throw new Exception('Gagal menambahkan favorit.');
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
    exit;
}
