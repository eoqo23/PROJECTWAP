<?php
session_start();
include 'koneksi.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Ambil data user/admin
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$admin_id'");
$admin = mysqli_fetch_assoc($result);
if ($admin['role'] !== 'admin') {
    die("Akses ditolak. Hanya admin.");
}

// === Tambah task baru ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $user_id = (int)$_POST['user_id'];
    $pekerjaan = mysqli_real_escape_string($koneksi, $_POST['pekerjaan']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);

    $query = "INSERT INTO task_admin (admin_id, user_id, pekerjaan, deskripsi, deadline) 
              VALUES ('$admin_id','$user_id','$pekerjaan','$deskripsi','$deadline')";

    if (!mysqli_query($koneksi, $query)) {
        die("Insert gagal: " . mysqli_error($koneksi));
    }
    header("Location: taskadmin.php");
    exit;
}

// Ambil semua task yang dibuat admin ini
$tasks = mysqli_query($koneksi, "SELECT t.*, u.username AS user_name 
                                FROM task_admin t 
                                JOIN users u ON t.user_id = u.id
                                WHERE t.admin_id='$admin_id'
                                ORDER BY t.deadline ASC");

// Ambil daftar user biasa
$users = mysqli_query($koneksi, "SELECT * FROM users WHERE role='admin'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Task Admin - FitTask</title>
<style>
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body {background: linear-gradient(135deg,#0b1120 0%,#1e293b 100%); color:#e2e8f0; min-height:100vh; display:flex;}
.sidebar {width:160px;background:rgba(15,23,42,0.75);backdrop-filter:blur(12px);padding:4.2rem 1rem 2rem;display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100%;box-shadow:3px 0 15px rgba(0,0,0,0.3);}
.sidebar a {color:#e2e8f0;text-decoration:none;display:block;width:100%;padding:10px 12px;border-radius:6px;font-size:0.9rem;margin-bottom:0.6rem;transition:all 0.25s ease;}
.sidebar a.active {background-color:rgba(59,130,246,0.4);}
.sidebar a:hover {background-color:rgba(59,130,246,0.3);transform:translateX(4px);}
.navbar {position:fixed;top:0;left:0;right:0;height:42px;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 10px rgba(0,0,0,0.2);z-index:10;}
.navbar h2 {color:#60a5fa;font-size:1.1rem;}
.menu-btn {position:absolute;left:0.8rem;top:15px;width:16px;height:12px;display:flex;flex-direction:column;justify-content:space-between;background:none;border:none;cursor:pointer;}
.menu-btn div {width:100%;height:2px;background-color:#fff;border-radius:2px;}
.main-content {flex:1;margin-left:160px;padding:5rem 2rem 2rem;width:100%;}
.form-card {background:rgba(30,41,59,0.55);backdrop-filter:blur(8px);padding:1.5rem;border-radius:10px;margin-bottom:2rem;box-shadow:0 0 10px rgba(0,0,0,0.3);}
.form-card h2 {color:#60a5fa;margin-bottom:1rem;}
form input,form select,form textarea,form button {width:100%;margin-bottom:0.8rem;padding:8px 10px;border-radius:6px;border:none;outline:none;font-size:0.9rem;}
form input,form select,form textarea {background:rgba(255,255,255,0.9);color:#0f172a;}
form button {background-color:#3b82f6;color:white;cursor:pointer;transition:0.3s;}
form button:hover {background-color:#2563eb;}
table {width:100%;border-collapse:collapse;background:rgba(30,41,59,0.55);backdrop-filter:blur(8px);border-radius:8px;overflow:hidden;}
th,td {padding:10px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);}
th {background:rgba(15,23,42,0.7);color:#60a5fa;}
tr:hover {background:rgba(59,130,246,0.1);}
footer {text-align:center;padding:1rem;color:#94a3b8;font-size:0.8rem;margin-top:2rem;}
</style>
</head>
<body>
<div class="navbar">
    <button class="menu-btn" id="menuBtn"><div></div><div></div><div></div></button>
    <h2>Task Admin - <?= htmlspecialchars($admin['username']); ?></h2>
</div>

<div class="sidebar" id="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="taskadmin.php" class="active">Task Saya</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <div class="form-card">
        <h2>Tambah Task</h2>
        <form method="POST">
            <select name="user_id" required>
                <option value="">-- Pilih User --</option>
                <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <option value="<?= $u['id']; ?>"><?= htmlspecialchars($u['username']); ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="pekerjaan" placeholder="Judul Task" required>
            <textarea name="deskripsi" placeholder="Deskripsi Task (opsional)"></textarea>
            <input type="date" name="deadline" required>
            <button type="submit" name="add">Tambah Task</button>
        </form>
    </div>

    <table>
        <tr>
            <th>User</th>
            <th>Pekerjaan</th>
            <th>Deskripsi</th>
            <th>Deadline</th>
            <th>Status</th>
        </tr>
        <?php if(mysqli_num_rows($tasks) > 0): ?>
            <?php while($task = mysqli_fetch_assoc($tasks)): ?>
                <tr>
                    <td><?= htmlspecialchars($task['user_name']); ?></td>
                    <td><?= htmlspecialchars($task['pekerjaan']); ?></td>
                    <td><?= htmlspecialchars($task['deskripsi']); ?></td>
                    <td><?= htmlspecialchars($task['deadline']); ?></td>
                    <td><?= htmlspecialchars($task['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="color:#94a3b8;">Belum ada task untuk user.</td></tr>
        <?php endif; ?>
    </table>

    <footer>© 2025 FitTask</footer>
</div>

<script>
const menuBtn = document.getElementById('menuBtn');
const sidebar = document.getElementById('sidebar');
const mainContent = document.querySelector('.main-content');
menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hide');
    mainContent.classList.toggle('full');
});
</script>

</body>
</html>
