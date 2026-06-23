<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// --- 1. LOGIKA AKSI (LUNAS & HAPUS) ---
if (isset($_GET['aksi'])) {
    $id = $_GET['id'];
    if ($_GET['aksi'] == 'lunas') {
        mysqli_query($conn, "UPDATE transaksi SET status_bayar = 'Lunas' WHERE id_transaksi = '$id'");
        $pesan = "Status diperbarui menjadi Lunas!";
        $tipe_pesan = "success";
    } elseif ($_GET['aksi'] == 'hapus') {
        mysqli_query($conn, "DELETE FROM transaksi WHERE id_transaksi = '$id'");
        $pesan = "Data berhasil dihapus!";
        $tipe_pesan = "danger";
    }
}

// --- 2. LOGIKA UPDATE (DARI MODAL EDIT) ---
if (isset($_POST['update_pemasukan'])) {
    $id = $_POST['id_transaksi'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $rincian = mysqli_real_escape_string($conn, $_POST['rincian']);
    $total = $_POST['total_bayar'];
    $status = $_POST['status_bayar'];
    $metode = $_POST['metode_bayar'];

    $query_up = "UPDATE transaksi SET 
                 nama_pelanggan='$nama', rincian='$rincian', total_bayar='$total', 
                 status_bayar='$status', metode_bayar='$metode' 
                 WHERE id_transaksi='$id'";
    
    if (mysqli_query($conn, $query_up)) {
        $pesan = "Data transaksi berhasil diperbarui!";
        $tipe_pesan = "success";
    }
}

// --- 3. LOGIKA SIMPAN PEMASUKAN BARU ---
if (isset($_POST['simpan_pemasukan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $rincian = mysqli_real_escape_string($conn, $_POST['rincian']);
    $subtotal = (float)$_POST['subtotal'];
    $potongan = (float)$_POST['potongan'];
    $status = $_POST['status_bayar'];
    $metode = $_POST['metode_bayar']; 
    
    $total_bayar = $subtotal - $potongan;

    $query = "INSERT INTO transaksi (nama_pelanggan, rincian, total_bayar, status_bayar, metode_bayar) 
              VALUES ('$nama', '$rincian', '$total_bayar', '$status', '$metode')";
    
    if (mysqli_query($conn, $query)) {
        $pesan = "Pemasukan berhasil dicatat!";
        $tipe_pesan = "success";
    }
}

// Ambil riwayat terbaru (Limit 15)
$query_riwayat = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id_transaksi DESC LIMIT 15");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pemasukan Cepat | Conclusion</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%); --bg: #f8fafc; --card: #ffffff; }
        body { margin: 0; background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }
        .container { width: 95%; max-width: 1200px; margin: 0 auto 50px auto; }
        .split-layout { display: grid; grid-template-columns: 380px 1fr; gap: 30px; align-items: start; margin-top: 25px; }
        .card { background: var(--card); padding: 30px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 0.95rem; box-sizing: border-box; font-family: inherit; }
        .btn-save { width: 100%; padding: 14px; border: none; border-radius: 14px; background: var(--gradient); color: white; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; margin-right: 5px; border: none; cursor: pointer; }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 600px; }
        td { background: #f8fafc; padding: 15px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .modal.show { display: flex; }
        @media (max-width: 992px) { .split-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 style="font-weight: 800; margin-top: 30px;">Pemasukan Cepat</h1>
        <?php if($pesan != ""): ?>
            <div style="padding: 15px; background: #dcfce7; color: #16a34a; border-radius: 12px; margin-bottom: 20px; font-weight: 600;"><i class="fas fa-check-circle"></i> <?= $pesan; ?></div>
        <?php endif; ?>

        <div class="split-layout">
            <div class="card">
                <h2 style="font-size: 1.1rem; margin-top: 0;"><i class="fas fa-plus-circle" style="color: var(--accent);"></i> Input Pemasukan</h2>
                <form action="" method="POST">
                    <div style="margin-bottom:15px;"><label style="font-size:0.8rem; font-weight:700; color:#64748b;">Nama Pelanggan</label><input type="text" name="nama_pelanggan" class="form-control" placeholder="Nama" required></div>
                    <div style="margin-bottom:15px;"><label style="font-size:0.8rem; font-weight:700; color:#64748b;">Rincian Pesanan</label><input type="text" name="rincian" class="form-control" placeholder="Cth: Print A4 10 lbr" required></div>
                    <div style="margin-bottom:15px;"><label style="font-size:0.8rem; font-weight:700; color:#64748b;">Harga Kotor (Rp)</label><input type="number" name="subtotal" id="subtotal" class="form-control" placeholder="0" oninput="hitungCepat()" required></div>
                    <div style="margin-bottom:15px;"><label style="font-size:0.8rem; font-weight:700; color:#64748b;">Potongan (Rp)</label><input type="number" name="potongan" id="potongan" class="form-control" value="0" oninput="hitungCepat()"></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom:15px;">
                        <select name="metode_bayar" class="form-control"><option value="Tunai">Tunai</option><option value="QRIS">QRIS</option></select>
                        <select name="status_bayar" class="form-control"><option value="Lunas">Lunas</option><option value="Belum Lunas">Belum Lunas</option></select>
                    </div>
                    <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px dashed #cbd5e1; text-align:center; margin-bottom:20px;">
                        <span style="font-size:0.75rem; font-weight:700; color:#64748b;">NETTO</span>
                        <div id="txt-netto" style="font-size:1.4rem; font-weight:800; color:var(--accent);">Rp 0</div>
                    </div>
                    <button type="submit" name="simpan_pemasukan" class="btn-save">SIMPAN PEMASUKAN</button>
                </form>
            </div>

            <div class="card">
                <h2 style="font-size: 1.1rem; margin-top: 0;"><i class="fas fa-history" style="color: var(--accent);"></i> Riwayat Terakhir</h2>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Pelanggan</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php while($r = mysqli_fetch_assoc($query_riwayat)): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($r['nama_pelanggan']) ?></b><br><small style="color:#64748b;"><?= htmlspecialchars($r['rincian']) ?></small></td>
                                <td style="font-weight:700;">Rp <?= number_format($r['total_bayar'],0,',','.') ?></td>
                                <td><span style="color:<?= ($r['status_bayar']=='Lunas')?'#16a34a':'#ef4444' ?>; font-weight:700;"><?= $r['status_bayar'] ?></span></td>
                                <td>
                                    <?php if($r['status_bayar'] != 'Lunas'): ?><a href="input.php?aksi=lunas&id=<?= $r['id_transaksi'] ?>" class="btn-icon" style="background:#dcfce7; color:#16a34a;"><i class="fas fa-check"></i></a><?php endif; ?>
                                    <button onclick='openEdit(<?= json_encode($r) ?>)' class="btn-icon" style="background:#e0f2fe; color:#0369a1;"><i class="fas fa-edit"></i></button>
                                    <a href="input.php?aksi=hapus&id=<?= $r['id_transaksi'] ?>" class="btn-icon" style="background:#fee2e2; color:#ef4444;" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content" style="background:#fff; padding:30px; border-radius:24px; width:90%; max-width:500px;">
            <h2 style="margin-top:0;">Edit Pemasukan</h2>
            <form action="" method="POST">
                <input type="hidden" name="id_transaksi" id="edit_id">
                <div style="margin-bottom:15px;"><label>Nama Pelanggan</label><input type="text" name="nama_pelanggan" id="edit_nama" class="form-control"></div>
                <div style="margin-bottom:15px;"><label>Rincian Pesanan</label><input type="text" name="rincian" id="edit_rincian" class="form-control"></div>
                <div style="margin-bottom:15px;"><label>Total Bayar (Rp)</label><input type="number" name="total_bayar" id="edit_total" class="form-control"></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
                    <select name="metode_bayar" id="edit_metode" class="form-control"><option value="Tunai">Tunai</option><option value="QRIS">QRIS</option></select>
                    <select name="status_bayar" id="edit_status" class="form-control"><option value="Lunas">Lunas</option><option value="Belum Lunas">Belum Lunas</option></select>
                </div>
                <button type="submit" name="update_pemasukan" class="btn-save">SIMPAN PERUBAHAN</button>
                <button type="button" onclick="closeEdit()" style="width:100%; background:none; border:none; color:#94a3b8; margin-top:10px; cursor:pointer; font-weight:600;">BATAL</button>
            </form>
        </div>
    </div>

    <script>
        function hitungCepat() {
            let s = document.getElementById("subtotal").value || 0;
            let p = document.getElementById("potongan").value || 0;
            document.getElementById("txt-netto").innerText = "Rp " + (parseFloat(s) - parseFloat(p)).toLocaleString('id-ID');
        }
        function openEdit(d) {
            document.getElementById('modalEdit').classList.add('show');
            document.getElementById('edit_id').value = d.id_transaksi;
            document.getElementById('edit_nama').value = d.nama_pelanggan;
            document.getElementById('edit_rincian').value = d.rincian;
            document.getElementById('edit_total').value = d.total_bayar;
            document.getElementById('edit_metode').value = d.metode_bayar;
            document.getElementById('edit_status').value = d.status_bayar;
        }
        function closeEdit() { document.getElementById('modalEdit').classList.remove('show'); }
    </script>
</body>
</html>