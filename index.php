<?php
// index.php - Main application file
require 'koneksi.php';

$rows = [];
$action = $_POST['action'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'tambah') {
        $nama = $koneksi->real_escape_string(trim($_POST['nama'] ?? ''));
        $berat = floatval($_POST['berat'] ?? 0);
        $harga_per_kg = 5000;
        $total = $berat * $harga_per_kg;

        $sql = "INSERT INTO laporan_transaksi (nama, berat, total, status, tgl_input) VALUES (?, ?, ?, 'Menunggu', NOW())";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('sdi', $nama, $berat, $total);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $nama = $koneksi->real_escape_string(trim($_POST['nama'] ?? ''));
        $berat = floatval($_POST['berat'] ?? 0);
        $status = $koneksi->real_escape_string($_POST['status'] ?? 'Menunggu');
        $harga_per_kg = 5000;
        $total = $berat * $harga_per_kg;

        $sql = "UPDATE laporan_transaksi SET nama=?, berat=?, total=?, status=? WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('sdisi', $nama, $berat, $total, $status, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'hapus') {
        $id = intval($_POST['id'] ?? 0);
        $sql = "DELETE FROM laporan_transaksi WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'export') {
        shell_exec('python export_data.py');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Export berhasil']);
        exit;
    }
}

// Fetch all transactions
$sql = "SELECT * FROM laporan_transaksi ORDER BY tgl_input DESC";
$result = $koneksi->query($sql);
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaundryFlow - Sistem Manajemen Laundry</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>LaundryFlow</h1>
        <p class="subtitle">Sistem Manajemen Laundry Sederhana</p>

        <section class="main-content">
            <!-- Form Tambah Transaksi -->
            <div class="card">
                <h2>Tambah Transaksi</h2>
                <form method="post">
                    <input type="hidden" name="action" value="tambah">
                    <input name="nama" placeholder="Nama pelanggan" required>
                    <input name="berat" placeholder="Berat (kg)" type="number" step="0.01" required>
                    <button type="submit">Tambah</button>
                </form>
            </div>

            <!-- Form Cari -->
            <div class="card">
                <h2>Cari</h2>
                <input id="q" placeholder="Cari nama...">
            </div>
        </section>

        <!-- Tabel Transaksi -->
        <section class="card" id="table-area">
            <h2>Daftar Transaksi</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Berat</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id']) ?></td>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['berat']) ?> kg</td>
                        <td>Rp <?= number_format($r['total'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                        <td>
                            <button class="btn-edit" data-id="<?= $r['id'] ?>">Edit</button>
                            <form method="post" class="inline-form" onsubmit="return confirm('Hapus transaksi ini?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Export Button -->
            <form method="post" class="export-form">
                <input type="hidden" name="action" value="export">
                <button type="submit" class="btn-export">Export CSV (Python)</button>
            </form>
        </section>

        <!-- Edit Modal -->
        <div id="edit-modal" class="modal" style="display:none">
            <div class="modal-content">
                <h3>Edit Transaksi</h3>
                <form id="edit-form" method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="e-id">

                    <label>Nama</label>
                    <input name="nama" id="e-nama" required>

                    <label>Berat (kg)</label>
                    <input name="berat" id="e-berat" type="number" step="0.01" required>

                    <label>Status</label>
                    <select name="status" id="e-status">
                        <option>Menunggu</option>
                        <option>Proses</option>
                        <option>Selesai</option>
                    </select>

                    <div class="modal-actions">
                        <button type="submit" class="btn-submit">Simpan</button>
                        <button type="button" id="close-modal" class="btn-cancel">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
