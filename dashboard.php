<?php
session_start();
include 'koneksi.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['user_id'];
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'");
if (!$result) die("Query gagal: " . mysqli_error($koneksi));

$user = mysqli_fetch_assoc($result);

// Redirect sesuai role
if ($user) {
    if ($user['role'] === 'admin') {
        header("Location: dashboardadmin.php");
        exit;
    } elseif ($user['role'] !== 'user') {
        echo "<script>alert('Role tidak dikenali!'); window.location.href='login.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('User tidak ditemukan!'); window.location.href='login.php';</script>";
    exit;
}

// === LOGIKA SEARCH ===
$search = "";
$query = "SELECT * FROM gyms";
if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $query = "SELECT * FROM gyms WHERE nama LIKE '%$search%' OR alamat LIKE '%$search%'";
}

$gyms = mysqli_query($koneksi, $query);
if (!$gyms) die("Query gyms gagal: " . mysqli_error($koneksi));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - FitTask</title>
    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0b1120 0%, #1e293b 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 160px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            padding: 4.2rem 1rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: fixed;
            top: 0; left: 0;
            height: 100%;
            transition: transform 0.3s ease;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.3);
            z-index: 9;
        }

        .sidebar.hide { transform: translateX(-100%); }

        .sidebar a {
            color: #e2e8f0;
            text-decoration: none;
            display: block;
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
            transition: all 0.25s ease;
        }

        .sidebar a.active { background-color: rgba(59, 130, 246, 0.4); }
        .sidebar a:hover {
            background-color: rgba(59, 130, 246, 0.3);
            transform: translateX(4px);
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 42px;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .navbar h2 {
            color: #60a5fa;
            font-size: 1.1rem;
        }

        .menu-btn {
            position: absolute;
            left: 0.8rem;
            top: 15px;
            width: 16px;
            height: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: none;
            border: none;
            cursor: pointer;
        }

        .menu-btn div {
            width: 100%;
            height: 2px;
            background-color: #fff;
            border-radius: 2px;
        }

        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 160px;
            padding: 5rem 2rem 2rem;
            transition: margin-left 0.3s ease;
            width: 100%;
        }

        .main-content.full { margin-left: 0; }

        .welcome {
            text-align: center;
            margin-bottom: 1.5rem;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(6px);
            padding: 1.2rem;
            border-radius: 0.8rem;
        }

        .welcome h1 {
            color: #60a5fa;
            font-size: 1.5rem;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .search-bar input {
            width: 60%;
            max-width: 400px;
            padding: 8px 14px;
            border-radius: 6px 0 0 6px;
            border: none;
            outline: none;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .search-bar button {
            background-color: #3b82f6;
            border: none;
            color: white;
            padding: 8px 14px;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            transition: 0.3s;
        }

        .search-bar button:hover { background-color: #2563eb; }

        /* Gym cards */
        .gym-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.2rem;
        }

        .gym-card {
            background: rgba(30, 41, 59, 0.55);
            backdrop-filter: blur(8px);
            padding: 1.3rem;
            border-radius: 0.9rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .gym-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
        }

        .gym-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .gym-card h3 {
            color: #60a5fa;
            margin-bottom: 0.5rem;
        }

        .gym-card p {
            color: #cbd5e1;
            font-size: 0.9rem;
            margin: 0.3rem 0;
        }

        .rating {
            color: #facc15;
        }

        button.detail-btn {
            padding: 8px 16px;
            background-color: #3b82f6;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 0.8rem;
            width: 100%;
        }

        button.detail-btn:hover { background-color: #2563eb; }

        footer {
            text-align: center;
            padding: 0.8rem;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <button class="menu-btn" id="menuBtn">
            <div></div><div></div><div></div>
        </button>
        <h2>FitTask</h2>
    </div>

    <div class="sidebar" id="sidebar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="tracker.php">Tracker</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="welcome">
            <h1>Selamat Datang, <?= htmlspecialchars($user['username']); ?> 💪</h1>
            <p>Pilih tempat gym terbaik untuk memulai latihanmu hari ini.</p>
        </div>


        <div class="gym-container">
            <?php if (mysqli_num_rows($gyms) > 0): ?>
                <?php while ($gym = mysqli_fetch_assoc($gyms)) : ?>
                    <?php
                        $gambar = !empty($gym['gambar']) && file_exists("uploads/" . basename($gym['gambar']))
                            ? "uploads/" . basename($gym['gambar'])
                            : "1760546452_Modern day gym good vibes.jpg";
                    ?>
                    <div class="gym-card">
                        <img src="<?= htmlspecialchars($gambar); ?>" alt="Gym Image">
                        <h3><?= htmlspecialchars($gym['nama_gym'] ?? 'Nama tidak tersedia'); ?></h3>
                        <p><?= htmlspecialchars($gym['alamat'] ?? 'Alamat tidak tersedia'); ?></p>
                        <p>Rp<?= number_format($gym['harga_per_bulan'] ?? 0, 0, ',', '.'); ?>/bulan</p>
                        <p class="rating">⭐ <?= htmlspecialchars($gym['rating'] ?? 0); ?>/5</p>
                        <button class="detail-btn">Detail</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#94a3b8;">Tidak ada gym yang cocok dengan pencarianmu.</p>
            <?php endif; ?>
        </div>

        <footer>
            <p>© 2025 FitTask</p>
        </footer>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hide');
            mainContent.classList.toggle('full');
        });
        
    </script>
</body>
</html>
