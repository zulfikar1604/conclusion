<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// --- 1. LOGIKA UPDATE TRANSAKSI (DARI MODAL EDIT) ---
if (isset($_POST['update_transaksi'])) {
    $id = $_POST['id_transaksi'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $rincian = mysqli_real_escape_string($conn, $_POST['rincian']);
    $total = $_POST['total_bayar'];
    $status = $_POST['status_bayar'];
    $metode = $_POST['metode_bayar'];

    $query_update = "UPDATE transaksi SET 
                     nama_pelanggan='$nama', rincian='$rincian', total_bayar='$total', 
                     status_bayar='$status', metode_bayar='$metode' 
                     WHERE id_transaksi='$id'";
    
    if (mysqli_query($conn, $query_update)) {
        header("Location: daftar_nota.php?pesan=update_sukses");
        exit;
    }
}

// --- 2. LOGIKA AKSI CEPAT (LUNAS & HAPUS) ---
if (isset($_GET['aksi'])) {
    $id = $_GET['id'];
    if ($_GET['aksi'] == 'lunas') {
        mysqli_query($conn, "UPDATE transaksi SET status_bayar = 'Lunas' WHERE id_transaksi = '$id'");
        header("Location: daftar_nota.php?pesan=update_sukses");
    } 
    elseif ($_GET['aksi'] == 'hapus') {
        mysqli_query($conn, "DELETE FROM transaksi WHERE id_transaksi = '$id'");
        header("Location: daftar_nota.php?pesan=hapus_sukses");
    }
    exit;
}

// --- 3. LOGIKA FILTER TANGGAL ---
$filter_tgl = isset($_GET['tgl']) ? $_GET['tgl'] : '';
if (!empty($filter_tgl)) {
    $query_nota = mysqli_query($conn, "SELECT * FROM transaksi WHERE DATE(tgl_transaksi) = '$filter_tgl' ORDER BY tgl_transaksi DESC");
} else {
    $query_nota = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tgl_transaksi DESC LIMIT 100");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Nota | Conclusion</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a; --accent: #3b82f6; 
            --bg: #f8fafc; --card-bg: #ffffff; 
            --text-main: #1e293b; --text-muted: #64748b;
        }

        body { margin: 0; padding: 0; background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }
        .container { width: 95%; max-width: 1300px; margin: 0 auto 50px auto; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; margin-bottom: 25px; }
        .page-title { font-size: 1.8rem; color: var(--text-main); font-weight: 800; margin: 0; }

        .filter-card {
            background: white; padding: 20px; border-radius: 20px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }
        .filter-card input[type="date"] { padding: 10px; border: 2px solid #e2e8f0; border-radius: 12px; font-family: inherit; outline: none; }
        
        .card { background: var(--card-bg); padding: 30px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); }
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 1000px; }
        th { color: var(--text-muted); font-weight: 600; text-align: left; padding: 0 15px; font-size: 0.85rem; text-transform: uppercase; }
        td { background: #f8fafc; padding: 18px 15px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }
        td:first-child { border-left: 1px solid #f1f5f9; border-radius: 14px 0 0 14px; font-weight: 700; color: var(--accent); }
        td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 14px 14px 0; }

        .btn-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; text-decoration: none; transition: 0.3s; cursor: pointer; border: none; }
        .btn-edit { background: #e0f2fe; color: #0369a1; }
        .btn-lunas { background: #dcfce7; color: #16a34a; }
        .btn-red { background: #fee2e2; color: #ef4444; }

        .badge { padding: 6px 12px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; }
        .bg-lunas { background: #dcfce7; color: #16a34a; }
        .bg-belum { background: #fee2e2; color: #ef4444; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .modal.show { display: flex; }
        .modal-content { background: #fff; padding: 30px; border-radius: 28px; width: 90%; max-width: 500px; box-shadow: 0 25px 50px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; margin-top: 5px; }
        .btn-save { width: 100%; padding: 15px; border: none; border-radius: 14px; background: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%); color: white; font-weight: 700; cursor: pointer; margin-top: 15px; }

        @media (max-width: 768px) { .card { padding: 20px; } }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Daftar Nota Transaksi</h1>
        </div>

        <div class="filter-card">
            <form action="" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <label style="font-weight: 600; color: var(--text-muted);">Cari per Tanggal:</label>
                <input type="date" name="tgl" value="<?= $filter_tgl; ?>">
                <button type="submit" style="background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; cursor: pointer;">Filter</button>
                <?php if(!empty($filter_tgl)): ?>
                    <a href="daftar_nota.php" style="background: #64748b; color: white; text-decoration: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;">Show All</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Rincian Pesanan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query_nota) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($query_nota)): ?>
                            <tr>
                                <td>#INV-<?= str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                                <td><b><?= htmlspecialchars($row['nama_pelanggan']); ?></b></td>
                                <td style="font-size: 0.85rem; color: #64748b; white-space: pre-line;"><?= htmlspecialchars($row['rincian']); ?></td>
                                <td style="font-weight: 700;">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?= ($row['status_bayar'] == 'Lunas') ? 'bg-lunas' : 'bg-belum'; ?>">
                                        <?= $row['status_bayar']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button onclick='openEdit(<?= json_encode($row); ?>)' class="btn-icon btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if($row['status_bayar'] == 'Belum Lunas'): ?>
                                            <a href="daftar_nota.php?aksi=lunas&id=<?= $row['id_transaksi']; ?>" class="btn-icon btn-lunas" onclick="return confirm('Konfirmasi Lunas?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="nota.php?id=<?= $row['id_transaksi']; ?>" target="_blank" class="btn-icon" style="background: #f1f5f9; color: #475569;">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        <a href="daftar_nota.php?aksi=hapus&id=<?= $row['id_transaksi']; ?>" class="btn-icon btn-red" onclick="return confirm('Hapus transaksi ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">Tidak ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h2 style="margin-top:0; font-size:1.2rem;"><i class="fas fa-edit" style="color: var(--accent);"></i> Edit Data Nota</h2>
            
            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px dashed #cbd5e1;">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-main);"><i class="fas fa-cart-plus"></i> Tambah Item Susulan</label>
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <select id="edit_tambah_item" class="form-control" style="flex: 1; padding: 10px; margin-top: 0;">
                        <option value="" data-harga="0">-- Pilih Item --</option>
                        <optgroup label="Layanan Cetak Jasa">
                            <?php 
                            $ql_edit = mysqli_query($conn, "SELECT * FROM kategori_layanan ORDER BY nama_layanan ASC");
                            while($rl = mysqli_fetch_assoc($ql_edit)) echo "<option value='".htmlspecialchars($rl['nama_layanan'])."' data-harga='".$rl['harga_per_satuan']."'>".htmlspecialchars($rl['nama_layanan'])."</option>"; 
                            ?>
                        </optgroup>
                        <optgroup label="Produk & Stok ATK">
                            <?php 
                            $qa_edit = mysqli_query($conn, "SELECT * FROM produk_atk ORDER BY nama_barang ASC");
                            while($ra = mysqli_fetch_assoc($qa_edit)) echo "<option value='".htmlspecialchars($ra['nama_barang'])."' data-harga='".$ra['harga_jual']."'>".htmlspecialchars($ra['nama_barang'])."</option>"; 
                            ?>
                        </optgroup>
                    </select>
                    <input type="number" id="edit_tambah_qty" class="form-control" value="1" min="1" style="width: 70px; padding: 10px; margin-top: 0;">
                    <button type="button" onclick="tambahItemEdit()" style="background: var(--accent); color: white; border: none; border-radius: 10px; padding: 0 15px; cursor: pointer; transition: 0.3s;" title="Tambahkan ke Rincian">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <form action="" method="POST">
                <input type="hidden" name="id_transaksi" id="edit_id">
                
                <div style="margin-bottom:15px;">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted);">Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" id="edit_nama" class="form-control" required>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted);">Rincian Pesanan</label>
                    <textarea name="rincian" id="edit_rincian" class="form-control" rows="4" style="line-height: 1.5;" required></textarea>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted);">Total Bayar (Rp)</label>
                    <input type="number" name="total_bayar" id="edit_total" class="form-control" required style="font-weight: 800; color: var(--accent); font-size: 1.1rem; transition: background-color 0.3s;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom:10px;">
                    <div style="display: flex; flex-direction: column;">
                        <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:5px;">Status</label>
                        <select name="status_bayar" id="edit_status" class="form-control" style="margin-top: 0;">
                            <option value="Lunas">Lunas</option>
                            <option value="Belum Lunas">Belum Lunas</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:5px;">Metode</label>
                        <select name="metode_bayar" id="edit_metode" class="form-control" style="margin-top: 0;">
                            <option value="Tunai">Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="update_transaksi" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <button type="button" onclick="closeEdit()" style="width:100%; background:none; border:none; color:var(--text-muted); margin-top:10px; cursor:pointer; font-weight:600;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        // Fungsi membuka modal
        function openEdit(data) {
            document.getElementById('modalEdit').classList.add('show');
            document.getElementById('edit_id').value = data.id_transaksi;
            document.getElementById('edit_nama').value = data.nama_pelanggan;
            document.getElementById('edit_rincian').value = data.rincian;
            document.getElementById('edit_total').value = data.total_bayar;
            document.getElementById('edit_status').value = data.status_bayar;
            document.getElementById('edit_metode').value = data.metode_bayar;
        }

        // Fungsi menutup modal
        function closeEdit() { 
            document.getElementById('modalEdit').classList.remove('show'); 
        }
        
        // Menutup saat klik area luar modal
        window.onclick = function(e) { 
            if(e.target == document.getElementById('modalEdit')) closeEdit(); 
        }

        // --- FUNGSI MINI KASIR ---
        function tambahItemEdit() {
            let selectItem = document.getElementById("edit_tambah_item");
            if (selectItem.selectedIndex === 0) {
                alert("Silakan pilih item yang ingin ditambahkan!");
                return;
            }

            // Ekstrak data yang dipilih
            let namaItem = selectItem.value;
            let hargaSatuan = parseInt(selectItem.options[selectItem.selectedIndex].getAttribute("data-harga")) || 0;
            let qty = parseInt(document.getElementById("edit_tambah_qty").value) || 1;
            
            // Hitung harga tambahan
            let subtotal = hargaSatuan * qty;

            // Update Textarea (Rincian Pesanan)
            let rincianArea = document.getElementById("edit_rincian");
            let formatTeksBaru = qty + "x " + namaItem;
            
            if (rincianArea.value.trim() !== "") {
                rincianArea.value += "\n" + formatTeksBaru; 
            } else {
                rincianArea.value = formatTeksBaru;
            }

            // Update Harga Total (Total Bayar)
            let totalInput = document.getElementById("edit_total");
            let totalLama = parseInt(totalInput.value) || 0;
            totalInput.value = totalLama + subtotal;

            // Animasi hijau kecil untuk efek sukses menambah
            totalInput.style.backgroundColor = "#dcfce7";
            setTimeout(() => { totalInput.style.backgroundColor = ""; }, 500);

            // Reset input dropdown agar siap untuk barang selanjutnya
            selectItem.selectedIndex = 0;
            document.getElementById("edit_tambah_qty").value = 1;
        }
    </script>

</body>
</html>