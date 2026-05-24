<?php
/**
 * Database Connection Configuration (PDO)
 * File ini menangani koneksi ke MySQL menggunakan PHP Data Objects (PDO)
 * untuk mencegah SQL Injection melalui prepared statements
 */

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan_smea');

try {
    // Inisialisasi PDO dengan DSN
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan error
    http_response_code(500);
    die("Error Koneksi Database: " . $e->getMessage());
}

/**
 * Helper function untuk execute query dengan prepared statement
 * @param string $query SQL query dengan placeholder ?
 * @param array $params Parameter untuk bind
 * @return PDOStatement
 */
function execute_query($query, $params = array()) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    if (!$stmt->execute($params)) {
        throw new Exception("Query Error: " . implode(", ", $stmt->errorInfo()));
    }
    return $stmt;
}

/**
 * Helper function untuk fetch single row
 * @param string $query SQL query
 * @param array $params Parameters
 * @return array|null
 */
function fetch_one($query, $params = array()) {
    $stmt = execute_query($query, $params);
    return $stmt->fetch();
}

/**
 * Helper function untuk fetch all rows
 * @param string $query SQL query
 * @param array $params Parameters
 * @return array
 */
function fetch_all($query, $params = array()) {
    $stmt = execute_query($query, $params);
    return $stmt->fetchAll();
}

/**
 * Helper function untuk insert/update/delete
 * @param string $query SQL query
 * @param array $params Parameters
 * @return int Rows affected
 */
function execute_action($query, $params = array()) {
    $stmt = execute_query($query, $params);
    return $stmt->rowCount();
}

/**
 * Helper function untuk get last inserted ID
 * @return int
 */
function get_last_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Helper function untuk start transaction
 */
function begin_transaction() {
    global $pdo;
    $pdo->beginTransaction();
}

/**
 * Helper function untuk commit transaction
 */
function commit_transaction() {
    global $pdo;
    $pdo->commit();
}

/**
 * Helper function untuk rollback transaction
 */
function rollback_transaction() {
    global $pdo;
    $pdo->rollBack();
}

// Ensure UTF-8 encoding
header('Content-Type: text/html; charset=utf-8');
?>
