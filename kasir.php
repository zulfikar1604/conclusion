<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$pesan_sukses = false;
$id_transaksi_baru = 0;
$error_msg = "";

if (isset($_POST['simpan_transaksi'])) {
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    
    // Tangkap data keranjang yang dikirim via JSON dari JavaScript
    $cart_data = json_decode($_POST['cart_data'], true); 
    
    $potongan = (float)$_POST['potongan'];
    $biaya_lain = (float)$_POST['biaya_lain'];
    $ket_biaya_lain = mysqli_real_escape_string($conn, $_POST['ket_biaya_lain']);
    $status_bayar = $_POST['status_bayar'];
    $metode_bayar = $_POST['metode_bayar']; 
    
    if (empty($cart_data)) {
        $error_msg = "Keranjang belanja masih kosong!";
    } else {
        $subtotal_cart = 0;
        $arr_rincian = [];

        // Loop isi keranjang untuk hitung subtotal & update stok
        foreach ($cart_data as $item) {
            $nama_item = mysqli_real_escape_string($conn, $item['nama']);
            $qty = (int)$item['qty'];
            $harga = (float)$item['harga'];
            
            $subtotal_cart += ($harga * $qty);
            $arr_rincian[] = $qty . "x " . $nama_item;

            // Update stok otomatis (Jika barang ada di tabel produk_atk, stok berkurang. Jika jasa, akan diabaikan)
            mysqli_query($conn, "UPDATE produk_atk SET stok = stok - $qty WHERE nama_barang = '$nama_item' AND stok >= $qty");
        }

        // Hitung Total Akhir
        $total_bayar = $subtotal_cart - $potongan + $biaya_lain;

        // Gabungkan array rincian menjadi satu teks rapi (dipisah dengan enter/newline)
        $rincian_final = implode("\n", $arr_rincian);
        if (!empty($ket_biaya_lain)) {
            $rincian_final .= "\n+ " . $ket_biaya_lain;
        }

        // Simpan ke Database
        $query_transaksi = "INSERT INTO transaksi (nama_pelanggan, rincian, total_bayar, status_bayar, metode_bayar) 
                            VALUES ('$nama_pelanggan', '$rincian_final', '$total_bayar', '$status_bayar', '$metode_bayar')";
        
        if (mysqli_query($conn, $query_transaksi)) {
            $pesan_sukses = true;
            $id_transaksi_baru = mysqli_insert_id($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kasir Multi-Item | Conclusion</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a; --accent: #3b82f6; 
            --gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            --bg: #f8fafc; --card-bg: #ffffff; 
            --text-main: #1e293b; --text-muted: #64748b;
        }

        body { margin: 0; padding: 0; background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; }
        .container { width: 95%; max-width: 1300px; margin: 0 auto 50px auto; padding: 0 10px; box-sizing: border-box; }
        .page-title { font-size: 1.8rem; color: var(--text-main); font-weight: 800; margin-bottom: 25px; margin-top: 20px; }

        .pos-layout { display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; align-items: start; }
        .card { background: var(--card-bg); padding: 30px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.03); margin-bottom: 20px; }
        .card h2 { font-size: 1.2rem; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px; margin-top: 0; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; }
        
        .form-control { width: 100%; padding: 14px 16px 14px 45px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s; box-sizing: border-box; font-family: inherit; }
        .form-control:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
        select.form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 16px center; background-size: 18px; }
        
        .btn-add { width: 100%; padding: 14px; border: none; border-radius: 12px; background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-add:hover { background: #bae6fd; }

        /* CART TABLE STYLES */
        .cart-container { background: #f8fafc; border-radius: 16px; border: 1px dashed #cbd5e1; padding: 15px; margin-bottom: 20px; min-height: 150px; }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { text-align: left; padding: 10px 5px; font-size: 0.8rem; color: var(--text-muted); border-bottom: 2px solid #e2e8f0; }
        .cart-table td { padding: 12px 5px; font-size: 0.9rem; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .cart-table td.qty-col { font-weight: 800; color: var(--accent); }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; border-radius: 6px; width: 28px; height: 28px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; }
        .btn-remove:hover { background: #ef4444; color: white; }

        .summary-box { background: #f1f5f9; padding: 20px; border-radius: 16px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted); font-weight: 600; font-size: 0.95rem; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #cbd5e1; font-size: 1.6rem; font-weight: 800; color: var(--accent); }
        
        .btn-submit { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--gradient); color: white; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25); }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(59, 130, 246, 0.35); }

        .alert-box { background: #dcfce7; padding: 20px; border-radius: 16px; margin-bottom: 25px; border: 1px solid #bdf0d2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }

        @media (max-width: 992px) { .pos-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Mesin Kasir Multi-Item</h1>

        <?php if($pesan_sukses): ?>
            <div class="alert-box">
                <div style="color: #16a34a; font-weight: 700;"><i class="fas fa-check-circle"></i> Transaksi Berhasil Disimpan!</div>
                <a href="nota.php?id=<?= $id_transaksi_baru; ?>" target="_blank" style="background: white; color: #16a34a; padding: 8px 15px; border-radius: 10px; text-decoration: none; font-weight: 700; border: 2px solid #16a34a;">Cetak Nota Struk</a>
            </div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <div class="pos-layout">
            
            <div class="card">
                <h2><i class="fas fa-cart-plus" style="color: var(--accent);"></i> Tambah Item</h2>
                
                <div class="form-group">
                    <label>Pilih Layanan / ATK</label>
                    <div class="input-wrapper">
                        <i class="fas fa-search"></i>
                        <select id="input_layanan" class="form-control">
                            <option value="" data-harga="0" selected disabled>-- Pilih dari Database --</option>
                            <optgroup label="Layanan Cetak Jasa">
                                <?php 
                                $q_layanan = mysqli_query($conn, "SELECT * FROM kategori_layanan ORDER BY nama_layanan ASC");
                                while($row = mysqli_fetch_assoc($q_layanan)): 
                                ?>
                                    <option value="<?= htmlspecialchars($row['nama_layanan']); ?>" data-harga="<?= $row['harga_per_satuan']; ?>">
                                        <?= htmlspecialchars($row['nama_layanan']); ?> (Rp <?= number_format($row['harga_per_satuan'],0,',','.'); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </optgroup>
                            <optgroup label="Produk & Stok ATK">
                                <?php 
                                $q_atk = mysqli_query($conn, "SELECT * FROM produk_atk WHERE stok > 0 ORDER BY nama_barang ASC");
                                while($row = mysqli_fetch_assoc($q_atk)): 
                                ?>
                                    <option value="<?= htmlspecialchars($row['nama_barang']); ?>" data-harga="<?= $row['harga_jual']; ?>">
                                        <?= htmlspecialchars($row['nama_barang']); ?> (Stok: <?= $row['stok']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Jumlah (Qty)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-sort-numeric-up"></i>
                        <input type="number" id="input_jumlah" class="form-control" value="1" min="1">
                    </div>
                </div>

                <button type="button" class="btn-add" onclick="tambahKeKeranjang()">
                    <i class="fas fa-plus"></i> Masukkan ke Keranjang
                </button>
            </div>

            <div class="card">
                <h2><i class="fas fa-shopping-basket" style="color: var(--accent);"></i> Keranjang & Pembayaran</h2>
                
                <form action="" method="POST" id="formKasir" onsubmit="return validasiCheckout()">
                    <input type="hidden" name="cart_data" id="cart_data_input" value="[]">

                    <div class="form-group">
                        <label>Nama Pelanggan / Proyek</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Wajib Diisi" required>
                        </div>
                    </div>

                    <div class="cart-container">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="width: 50px;">Qty</th>
                                    <th style="text-align: right;">Subtotal</th>
                                    <th style="width: 40px; text-align: center;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="cart_body">
                                <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">Keranjang kosong</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Biaya Lain (Rp)</label>
                            <input type="number" name="biaya_lain" id="biaya_lain" class="form-control" style="padding-left: 15px;" value="0" min="0" oninput="renderKeranjang()">
                        </div>
                        <div class="form-group">
                            <label>Diskon (Rp)</label>
                            <input type="number" name="potongan" id="potongan" class="form-control" style="padding-left: 15px;" value="0" min="0" oninput="renderKeranjang()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ket. Biaya Lain (Opsional)</label>
                        <input type="text" name="ket_biaya_lain" class="form-control" style="padding-left: 15px;" placeholder="Contoh: Jilid, Laminating">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Metode</label>
                            <select name="metode_bayar" class="form-control" style="padding-left: 15px;">
                                <option value="Tunai">Tunai</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status_bayar" class="form-control" style="padding-left: 15px;">
                                <option value="Lunas">Lunas</option>
                                <option value="Belum Lunas">Belum Lunas</option>
                            </select>
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Total Item:</span>
                            <span id="txt-subtotal">Rp 0</span>
                        </div>
                        <div class="summary-row" style="color: #10b981;">
                            <span>Biaya Lain:</span>
                            <span id="txt-lain">+ Rp 0</span>
                        </div>
                        <div class="summary-row" style="color: #ef4444;">
                            <span>Diskon:</span>
                            <span id="txt-diskon">- Rp 0</span>
                        </div>
                        <div class="summary-total">
                            <span>TOTAL BAYAR</span>
                            <span id="txt-total">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" name="simpan_transaksi" class="btn-submit">
                        <i class="fas fa-check-circle"></i> Selesaikan Transaksi
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script>
        let cart = []; // Array untuk menampung item

        // Fungsi Memasukkan Barang ke Keranjang
        function tambahKeKeranjang() {
            let selectLayanan = document.getElementById("input_layanan");
            if(selectLayanan.selectedIndex === 0) {
                alert("Pilih Layanan atau ATK terlebih dahulu!");
                return;
            }

            let nama = selectLayanan.value;
            let harga = parseInt(selectLayanan.options[selectLayanan.selectedIndex].getAttribute("data-harga"));
            let qty = parseInt(document.getElementById("input_jumlah").value);

            if(qty <= 0 || isNaN(qty)) {
                alert("Jumlah barang tidak valid!");
                return;
            }

            // Cek apakah barang sudah ada di keranjang
            let existingItem = cart.find(item => item.nama === nama);
            if(existingItem) {
                existingItem.qty += qty; // Tambah qty nya saja
            } else {
                cart.push({ nama: nama, harga: harga, qty: qty }); // Masukkan item baru
            }

            // Reset input form
            selectLayanan.selectedIndex = 0;
            document.getElementById("input_jumlah").value = 1;

            renderKeranjang();
        }

        // Fungsi Menghapus Barang
        function hapusItem(index) {
            cart.splice(index, 1);
            renderKeranjang();
        }

        // Fungsi Menggambar Ulang Tabel Keranjang & Hitung Total
        function renderKeranjang() {
            let tbody = document.getElementById("cart_body");
            let subtotalCart = 0;

            if(cart.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">Keranjang kosong</td></tr>';
            } else {
                tbody.innerHTML = "";
                cart.forEach((item, index) => {
                    let sub = item.harga * item.qty;
                    subtotalCart += sub;
                    
                    tbody.innerHTML += `
                        <tr>
                            <td><b>${item.nama}</b><br><small style="color: #64748b;">@Rp ${item.harga.toLocaleString('id-ID')}</small></td>
                            <td class="qty-col">${item.qty}</td>
                            <td style="text-align: right; font-weight: 600;">Rp ${sub.toLocaleString('id-ID')}</td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-remove" onclick="hapusItem(${index})"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                    `;
                });
            }

            // Hitung Kalkulasi Akhir
            let biayaLain = parseInt(document.getElementById("biaya_lain").value) || 0;
            let diskon = parseInt(document.getElementById("potongan").value) || 0;
            let totalAkhir = subtotalCart + biayaLain - diskon;

            // Tampilkan ke UI
            document.getElementById("txt-subtotal").innerText = "Rp " + subtotalCart.toLocaleString('id-ID');
            document.getElementById("txt-lain").innerText = "+ Rp " + biayaLain.toLocaleString('id-ID');
            document.getElementById("txt-diskon").innerText = "- Rp " + diskon.toLocaleString('id-ID');
            document.getElementById("txt-total").innerText = "Rp " + totalAkhir.toLocaleString('id-ID');

            // Update Input Hidden untuk dikirim ke PHP
            document.getElementById("cart_data_input").value = JSON.stringify(cart);
        }

        // Cegah submit jika keranjang kosong
        function validasiCheckout() {
            if(cart.length === 0) {
                alert("Keranjang masih kosong! Tambahkan minimal 1 barang.");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>