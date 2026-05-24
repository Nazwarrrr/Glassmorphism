<?php
/**
 * Helper Functions untuk Business Logic
 * Termasuk perhitungan denda, manajemen stok, validasi booking, dll
 */

require_once __DIR__ . '/../config/koneksi.php';

// Konstanta sistem
define('TARIF_DENDA_PER_HARI', 1000);
define('DURASI_PEMINJAMAN_HARI', 7);
define('MAX_PEMINJAMAN_AKTIF', 3);
define('BOOKING_EXPIRY_JAM', 48);

/**
 * Hitung denda otomatis berdasarkan keterlambatan
 * @param int $id_peminjaman
 * @param datetime $tgl_kembali (optional, jika null ambil dari DB)
 * @return int Total denda
 */
function calculate_fine($id_peminjaman, $tgl_kembali = null) {
    // Jika tgl_kembali tidak disediakan, ambil dari database
    if ($tgl_kembali === null) {
        $peminjaman = fetch_one(
            "SELECT tgl_kembali, status FROM peminjaman WHERE id_peminjaman = ?",
            [$id_peminjaman]
        );
        if (!$peminjaman) {
            return 0;
        }
        $tgl_kembali = $peminjaman['tgl_kembali'];
        $status = $peminjaman['status'];
    } else {
        $status = fetch_one(
            "SELECT status FROM peminjaman WHERE id_peminjaman = ?",
            [$id_peminjaman]
        )['status'];
    }

    // Jika status bukan 'Sedang Dipinjam' atau 'Terlambat', denda = 0
    if ($status !== 'Sedang Dipinjam' && $status !== 'Terlambat') {
        return 0;
    }

    // Jika tgl_kembali NULL (belum diset), denda = 0
    if ($tgl_kembali === null) {
        return 0;
    }

    // Hitung selisih waktu
    $now = new DateTime();
    $due_date = new DateTime($tgl_kembali);
    
    // Jika belum mencapai tanggal jatuh tempo
    if ($now <= $due_date) {
        return 0;
    }

    // Hitung hari keterlambatan (pembulatan ke atas)
    $interval = $now->diff($due_date);
    $hari_terlambat = $interval->days;
    
    // Jika same day, hitung sebagai 1 hari
    if ($hari_terlambat == 0 && $now > $due_date) {
        $hari_terlambat = 1;
    }

    return $hari_terlambat * TARIF_DENDA_PER_HARI;
}

/**
 * Get total denda aktif user (dari semua peminjaman status Sedang Dipinjam/Terlambat)
 * @param int $id_user
 * @return int Total denda
 */
function get_user_total_fine($id_user) {
    $peminjaman_list = fetch_all(
        "SELECT id_peminjaman, tgl_kembali, status FROM peminjaman 
         WHERE id_user = ? AND (status = 'Sedang Dipinjam' OR status = 'Terlambat')",
        [$id_user]
    );

    $total_denda = 0;
    foreach ($peminjaman_list as $p) {
        $total_denda += calculate_fine($p['id_peminjaman'], $p['tgl_kembali']);
    }

    return $total_denda;
}

/**
 * Get jumlah peminjaman aktif user (booking + sedang dibawa)
 * @param int $id_user
 * @return int Jumlah
 */
function get_user_active_loans($id_user) {
    $result = fetch_one(
        "SELECT COUNT(*) as count FROM peminjaman 
         WHERE id_user = ? AND (status = 'Menunggu Konfirmasi' OR status = 'Sedang Dipinjam')",
        [$id_user]
    );
    return $result['count'] ?? 0;
}

/**
 * Check apakah user masih bisa meminjam (belum mencapai max 3 buku)
 * @param int $id_user
 * @return bool
 */
function can_borrow($id_user) {
    $active_loans = get_user_active_loans($id_user);
    return $active_loans < MAX_PEMINJAMAN_AKTIF;
}

/**
 * Auto-reject booking yang sudah expired (> 2 hari)
 * Dipanggil di setiap page load untuk auto-cleanup
 * @return int Jumlah booking yang di-reject
 */
function reject_expired_bookings() {
    $count = 0;
    
    try {
        // Find all pending bookings yang lebih dari 2 hari
        $expired_bookings = fetch_all(
            "SELECT id_peminjaman, id_buku FROM peminjaman 
             WHERE status = 'Menunggu Konfirmasi' 
             AND tgl_booking < DATE_SUB(NOW(), INTERVAL " . BOOKING_EXPIRY_JAM . " HOUR)"
        );

        if (empty($expired_bookings)) {
            return 0;
        }

        // Process each expired booking
        foreach ($expired_bookings as $booking) {
            begin_transaction();
            try {
                // Update status booking menjadi Ditolak
                execute_action(
                    "UPDATE peminjaman SET status = 'Ditolak' WHERE id_peminjaman = ?",
                    [$booking['id_peminjaman']]
                );

                // Restore stok buku
                execute_action(
                    "UPDATE buku SET stok = stok + 1 WHERE id_buku = ?",
                    [$booking['id_buku']]
                );

                commit_transaction();
                $count++;
            } catch (Exception $e) {
                rollback_transaction();
            }
        }

        return $count;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get stok buku with lock (FOR UPDATE) untuk mencegah race condition
 * @param int $id_buku
 * @return int|null Stok buku atau null jika tidak ditemukan
 */
function get_book_stock_locked($id_buku) {
    global $pdo;
    
    // Query dengan lock untuk mencegah race condition
    $query = "SELECT stok FROM buku WHERE id_buku = ? FOR UPDATE";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_buku]);
    $result = $stmt->fetch();
    
    return $result['stok'] ?? null;
}

/**
 * Create booking baru dengan transaction + stock lock
 * @param int $id_user
 * @param int $id_buku
 * @return array ['success' => bool, 'message' => string, 'id_peminjaman' => int|null]
 */
function create_booking($id_user, $id_buku) {
    global $pdo;

    // Check 1: User sudah mencapai max peminjaman?
    if (!can_borrow($id_user)) {
        return [
            'success' => false,
            'message' => 'Anda sudah mencapai batas maksimal peminjaman (3 buku). Silakan kembalikan buku terlebih dahulu.'
        ];
    }

    // Check 2: Apakah buku sudah pernah dibooking oleh user ini (status Menunggu)?
    $existing_booking = fetch_one(
        "SELECT id_peminjaman FROM peminjaman 
         WHERE id_user = ? AND id_buku = ? AND status = 'Menunggu Konfirmasi'",
        [$id_user, $id_buku]
    );
    if ($existing_booking) {
        return [
            'success' => false,
            'message' => 'Anda sudah memiliki booking untuk buku ini. Silakan lihat di tab Menunggu Persetujuan.'
        ];
    }

    try {
        $pdo->beginTransaction();

        // Lock baris buku untuk check stok
        $stok = get_book_stock_locked($id_buku);

        if ($stok === null) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Buku tidak ditemukan.'
            ];
        }

        if ($stok <= 0) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Maaf, stok buku habis atau sedang dipesan siswa lain.'
            ];
        }

        // Insert booking
        $stmt = $pdo->prepare(
            "INSERT INTO peminjaman (id_user, id_buku, status) 
             VALUES (?, ?, 'Menunggu Konfirmasi')"
        );
        $stmt->execute([$id_user, $id_buku]);
        $id_peminjaman = $pdo->lastInsertId();

        // Update stok (kurangi 1)
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id_buku = ?")
            ->execute([$id_buku]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Berhasil memesan buku. Silakan ambil di perpustakaan dalam waktu 2x24 jam.',
            'id_peminjaman' => $id_peminjaman
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}

/**
 * Cancel booking dengan restore stok
 * @param int $id_peminjaman
 * @param int $id_user (untuk validasi owner)
 * @return array ['success' => bool, 'message' => string]
 */
function cancel_booking($id_peminjaman, $id_user) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Check booking exists dan milik user ini
        $peminjaman = fetch_one(
            "SELECT id_buku, status FROM peminjaman WHERE id_peminjaman = ? AND id_user = ?",
            [$id_peminjaman, $id_user]
        );

        if (!$peminjaman) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Booking tidak ditemukan.'
            ];
        }

        // Check status masih Menunggu Konfirmasi
        if ($peminjaman['status'] !== 'Menunggu Konfirmasi') {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Tidak bisa membatalkan booking yang status-nya sudah berubah.'
            ];
        }

        // Update status menjadi Ditolak
        execute_action(
            "UPDATE peminjaman SET status = 'Ditolak' WHERE id_peminjaman = ?",
            [$id_peminjaman]
        );

        // Restore stok
        execute_action(
            "UPDATE buku SET stok = stok + 1 WHERE id_buku = ?",
            [$peminjaman['id_buku']]
        );

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Booking berhasil dibatalkan. Stok buku telah dipulihkan.'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}

/**
 * Update status peminjaman menjadi Terlambat jika sudah melewati tgl_kembali
 * @param int $id_peminjaman
 * @return bool
 */
function update_overdue_status($id_peminjaman) {
    $peminjaman = fetch_one(
        "SELECT tgl_kembali, status FROM peminjaman WHERE id_peminjaman = ?",
        [$id_peminjaman]
    );

    if (!$peminjaman || $peminjaman['status'] !== 'Sedang Dipinjam') {
        return false;
    }

    if ($peminjaman['tgl_kembali'] && strtotime($peminjaman['tgl_kembali']) < time()) {
        execute_action(
            "UPDATE peminjaman SET status = 'Terlambat' WHERE id_peminjaman = ?",
            [$id_peminjaman]
        );
        return true;
    }

    return false;
}
?>
