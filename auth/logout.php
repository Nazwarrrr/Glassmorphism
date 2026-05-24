<?php
/**
 * Logout Handler
 * Menghancurkan session dan redirect ke login
 */

require_once __DIR__ . '/../config/session_handler.php';

// Destroy session dan redirect
do_logout();
?>
