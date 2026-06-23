<?php
// 1. Panggil session yang sedang berjalan
session_start();

// 2. Kosongkan semua data session
session_unset();

// 3. Hancurkan session (ibarat membuang kunci aksesnya)
session_destroy();

// 4. Arahkan kembali ke halaman login (index.php)
header("Location: index.php");
exit;
?>