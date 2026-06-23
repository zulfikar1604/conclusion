<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    // Keamanan dasar mencegah error input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cek kecocokan di database (Tanpa Hash, sesuai permintaan sebelumnya)
    $query  = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin'] = $row['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | Conclusion</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            /* Background gradien elegan */
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Mencegah layar geser-geser karena dekorasi bulat */
            position: relative;
        }

        /* Dekorasi Background Bulat */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(45, 212, 191, 0.2));
            filter: blur(20px);
            z-index: 0;
        }
        .circle-1 { width: 350px; height: 350px; top: -100px; right: -50px; animation: float 6s ease-in-out infinite; }
        .circle-2 { width: 250px; height: 250px; bottom: -50px; left: -50px; animation: float 8s ease-in-out infinite reverse; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
            100% { transform: translateY(0px); }
        }

        .login-container {
            /* Kunci Responsif: Lebar 90% dari layar HP, tapi maksimal 400px di laptop */
            width: 90%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px 30px;
            border-radius: 28px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            text-align: center;
            color: white;
            z-index: 1;
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 { font-weight: 800; margin-bottom: 5px; font-size: 1.6rem; letter-spacing: -0.5px; }
        .login-container h2 span { color: #3b82f6; }
        .login-container p { font-size: 0.9rem; margin-bottom: 35px; color: #94a3b8; font-weight: 400; }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .input-group input {
            width: 100%;
            /* Padding lebih besar agar jari mudah menyentuh di HP */
            padding: 16px 15px 16px 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            outline: none;
            border-radius: 16px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .input-group input::placeholder { color: #64748b; }
        
        /* Efek nyala saat diklik */
        .input-group input:focus { 
            background: rgba(255, 255, 255, 0.12); 
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); 
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2dd4bf 100%);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(59, 130, 246, 0.4);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* --- PENYESUAIAN KHUSUS LAYAR HP SANGAT KECIL --- */
        @media (max-width: 400px) {
            .login-container {
                padding: 30px 20px;
            }
            .login-container h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="login-container">
        <h2>CONCLUSION<span>GROUP</span></h2>
        <p>Fortune Favors The Bold</p>

        <?php if(isset($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-triangle"></i> Username atau Password Salah!
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Masukkan Username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                MASUK SEKARANG <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
            </button>
        </form>
    </div>

</body>
</html>