<?php
/**
 * Remove Book from Favorites (by id_favorit)
 * POST endpoint to remove a favorite by id_favorit (used on profile page)
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';

header('Content-Type: application/json');

// Authentication check
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

// Role check - only siswa can remove favorites
if ($_SESSION['role'] !== 'siswa') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Hanya siswa yang bisa mengelola favorit.']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate id_favorit parameter
if (!isset($input['id_favorit']) || empty($input['id_favorit'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_favorit diperlukan.']);
    exit;
}

$id_favorit = intval($input['id_favorit']);
$id_user = $_SESSION['id_user'];

// Verify that the favorite belongs to the current user
$favorite = fetch_one(
    "SELECT id_favorit FROM favorit WHERE id_favorit = ? AND id_user = ?",
    [$id_favorit, $id_user]
);

if (!$favorite) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Favorit tidak ditemukan.']);
    exit;
}

try {
    // Remove the favorite
    $result = execute_action(
        "DELETE FROM favorit WHERE id_favorit = ?",
        [$id_favorit]
    );
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Buku dihapus dari favorit.'
        ]);
    } else {
        throw new Exception('Gagal menghapus favorit.');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
    exit;
}
