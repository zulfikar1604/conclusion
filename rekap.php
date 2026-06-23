<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// --- LOGIKA UPDATE TRANSAKSI (EDIT) ---
if (isset($_POST['update_transaksi'])) {
    $id = $_POST['id_transaksi'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $total = $_POST['total_bayar'];
    $status = $_POST['status_bayar'];
    $metode = $_POST['metode_bayar'];

    $query_update = "UPDATE transaksi SET 
                     nama_pelanggan='$nama', total_bayar='$total', 
                     status_bayar='$status', metode_bayar='$metode' 
                     WHERE id_transaksi='$id'";
    
    mysqli_query($conn, $query_update);
}

// --- LOGIKA PERHITUNGAN KEUANGAN (Hanya yang LUNAS) ---

// 1. Hitung Total Pemasukan Nyata (Hanya Status Lunas)
$q_masuk = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status_bayar = 'Lunas'");
$d_masuk = mysqli_fetch_assoc($q_masuk);
$total_pemasukan = $d_masuk['total'] ?? 0;

// 2. Hitung Total Pengeluaran
$q_keluar = mysqli_query($conn, "SELECT SUM(nominal) as total FROM pengeluaran");
$d_keluar = mysqli_fetch_assoc($q_keluar);
$total_pengeluaran = $d_keluar['total'] ?? 0;

// 3. Hitung Laba Bersih Nyata
$laba_bersih = $total_pemasukan - $total_pengeluaran;

// Ambil riwayat transaksi (Tampilkan semua untuk diedit)
$riwayat_transaksi = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tgl_transaksi DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Arsip Laporan | Print Master</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a; --accent: #3b82f6;
            --gradient-win: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --bg: #f8fafc; --card-bg: #ffffff;
            --text-main: #1e293b; --text-muted: #64748b;
        }

        body { margin: 0; padding: 0; background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }
        .container { width: 95%; max-width: 1200px; margin: 0 auto 50px auto; }
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 1.8rem; color: var(--text-main); font-weight: 800; }

        .rekap-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .rekap-card { background: var(--card-bg); padding: 25px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); }
        .rekap-card h3 { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; }
        .rekap-card .amount { font-size: 1.8rem; font-weight: 800; color: var(--text-main); }
        .laba-card { background: var(--gradient-win); color: white; }
        .laba-card h3, .laba-card .amount { color: white; }

        .table-card { background: var(--card-bg); padding: 30px; border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 900px; }
        th { color: var(--text-muted); font-weight: 600; text-align: left; padding: 0 20px; font-size: 0.85rem; }
        td { background: #fcfcfd; padding: 18px 20px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        td:first-child { border-left: 1px solid #f1f5f9; border-radius: 14px 0 0 14px; font-weight: 700; color: var(--accent); }
        td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 14px 14px 0; }

        .btn-edit { background: #f1f5f9; color: var(--accent); border: none; padding: 8px 12px; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .btn-edit:hover { background: var(--accent); color: white; }

        /* Modal Edit */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .modal.show { display: flex; }
        .modal-content { background: #fff; padding: 30px; border-radius: 28px; width: 90%; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; }
        .btn-update { width: 100%; padding: 15px; border: none; border-radius: 14px; background: var(--gradient-win); color: white; font-weight: 700; cursor: pointer; }

        @media (max-width: 768px) { .rekap-grid { grid-template-columns: 1fr; } .table-card { padding: 20px; } }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Arsip Laporan & Keuangan</h1>
            <p style="color: var(--text-muted);">Uang "Belum Lunas" otomatis tidak dihitung dalam saldo laba.</p>
        </div>

        <div class="rekap-grid">
            <div class="rekap-card">
                <h3>Kas Masuk Nyata (Lunas)</h3>
                <div class="amount">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></div>
            </div>
            <div class="rekap-card">
                <h3>Total Pengeluaran</h3>
                <div class="amount" style="color: #ef4444;">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></div>
            </div>
            <div class="rekap-card laba-card">
                <h3>Laba Bersih Saat Ini</h3>
                <div class="amount">Rp <?= number_format($laba_bersih, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="table-card">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px;">Riwayat Transaksi</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($riwayat_transaksi)): ?>
                        <tr>
                            <td>#INV-<?= str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?= date('d M, H:i', strtotime($row['tgl_transaksi'])); ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                            <td style="font-weight: 700;">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td>
                                <span style="color: <?= ($row['status_bayar'] == 'Lunas') ? '#16a34a' : '#ef4444'; ?>; font-weight: 700;">
                                    <?= $row['status_bayar']; ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($row)); ?>)" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h2 style="margin-top: 0; font-size: 1.2rem;">Edit Data Transaksi</h2>
            <form action="" method="POST">
                <input type="hidden" name="id_transaksi" id="edit_id">
                
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.8rem;">Nama Pelanggan / Detail</label>
                    <input type="text" name="nama_pelanggan" id="edit_nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.8rem;">Total Nominal (Rp)</label>
                    <input type="number" name="total_bayar" id="edit_total" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; font-size: 0.8rem;">Status</label>
                        <select name="status_bayar" id="edit_status" class="form-control">
                            <option value="Lunas">Lunas</option>
                            <option value="Belum Lunas">Belum Lunas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; font-size: 0.8rem;">Metode</label>
                        <select name="metode_bayar" id="edit_metode" class="form-control">
                            <option value="Tunai">Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="update_transaksi" class="btn-update">Simpan Perubahan</button>
                <button type="button" onclick="closeModal()" style="width: 100%; background: none; border: none; color: #94a3b8; cursor: pointer; margin-top: 10px;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(data) {
            document.getElementById('modalEdit').classList.add('show');
            document.getElementById('edit_id').value = data.id_transaksi;
            document.getElementById('edit_nama').value = data.nama_pelanggan;
            document.getElementById('edit_total').value = data.total_bayar;
            document.getElementById('edit_status').value = data.status_bayar;
            document.getElementById('edit_metode').value = data.metode_bayar;
        }

        function closeModal() { document.getElementById('modalEdit').classList.remove('show'); }
    </script>

</body>
</html>