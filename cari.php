<?php
// cari.php - Search API endpoint
require 'koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// Get search query
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$q = $koneksi->real_escape_string($q);

// Prepare statement
$sql = "SELECT id, nama, berat, total, status, tgl_input FROM laporan_transaksi WHERE nama LIKE ? ORDER BY tgl_input DESC LIMIT 50";
$stmt = $koneksi->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $koneksi->error]);
    exit;
}

// Bind parameter
$like = "%{$q}%";
$stmt->bind_param('s', $like);
$stmt->execute();

// Get results
$res = $stmt->get_result();
$out = [];

while ($r = $res->fetch_assoc()) {
    $out[] = $r;
}

$stmt->close();

// Return JSON response
echo json_encode($out);
?>
