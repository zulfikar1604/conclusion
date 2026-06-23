<?php
session_start();
// Pastikan file koneksi.php sudah ada dan benar
include 'koneksi.php';

// Proteksi halaman, harus login
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// --- LOGIKA PENGAMBILAN DATA STATISTIK DARI DATABASE ---

// 1. Ambil Total Pendapatan (Kotor)
$q_masuk = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status_bayar = 'Lunas'");
$d_masuk = mysqli_fetch_assoc($q_masuk);
$total_pendapatan = $d_masuk['total'] ?? 0;

// 2. Hitung Total Pengeluaran
$q_keluar = mysqli_query($conn, "SELECT SUM(nominal) as total FROM pengeluaran");
$d_keluar = mysqli_fetch_assoc($q_keluar);
$total_pengeluaran = $d_keluar['total'] ?? 0;

// 3. Hitung Laba Bersih Nyata
$laba_bersih = $total_pendapatan - $total_pengeluaran;


// 4. Ambil Total Order/Transaksi
$query_transaksi = mysqli_query($conn, "SELECT COUNT(id_transaksi) as jumlah FROM transaksi");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_order = $data_transaksi['jumlah'] ? $data_transaksi['jumlah'] : 0;

// 5. Peringatan Stok ATK Menipis
$query_stok_kritis = mysqli_query($conn, "SELECT COUNT(id_produk) as kritis FROM produk_atk WHERE stok <= 5");
$stok_kritis = 0;
if ($query_stok_kritis) {
    $data_stok_kritis = mysqli_fetch_assoc($query_stok_kritis);
    $stok_kritis = $data_stok_kritis['kritis'] ? $data_stok_kritis['kritis'] : 0;
}

// 6. Ambil 5 Transaksi Terbaru
$query_terbaru = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tgl_transaksi DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operasional | Conclusion</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            margin: 0; padding: 0; background-color: var(--bg);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%), 
                              radial-gradient(at 100% 0%, rgba(45, 212, 191, 0.05) 0px, transparent 50%);
            min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .container { width: 95%; max-width: 1200px; margin: 0 auto 50px auto; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .page-header h1 { font-size: 2.2rem; color: var(--text-main); font-weight: 800; letter-spacing: -0.5px; margin-bottom: 5px; }
        .page-header p { color: var(--text-muted); font-size: 1rem; font-weight: 500; }

        .date-badge {
            background: white; padding: 10px 20px; border-radius: 16px; font-weight: 600; color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 10px; border: 1px solid rgba(0,0,0,0.02);
        }

        /* Stats Grid - Diubah jadi minmax 220px agar 4 kartu muat sebaris di laptop */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }

        .stat-card {
            background: var(--card-bg); padding: 25px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.03);
            position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.02); transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: var(--gradient); }
        
        /* Warna Khusus Kartu */
        .stat-card.warning::before { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); }
        .stat-card.danger::before { background: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%); }
        .stat-card.success::before { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }

        .stat-card h3 { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        
        .stat-card .value { font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin-top: 10px; display: flex; align-items: baseline; gap: 5px; }
        .stat-card .value span { font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }
        
        .stat-card i { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 3rem; opacity: 0.05; color: var(--primary); }

        .content-card { background: var(--card-bg); padding: 35px; border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card-header h2 { font-size: 1.3rem; font-weight: 700; color: var(--text-main); }

        .btn-action { background: var(--gradient); color: white; padding: 12px 24px; border-radius: 14px; text-decoration: none; font-weight: 600; font-size: 0.95rem; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25); transition: all 0.3s ease; }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(59, 130, 246, 0.35); }

        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 600px; }
        th { color: var(--text-muted); font-weight: 600; text-align: left; padding: 0 20px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        td { background: #f8fafc; padding: 18px 20px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; color: var(--text-main); font-weight: 500; font-size: 0.95rem; }
        td:first-child { border-left: 1px solid #f1f5f9; border-radius: 14px 0 0 14px; font-weight: 700; color: var(--accent); }
        td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 14px 14px 0; }

        .badge { padding: 6px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-pending { background: #fef3c7; color: #d97706; }

        .empty-state { text-align: center; padding: 40px; color: var(--text-muted); font-weight: 500; background: #f8fafc; border-radius: 16px; border: 1px dashed #cbd5e1; }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .stats-grid { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-action { width: 100%; text-align: center; box-sizing: border-box; }
            .content-card { padding: 20px; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <div>
                <h1>Halo, Conclusion Tim!</h1>
                <p>Pantau arus kas dan operasional Conclusion hari ini.</p>
            </div>
            <div class="date-badge">
                <i class="far fa-calendar-alt" style="color: var(--accent);"></i>
                <?php echo date('d F Y'); ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Omzet Kotor</h3>
                <div class="value"><span>Rp</span> <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <i class="fas fa-wallet"></i>
            </div>

            <div class="stat-card danger">
                <h3>Total Pengeluaran</h3>
                <div class="value" style="color: #ef4444;"><span>Rp</span> <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                <i class="fas fa-hand-holding-usd"></i>
            </div>

            <div class="stat-card success">
                <h3>Laba Bersih</h3>
                <div class="value" style="color: #10b981;"><span>Rp</span> <?php echo number_format($laba_bersih, 0, ',', '.'); ?></div>
                <i class="fas fa-chart-line"></i>
            </div>
            
            <div class="stat-card <?php echo ($stok_kritis > 0) ? 'warning' : ''; ?>">
                <h3>Stok ATK Menipis</h3>
                <div class="value" style="<?php echo ($stok_kritis > 0) ? 'color: #ef4444;' : ''; ?>">
                    <?php echo $stok_kritis; ?> <span>Barang</span>
                </div>
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>Aktivitas Transaksi Terbaru</h2>
                <a href="kasir.php" class="btn-action"><i class="fas fa-plus"></i> Transaksi Baru</a>
            </div>
            
            <?php if (mysqli_num_rows($query_terbaru) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Nota</th>
                                <th>Tanggal</th>
                                <th>Nama Pelanggan</th>
                                <th>Total Harga</th>
                                <th>Status Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($query_terbaru)): ?>
                            <tr>
                                <td>#INV-<?php echo str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td style="font-weight: 700;">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if($row['status_bayar'] == 'Lunas'): ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Lunas</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fas fa-clock"></i> Belum Lunas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <p>Belum ada data transaksi tercatat. Mulai input transaksi baru!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>