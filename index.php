<?php
/**
 * Index/Router Utama E-Perpus SMEA
 * Menangani routing dan redirect berdasarkan session
 */

require_once __DIR__ . '/config/session_handler.php';

// Jika user sudah login, redirect ke dashboard sesuai role
if (is_logged_in()) {
    redirect_by_role();
}

// Jika belum login, redirect ke login page
header("Location: /Perpustakaan/auth/login.php");
exit();
?>
