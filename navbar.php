<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --glass-bg: rgba(255, 255, 255, 0.95);
        --primary: #0f172a;
        --accent: #3b82f6;
        --gradient: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
        --text-muted: #64748b;
    }

    .premium-nav * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

    .premium-nav {
        position: sticky; top: 15px;
        margin: 15px auto 30px auto;
        width: 98%; max-width: 1300px; /* Diperlebar agar muat banyak menu */
        background: var(--glass-bg);
        backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 10px 20px; /* Padding disesuaikan */
        border-radius: 20px;
        display: flex; justify-content: space-between; align-items: center;
        z-index: 1000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .nav-brand {
        font-weight: 800; font-size: 1.25rem; color: var(--primary);
        text-decoration: none; letter-spacing: -0.5px;
        white-space: nowrap;
    }
    .nav-brand span {
        background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* Rapatkan gap agar 7 menu bisa sejajar */
    .nav-list { display: flex; gap: 8px; list-style: none; margin: 0; padding: 0; align-items: center; }

    .nav-link {
        text-decoration: none; color: var(--text-muted);
        font-weight: 600; font-size: 0.85rem; /* Font disesuaikan sedikit */
        padding: 10px 12px; border-radius: 12px;
        transition: 0.3s; display: flex; align-items: center; gap: 6px;
        white-space: nowrap; /* INI KUNCINYA: Mencegah teks turun jadi 2 baris */
    }

    .nav-link:hover, .nav-link.active {
        color: var(--accent); background: rgba(59, 130, 246, 0.08);
    }

    .btn-exit {
        background: #fee2e2; color: #ef4444;
        padding: 10px 16px; border-radius: 12px;
        text-decoration: none; font-weight: 700; font-size: 0.85rem;
        transition: 0.3s; white-space: nowrap;
    }
    .btn-exit:hover { background: #ef4444; color: white; }

    /* Responsif untuk HP dan Tablet */
    @media (max-width: 1024px) {
        .nav-list { overflow-x: auto; padding-bottom: 5px; } /* Scroll menyamping di layar kecil */
        .nav-list::-webkit-scrollbar { height: 4px; }
        .nav-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    }
    
    @media (max-width: 768px) {
        .premium-nav { flex-direction: column; gap: 15px; border-radius: 15px; padding: 15px; }
        .nav-list { width: 100%; justify-content: flex-start; }
        .btn-exit { width: 100%; text-align: center; }
    }
</style>

<?php $active = basename($_SERVER['PHP_SELF']); ?>

<nav class="premium-nav">
    <a href="dashboard.php" class="nav-brand">
        <i class="fas fa-print" style="color: var(--accent);"></i> Conclusion<span>Group</span>
    </a>

    <ul class="nav-list">
       <li>
            <a href="dashboard.php" class="nav-link <?= ($active == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Beranda
            </a>
        </li>
        <li>
            <a href="kasir.php" class="nav-link <?= ($active == 'kasir.php') ? 'active' : ''; ?>">
                <i class="fas fa-cash-register"></i> Kasir & Nota
            </a>
        </li>
        <li>
            <a href="daftar_nota.php" class="nav-link <?= ($active == 'daftar_nota.php') ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i> Riwayat Nota
            </a>
        </li>
        <li>
            <a href="input.php" class="nav-link <?= ($active == 'input.php') ? 'active' : ''; ?>">
                <i class="fas fa-bolt"></i> Pemasukan Cepat
            </a>
        </li>
        <li>
            <a href="pengeluaran.php" class="nav-link <?= ($active == 'pengeluaran.php') ? 'active' : ''; ?>">
                <i class="fas fa-minus-circle"></i> Pengeluaran
            </a>
        </li>
        <li>
            <a href="stok.php" class="nav-link <?= ($active == 'stok.php') ? 'active' : ''; ?>">
                <i class="fas fa-boxes-stacked"></i> Stok ATK
            </a>
        </li>
        <li>
            <a href="rekap.php" class="nav-link <?= ($active == 'rekap.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Laporan Kas
            </a>
        </li>
    </ul>

    <a href="logout.php" class="btn-exit">Keluar</a>
</nav>