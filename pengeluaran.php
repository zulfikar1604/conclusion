<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// 1. LOGIKA SIMPAN PENGELUARAN
if (isset($_POST['simpan_pengeluaran'])) {
    $tgl_pengeluaran = $_POST['tgl_pengeluaran'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $nominal = $_POST['nominal'];

    $query_insert = "INSERT INTO pengeluaran (tgl_pengeluaran, keterangan, nominal) 
                     VALUES ('$tgl_pengeluaran', '$keterangan', '$nominal')";

    if (mysqli_query($conn, $query_insert)) {
        $pesan = "Pengeluaran berhasil dicatat!";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal mencatat pengeluaran: " . mysqli_error($conn);
        $tipe_pesan = "danger";
    }
}

// 2. AMBIL RIWAYAT PENGELUARAN TERBARU
$query_riwayat = mysqli_query($conn, "SELECT * FROM pengeluaran ORDER BY tgl_pengeluaran DESC, id_pengeluaran DESC LIMIT 15");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengeluaran Kas | Print Master</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a; 
            --accent: #f43f5e; /* Warna merah/oranye khusus pengeluaran */
            --gradient: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);
            --bg: #f8fafc; --card-bg: #ffffff; 
            --text-main: #1e293b; --text-muted: #64748b;
        }

        body {
            margin: 0; padding: 0; background-color: var(--bg);
            background-image: radial-gradient(at 0% 0%, rgba(244, 63, 94, 0.05) 0px, transparent 50%);
            font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh;
        }

        .container { width: 95%; max-width: 1200px; margin: 0 auto 50px auto; }
        .page-title { font-size: 1.8rem; color: var(--text-main); font-weight: 800; margin-bottom: 25px; }

        /* GRID LAYOUT: Kiri Form, Kanan Riwayat */
        .split-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 30px;
            align-items: start;
        }

        .card { background: var(--card-bg); padding: 30px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); }
        .card h2 { font-size: 1.2rem; margin-bottom: 25px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }

        /* Form Styles */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .form-control { width: 100%; padding: 12px 15px 12px 42px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; color: var(--text-main); transition: 0.3s; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif;}
        .form-control:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1); }
        
        .btn-save { width: 100%; padding: 14px; border: none; border-radius: 14px; background: var(--gradient); color: white; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; margin-top: 10px; box-shadow: 0 10px 20px rgba(244, 63, 94, 0.25); }
        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(244, 63, 94, 0.35); }

        /* Alert Styles */
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; } /* Merah tapi positif karena berhasil input */

        /* Table Styles */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 550px; }
        th { color: var(--text-muted); font-weight: 600; text-align: left; padding: 0 15px; font-size: 0.8rem; text-transform: uppercase; }
        td { background: #f8fafc; padding: 15px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; font-weight: 500; }
        td:first-child { border-left: 1px solid #f1f5f9; border-radius: 12px 0 0 12px; font-weight: 700; color: var(--text-muted); }
        td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 12px 12px 0; }
        
        .loss-text { color: #ef4444; font-weight: 800; }

        @media (max-width: 992px) {
            .split-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Catat Pengeluaran Operasional</h1>

        <?php if($pesan != ""): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $pesan; ?>
            </div>
        <?php endif; ?>

        <div class="split-layout">
            
            <div class="card">
                <h2><i class="fas fa-wallet" style="color: var(--accent);"></i> Input Biaya Baru</h2>
                <form action="" method="POST">
                    
                    <div class="form-group">
                        <label>Tanggal Pengeluaran</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" name="tgl_pengeluaran" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan / Nama Barang</label>
                        <div class="input-wrapper">
                            <i class="fas fa-edit"></i>
                            <input type="text" name="keterangan" class="form-control" placeholder="Cth: Beli Tinta, Map, atau Listrik" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-money-bill-wave"></i>
                            <input type="number" name="nominal" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <button type="submit" name="simpan_pengeluaran" class="btn-save">
                        <i class="fas fa-minus-circle"></i> Catat Pengeluaran
                    </button>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-history" style="color: var(--accent);"></i> Riwayat Pengeluaran Terakhir</h2>
                
                <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan Biaya</th>
                                    <th>Nominal Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($query_riwayat)): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['tgl_pengeluaran'])); ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                    <td class="loss-text">-Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #94a3b8;">
                        <i class="fas fa-receipt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 10px;"></i>
                        <p>Belum ada catatan pengeluaran.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>