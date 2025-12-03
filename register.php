<?php
include "koneksi.php"; // koneksi mysqli

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // validasi password konfirmasi
    if ($password !== $confirm) {
        echo "<script>alert('Password dan konfirmasi tidak sama!');</script>";
    } else {
        // cek apakah username atau email sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' OR username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username atau email sudah terdaftar');</script>";
        } else {
            // insert user baru
            $query = "INSERT INTO users (username, email, password, role) 
                      VALUES ('$username', '$email', '$password', 'user')";
            if (mysqli_query($koneksi, $query)) {
                echo "<script>
                    alert('Registrasi berhasil! Silakan login.');
                    window.location.href='index.php';
                </script>";
            } else {
                echo "<script>alert('Registrasi gagal: ".mysqli_error($koneksi)."');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - FitTask</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        <h2>Selamat Datang! Buat Akun Anda.</h2>
        <p class="sub-text">Sudah punya akun? <a href="index.php">Masuk</a></p>

        <form method="POST">
            <input type="text" name="username" placeholder="Masukkan nama pengguna Anda" required>
            <input type="email" name="email" placeholder="Masukkan email Anda" required>
            <input type="password" name="password" placeholder="Masukkan kata sandi" required>
            <input type="password" name="confirm" placeholder="Konfirmasi kata sandi" required>
            <button type="submit" name="submit">Daftar</button>
        </form>

        <p class="footer-text">
            <a href="#">Kebijakan Privasi</a> • <a href="#">Ketentuan Layanan</a>
        </p>
    </div>
</body>
</html>
