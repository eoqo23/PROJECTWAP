<?php
session_start();
include "koneksi.php"; // koneksi mysqli

// tampilkan error (untuk debugging, hapus di production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['login'])) {
    // amankan input
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = trim($_POST['password']);

    // ambil data user berdasarkan email
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    $user = mysqli_fetch_assoc($result);

    // cek user ada atau tidak
    if ($user) {
        // untuk saat ini, cek password langsung (belum hash)
        if (trim($user['password']) === $password) {
            // hapus session lama biar gak nyangkut
            session_unset();

            // simpan session baru
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role'])); // pastikan lowercase

            // redirect sesuai role
            if ($_SESSION['role'] === 'admin') {
                header("Location: dashboardadmin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    }

    // jika gagal login
    echo "<script>
        alert('Email atau password salah!');
        window.location.href='index.php';
    </script>";
    exit;
}
?>





<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - FitTask</title>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #0f172a;
            color: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #1e293b;
            padding: 2.5rem;
            border-radius: 1rem;
            width: 360px;
            box-shadow: 0 0 30px rgba(0,0,0,0.4);
            text-align: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 1rem;
        }

        h2 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .sub-text {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }

        .sub-text a {
            color: #3b82f6;
            text-decoration: none;
        }

        .sub-text a:hover {
            text-decoration: underline;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        input::placeholder {
            color: #64748b;
        }

        input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }

        button:hover {
            background-color: #2563eb;
        }

        button:active {
            transform: scale(0.98);
        }

        .footer-text {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #64748b;
        }

        .footer-text a {
            color: #3b82f6;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">⚙️FitTask</div>
        <h2>Selamat Datang Kembali!</h2>
        <p class="sub-text">Belum punya akun? <a href="register.php">Daftar</a></p>

        <form method="POST">
            <input type="email" name="email" placeholder="Masukkan email Anda" required>
            <input type="password" name="password" placeholder="Masukkan kata sandi" required>
            <button type="submit" name="login">Masuk</button>
        </form>

        <p class="footer-text">
            <a href="#">Kebijakan Privasi</a> • <a href="#">Ketentuan Layanan</a>
        </p>
    </div>
</body>
</html>
