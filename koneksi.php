<?php
// koneksi.php - Database connection
// Configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'laundryflow_db';

// Create connection
$koneksi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($koneksi->connect_errno) {
    http_response_code(500);
    die('Koneksi gagal: ' . $koneksi->connect_error);
}

// Set charset to UTF-8
$koneksi->set_charset('utf8mb4');
?>
