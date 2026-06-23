<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// --- 1. LOGIKA SIMPAN BARANG (SUDAH DIPERBAIKI) ---
if (isset($_POST['simpan_barang'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok        = $_POST['stok'];
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    $harga_jual  = $_POST['harga_jual'];

    // LOGIKA ANTI ERROR: Cek apakah input pakai Kalkulator Pack atau Input Langsung
    if (!empty($_POST['harga_pack']) && !empty($_POST['isi_pack']) && $_POST['isi_pack'] > 0) {
        // Jika pakai kalkulator pack
        $harga_beli = (float)$_POST['harga_pack'] / (float)$_POST['isi_pack'];
    } else {
        // Jika input satuan langsung (untuk yang beli pcs-an)
        $harga_beli = (float)$_POST['harga_beli_langsung'];
    }

    $query_insert = "INSERT INTO produk_atk (nama_barang, harga_beli, harga_jual, stok, satuan) 
                     VALUES ('$nama_barang', '$harga_beli', '$harga_jual', '$stok', '$satuan')";

    if (mysqli_query($conn, $query_insert)) {
        $pesan = "Barang berhasil disimpan!";
        $tipe_pesan = "success";
    }
}

// --- 2. LOGIKA UPDATE/EDIT ---
if (isset($_POST['update_barang'])) {
    $id_produk = $_POST['id_produk'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $beli = $_POST['harga_beli'];
    $jual = $_POST['harga_jual'];
    $stok = $_POST['stok'];
    $sat  = $_POST['satuan'];

    $query_update = "UPDATE produk_atk SET 
                     nama_barang='$nama', harga_beli='$beli', harga_jual='$jual', 
                     stok='$stok', satuan='$sat' WHERE id_produk='$id_produk'";
    
    if (mysqli_query($conn, $query_update)) {
        $pesan = "Data barang diperbarui!";
        $tipe_pesan = "success";
    }
}

// --- 3. LOGIKA HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM produk_atk WHERE id_produk = '$id'");
    header("Location: stok.php?pesan=hapus_sukses");
    exit;
}

$query_stok = mysqli_query($conn, "SELECT * FROM produk_atk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventaris ATK | Conclusion</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --bg: #f8fafc; --card-bg: #ffffff; }
        body { margin: 0; padding: 0; background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        .container { width: 95%; max-width: 1200px; margin: 20px auto; padding: 0 10px; box-sizing: border-box; }
        .card { background: var(--card-bg); padding: 30px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); margin-bottom: 20px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 16px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        td { background: #f8fafc; padding: 18px 15px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; vertical-align: middle; }
        th { background: #f1f5f9; padding: 20px 15px; font-weight: 700; color: var(--primary); text-align: left; position: sticky; top: 0; z-index: 10; }
        .btn-add { background: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%); color: white; padding: 14px 28px; border-radius: 14px; border: none; cursor: pointer; font-weight: 600; transition: 0.3s; font-size: 1rem; min-height: 48px; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3); }
        .btn-add:active { transform: translateY(0); }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); align-items: center; justify-content: center; opacity: 0; transition: 0.3s; padding: 20px; box-sizing: border-box; }
        .modal.show { display: flex; opacity: 1; }
        .modal-content { background: #fff; padding: 32px; border-radius: 28px; width: 100%; max-width: 550px; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 25px 50px rgba(0,0,0,0.15); }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 600; font-size: 0.9rem; color: #475569; margin-bottom: 8px; display: block; }
        .form-control { width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; font-size: 1rem; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .divider { border-top: 1px dashed #cbd5e1; margin: 25px 0; position: relative; }
        .divider span { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 16px; font-size: 0.8rem; color: #94a3b8; font-weight: 700; }

        /* MOBILE RESPONSIVE ENHANCEMENTS */
        @media (max-width: 480px) {
            .container { width: 100%; margin: 10px auto; padding: 0 15px; }
            .card { padding: 24px 20px; border-radius: 20px; margin-bottom: 16px; }
            table { min-width: 750px; }
            td, th { padding: 14px 12px; font-size: 0.9rem; }
            th { font-size: 0.85rem; padding: 16px 12px; }
            .btn-add { padding: 16px 24px; font-size: 1.05rem; min-height: 52px; width: 100%; max-width: 280px; }
            .modal-content { padding: 24px 20px; margin: 10px; max-height: 95vh; }
            .form-control { padding: 16px; font-size: 1.05rem; }
            .form-group label { font-size: 0.95rem; }
            body { padding-bottom: 20px; }
        }

        @media (max-width: 768px) and (min-width: 481px) {
            .container { width: 98%; padding: 0 12px; }
            .card { padding: 28px 24px; }
            td, th { padding: 16px 14px; }
            .btn-add { min-height: 50px; padding: 15px 26px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div style="display: flex; justify-content: space-between; margin-bottom: 25px; align-items: center;">
            <h1 style="font-weight: 800; color: var(--primary);">Inventaris ATK</h1>
            <button onclick="openModal('tambah')" class="btn-add"><i class="fas fa-plus"></i> Tambah Barang</button>
        </div>

        <?php if($pesan != ""): ?>
            <div style="padding: 15px; background: #dcfce7; color: #16a34a; border-radius: 12px; margin-bottom: 20px; font-weight: 600;"><i class="fas fa-check-circle"></i> <?= $pesan; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Stok</th>
                            <th>HPP Satuan</th>
                            <th>Harga Jual</th>
                            <th>Total Margin Laba</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($query_stok)): 
                            $total_laba = ($row['harga_jual'] * $row['stok']) - ($row['harga_beli'] * $row['stok']);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><span style="font-weight: 700;"><?= $row['stok'] ?></span> <?= $row['satuan'] ?></td>
                            <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                            <td style="font-weight: 800; color: <?= ($total_laba >= 0) ? '#10b981' : '#ef4444'; ?>;">
                                <?= ($total_laba >= 0 ? '+ ' : '- ') ?>Rp <?= number_format(abs($total_laba), 0, ',', '.'); ?>
                            </td>
                            <td>
                                <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($row)); ?>)" style="color: #3b82f6; background: none; border: none; cursor: pointer; font-size: 1.1rem;"><i class="fas fa-edit"></i></button>
                                <a href="stok.php?aksi=hapus&id=<?= $row['id_produk']; ?>" onclick="return confirm('Hapus barang?')" style="color: #ef4444; margin-left: 10px; font-size: 1.1rem;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalBarang" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-top: 0;">Tambah Barang</h2>
            <form action="" method="POST">
                <input type="hidden" name="id_produk" id="form_id">
                
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" id="form_nama" class="form-control" placeholder="Contoh: Joyko Pen" required>
                </div>

                <div id="area_tambah">
                    <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; margin-bottom: 15px;">
                        <p style="margin: 0 0 10px 0; font-size: 0.8rem; font-weight: 700; color: var(--accent);">OPSI 1: Beli Per Pack (Kalkulator)</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group" style="margin: 0;">
                                <label>Harga 1 Pack</label>
                                <input type="number" name="harga_pack" class="form-control" placeholder="Misal: 21500">
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label>Isi Per Pack</label>
                                <input type="number" name="isi_pack" class="form-control" placeholder="Misal: 12">
                            </div>
                        </div>
                    </div>

                    <div class="divider"><span>ATAU</span></div>

                    <div class="form-group">
                        <label>OPSI 2: Harga Modal Satuan (Langsung)</label>
                        <input type="number" name="harga_beli_langsung" class="form-control" placeholder="Isi jika beli satuan/pcs">
                    </div>
                </div>

                <div id="area_edit" style="display: none;">
                    <div class="form-group">
                        <label>HPP Satuan (Harga Modal per Pcs)</label>
                        <input type="number" name="harga_beli" id="form_beli" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Harga Jual /Pcs</label>
                        <input type="number" name="harga_jual" id="form_jual" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Stok Sekarang</label>
                        <input type="number" name="stok" id="form_stok" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Satuan</label>
                    <select name="satuan" id="form_satuan" class="form-control">
                        <option value="Pcs">Pcs</option>
                        <option value="Buku">Buku</option>
                        <option value="Rim">Rim</option>
                    </select>
                </div>

                <button type="submit" name="simpan_barang" id="btnSubmit" class="btn-add" style="width: 100%; margin-top: 10px;">Simpan Barang</button>
                <button type="button" onclick="closeModal()" style="width: 100%; background: none; border: none; color: #94a3b8; cursor: pointer; margin-top: 10px; font-weight: 600;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode, data = null) {
            const modal = document.getElementById('modalBarang');
            modal.classList.add('show');
            if (mode === 'edit') {
                document.getElementById('modalTitle').innerText = "Edit Barang";
                document.getElementById('btnSubmit').name = "update_barang";
                document.getElementById('area_tambah').style.display = "none";
                document.getElementById('area_edit').style.display = "block";
                document.getElementById('form_id').value = data.id_produk;
                document.getElementById('form_nama').value = data.nama_barang;
                document.getElementById('form_beli').value = data.harga_beli;
                document.getElementById('form_jual').value = data.harga_jual;
                document.getElementById('form_stok').value = data.stok;
                document.getElementById('form_satuan').value = data.satuan;
            } else {
                document.getElementById('modalTitle').innerText = "Tambah Barang";
                document.getElementById('btnSubmit').name = "simpan_barang";
                document.getElementById('area_tambah').style.display = "block";
                document.getElementById('area_edit').style.display = "none";
                // Reset form tambah
                document.getElementById('form_id').value = "";
                document.getElementById('form_nama').value = "";
                document.getElementById('form_jual').value = "";
                document.getElementById('form_stok').value = "";
            }
        }
        function closeModal() { document.getElementById('modalBarang').classList.remove('show'); }
    </script>
</body>
</html>