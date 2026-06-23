<?php
session_start();
include 'koneksi.php';

// Cek apakah ID nota tersedia
if (!isset($_GET['id'])) {
    echo "Data tidak ditemukan!"; exit;
}

$id = $_GET['id'];
// Mengambil data transaksi berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Transaksi tidak valid!"; exit;
}

// Logika Stempel PAID / UNPAID
$status = $data['status_bayar']; 
$stamp_text = ($status == 'Lunas') ? 'PAID' : 'UNPAID';
$stamp_color = ($status == 'Lunas') ? '#10b981' : '#ef4444';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota #INV-<?= str_pad($data['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #f1f5f9; 
            font-family: 'Space Mono', monospace; 
            color: #000; 
            padding: 20px; 
            display: flex; 
            justify-content: center; 
        }
        
        .struk-container {
            background: #fff; 
            max-width: 90vw; width: 100%; min-width: 280px; max-width: 380px;
            padding: 25px; 
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
            border-top: 6px solid <?= $stamp_color ?>;
            overflow: hidden; 
            margin: 0 auto;
        }

        /* Desain Stempel Digital */
        .stamp {
            position: absolute; 
            top: 45%; 
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: clamp(2rem, 8vw, 3.5rem); 
            font-weight: 800; 
            opacity: 0.1;
            border: 8px solid <?= $stamp_color ?>; 
            padding: 10px 20px;
            color: <?= $stamp_color ?>; 
            pointer-events: none;
            border-radius: 15px; 
            letter-spacing: 4px; 
            z-index: 10;
            text-align: center;
        }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 15px; }
        .info { font-size: clamp(0.85rem, 3vw, 1rem); margin-bottom: 20px; line-height: 1.5; }
        .info table { width: 100%; }
        
        .items { border-bottom: 1px dashed #000; padding-bottom: 15px; margin-bottom: 15px; }
        .total-area { font-size: clamp(0.95rem, 3.5vw, 1.1rem); font-weight: 700; }
        .total-row { display: flex; justify-content: space-between; margin-top: 8px; }

        .footer { text-align: center; margin-top: 30px; font-size: clamp(0.75rem, 2.5vw, 0.9rem); color: #333; }
        
        /* Tombol Cetak (Hanya tampil di layar, tidak saat di-print) */
        .no-print-area { margin-top: 25px; }
        .btn-print { 
            display: block; width: 100%; padding: clamp(12px, 4vw, 16px); background: #3b82f6; 
            color: white; text-align: center; text-decoration: none; font-weight: bold; min-height: 44px;
            border-radius: 10px; border: none; cursor: pointer; font-family: inherit; font-size: clamp(1rem, 3.5vw, 1.1rem);
        }
        .btn-close { 
            display: block; width: 100%; padding: clamp(10px, 3.5vw, 14px); background: #64748b; 
            color: white; text-align: center; text-decoration: none; font-size: clamp(0.8rem, 3vw, 1rem); min-height: 44px;
            margin-top: 10px; border-radius: 10px; border: none; cursor: pointer;
        }

        @media (max-width: 480px) {
            body { padding: 10px; font-size: 16px; }
            .struk-container { padding: 20px 18px; }
            .header h2 { font-size: clamp(1.4rem, 6vw, 1.8rem) !important; }
        }

        @media print { 
            .no-print-area { display: none !important; } 
            body { padding: 0; background: white; } 
            .struk-container { box-shadow: none; border-top: none; width: 100%; max-width: none; padding: 15px; } 
        }
    </style>
</head>
<body>

    <div class="struk-container">
        <div class="stamp"><?= $stamp_text ?></div>

        <div class="header">
            <h2 style="margin:0; letter-spacing: 2px;">CONCLUSION</h2>
            <p style="font-size:0.75rem; margin:5px 0;">Digital Printing & Stationary<br>Solusi Cetak Cepat & Berkualitas</p>
        </div>

        <div class="info">
            <table>
                <tr><td>Nota</td><td>: #INV-<?= str_pad($data['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></td></tr>
                <tr><td>Tgl</td><td>: <?= date('d/m/Y H:i', strtotime($data['tgl_transaksi'])) ?></td></tr>
                <tr>
                    <td style="vertical-align: top;">Nama</td>
                    <td>: <b><?= strtoupper(htmlspecialchars($data['nama_pelanggan'])) ?></b></td>
                </tr>
            </table>
        </div>

        <div class="items">
            <div style="font-size:0.8rem; margin-bottom:8px; font-weight:700;">RINCIAN PESANAN:</div>
            <div style="font-size:0.9rem; line-height:1.5; color: #333;">
                <?= nl2br(htmlspecialchars($data['rincian'])) ?>
            </div>
        </div>

        <div class="total-area">
            <div class="total-row">
                <span>Metode</span>
                <span><?= $data['metode_bayar'] ?></span>
            </div>
            <div class="total-row" style="font-size:1.15rem; border-top:2px solid #000; padding-top:10px; margin-top:10px;">
                <span>TOTAL</span>
                <span>Rp <?= number_format($data['total_bayar'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="footer">
            <p>Status: <span style="color:<?= $stamp_color ?>; font-weight:bold;"><?= strtoupper($status) ?></span></p>
            <p style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">Terima Kasih Atas Kepercayaan Anda!</p>
        </div>

        <div class="no-print-area">
            <button onclick="window.print()" class="btn-print">CETAK SEKARANG</button>
            <button onclick="window.close()" class="btn-close">TUTUP HALAMAN</button>
        </div>
    </div>

</body>
</html>