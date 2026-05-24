<?php
/**
 * Session Handler Configuration
 * Menangani session timeout 30 menit dan inactivity check
 */

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Durasi session timeout dalam detik (30 menit = 1800 detik)
define('SESSION_TIMEOUT', 1800);

/**
 * Function untuk check dan handle session timeout
 * Call function ini di setiap page yang memerlukan autentikasi
 */
function check_session_timeout() {
    // Jika belum ada session aktivitas, set sebagai first visit
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }

    // Hitung selisih waktu sejak aktivitas terakhir
    $elapsed = time() - $_SESSION['last_activity'];

    // Jika sudah melebihi timeout, destroy session
    if ($elapsed > SESSION_TIMEOUT) {
        session_destroy();
        header("Location: /Perpustakaan/auth/login.php?timeout=1");
        exit();
    }

    // Update waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Function untuk check apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['id_user']) && isset($_SESSION['role']);
}

/**
 * Function untuk check user role
 * @param string $required_role Peran yang dibutuhkan ('siswa' atau 'admin')
 */
function check_role($required_role) {
    if (!is_logged_in()) {
        header("Location: /Perpustakaan/auth/login.php");
        exit();
    }

    if ($_SESSION['role'] !== $required_role) {
        // Redirect ke dashboard sesuai role mereka
        if ($_SESSION['role'] === 'siswa') {
            header("Location: /Perpustakaan/siswa/dashboard.php");
        } else {
            header("Location: /Perpustakaan/admin/dashboard.php");
        }
        exit();
    }

    check_session_timeout();
}

/**
 * Function untuk redirect berdasarkan role saat login
 */
function redirect_by_role() {
    if (!is_logged_in()) {
        return false;
    }

    check_session_timeout();

    if ($_SESSION['role'] === 'siswa') {
        header("Location: /Perpustakaan/siswa/dashboard.php");
    } else {
        header("Location: /Perpustakaan/admin/dashboard.php");
    }
    exit();
}

/**
 * Function untuk logout
 */
function do_logout() {
    session_destroy();
    header("Location: /Perpustakaan/auth/login.php");
    exit();
}
?>
