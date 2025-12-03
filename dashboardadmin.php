<?php
session_start();
include 'koneksi.php';

// Cek login & role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'"));
if (!$user || strtolower($user['role']) !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// === HAPUS GYM ===
if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM gyms WHERE id = $hapusId");
    header("Location: dashboardadmin.php");
    exit;
}

// Ambil daftar gym
$gyms = mysqli_query($koneksi, "SELECT * FROM gyms ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - FitTask</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background: linear-gradient(135deg,#0b1120 0%,#1e293b 100%); color:#e2e8f0; min-height:100vh; display:flex; overflow-x:hidden; }
        .sidebar { width:160px; background:rgba(15,23,42,0.75); backdrop-filter:blur(12px); padding:4.2rem 1rem 2rem; display:flex; flex-direction:column; align-items:flex-start; position:fixed; top:0; left:0; height:100%; transition: transform 0.3s ease; box-shadow:3px 0 15px rgba(0,0,0,0.3); z-index:9; }
        .sidebar.hide { transform: translateX(-100%); }
        .sidebar a { color:#e2e8f0; text-decoration:none; display:block; width:100%; padding:10px 12px; border-radius:6px; font-size:0.88rem; margin-bottom:0.6rem; transition:all 0.25s ease; }
        .sidebar a.active { background-color: rgba(59,130,246,0.4); }
        .sidebar a:hover { background-color: rgba(59,130,246,0.3); transform: translateX(4px); }
        .navbar { position:fixed; top:0; left:0; right:0; height:42px; background: rgba(15,23,42,0.5); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; padding:0 0.8rem; z-index:10; box-shadow:0 2px 10px rgba(0,0,0,0.2); }
        .navbar h2 { color:#60a5fa; font-size:1.1rem; letter-spacing:0.5px; }
        .menu-btn { position:absolute; left:0.8rem; top:15px; width:16px; height:12px; display:flex; flex-direction:column; justify-content:space-between; background:none; border:none; cursor:pointer; padding:0; }
        .menu-btn div { width:100%; height:2px; background-color:#fff; border-radius:2px; }
        .main-content { flex:1; margin-left:160px; padding:5rem 2rem 2rem; transition: margin-left 0.3s ease; width:100%; }
        .main-content.full { margin-left:0; }
        .welcome { text-align:center; margin-bottom:1.5rem; background: rgba(30,41,59,0.4); backdrop-filter:blur(6px); padding:1.2rem; border-radius:0.8rem; }
        .welcome h1 { color:#60a5fa; font-size:1.5rem; }
        .add-btn { display:inline-block; margin:1rem 0; padding:10px 18px; background:#3b82f6; color:white; border-radius:6px; text-decoration:none; transition:0.3s; }
        .add-btn:hover { background:#2563eb; }
        .gym-container { display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:1.2rem; margin-top:1rem; }
        .gym-card { background: rgba(30,41,59,0.55); backdrop-filter:blur(8px); padding:1.3rem; border-radius:0.9rem; box-shadow:0 0 10px rgba(0,0,0,0.3); }
        .gym-card img { width:100%; height:140px; object-fit:cover; border-radius:6px; margin-bottom:0.6rem; }
        .gym-card h3 { color:#60a5fa; margin-bottom:0.5rem; }
        .gym-card p { color:#cbd5e1; font-size:0.9rem; margin:0.3rem 0; }
        .actions { display:flex; gap:0.5rem; margin-top:0.5rem; }
        .actions a { padding:6px 10px; background:#3b82f6; border-radius:6px; color:white; font-size:0.85rem; text-decoration:none; transition:0.3s; }
        .actions a:hover { background:#2563eb; }
        .search-container {
  display: flex;
  justify-content: center;
  margin: 1rem 0 1.5rem;
}

#searchForm {
  display: flex;
  align-items: center;
  background: rgba(30,41,59,0.6);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 8px rgba(0,0,0,0.2);
}

#searchInput {
  width: 280px;
  padding: 10px 12px;
  border: none;
  outline: none;
  font-size: 0.9rem;
  color: #e2e8f0;
  background: transparent;
}

#searchInput::placeholder {
  color: #94a3b8;
}

#searchBtn {
  padding: 10px 14px;
  background: #3b82f6;
  border: none;
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

#searchBtn:hover {
  background: #2563eb;
}
    </style>
</head>
<body>
    <div class="navbar">
        <button class="menu-btn" id="menuBtn"><div></div><div></div><div></div></button>
        <h2>Dashboard Admin</h2>
    </div>

    <div class="sidebar" id="sidebar">
        <a href="dashboardadmin.php" class="active">Dashboard</a>
        <a href="taskadmin.php">Tugas</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="welcome">
            <h1>Halo, <?= htmlspecialchars($user['username']); ?> 👑</h1>
            <p>Kelola katalog gym di sini.</p>
        </div>
<div class="search-container">
  <form id="searchForm" onsubmit="return false;">
    <input type="text" id="searchInput" placeholder="Cari gym atau alamat..." autocomplete="off">
    <button type="button" id="searchBtn">Cari</button>
  </form>
</div>
        <!-- Tombol Tambah Gym -->
        <a href="add_edit_gym.php" class="add-btn">+ Tambah Gym</a>

        <!-- Daftar Gym -->
        <div class="gym-container">
            <?php while ($gym = mysqli_fetch_assoc($gyms)): ?>
                <div class="gym-card">
                    <img src="<?= htmlspecialchars(!empty($gym['gambar']) ? 'uploads/' . $gym['gambar'] : 'uploads/1760546452_Modern day gym good vibes.jpg'); ?>" alt="Gym Image">
                    <h3><?= htmlspecialchars($gym['nama_gym']); ?></h3>
                    <p><?= htmlspecialchars($gym['alamat']); ?></p>
                    <p>Rp<?= number_format($gym['harga_per_bulan'], 0, ',', '.'); ?>/bulan</p>
                    <p class="rating"><?= htmlspecialchars($gym['rating']); ?>/5</p>
                    <div class="actions">
                        <a href="add_edit_gym.php?id=<?= $gym['id']; ?>">Edit</a>
                        <a href="dashboardadmin.php?hapus=<?= $gym['id']; ?>" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

   <script>
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hide');
    mainContent.classList.toggle('full');
});

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchInput");
  const searchBtn = document.getElementById("searchBtn");
  const gymContainer = document.querySelector(".gym-container");

  function searchGyms() {
    const query = searchInput.value.trim();
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "search_gyms.php?query=" + encodeURIComponent(query), true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        gymContainer.innerHTML = xhr.responseText;
      }
    };
    xhr.send();
  }

  searchBtn.addEventListener("click", searchGyms);
  searchInput.addEventListener("keyup", searchGyms);
});
</script>
</body>
</html>
