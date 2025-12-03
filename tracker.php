<?php
session_start();
include 'koneksi.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$user_id'");
if (!$result) die("Query gagal: " . mysqli_error($koneksi));
$user = mysqli_fetch_assoc($result);

// === Tambah data latihan ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal_latihan']);
    $exercise = mysqli_real_escape_string($koneksi, $_POST['exercise']);
    $sets = (int)$_POST['sets'];
    $reps = (int)$_POST['reps'];
    $weight = (float)$_POST['weight'];
    $note = mysqli_real_escape_string($koneksi, $_POST['note']);

    $query = "INSERT INTO tracker (user_id, tanggal_latihan, exercise, sets, reps, weight, note)
              VALUES ('$user_id', '$tanggal', '$exercise', '$sets', '$reps', '$weight', '$note')";

    if (!mysqli_query($koneksi, $query)) {
        die("Insert gagal: " . mysqli_error($koneksi));
    }
    header("Location: tracker.php");
    exit;
}

// === Update status latihan ===
if (isset($_GET['status']) && isset($_GET['status_id'])) {
    $id_status = (int)$_GET['status_id'];
    $new_status = $_GET['status'] === 'done' ? 'done' : 'pending';
    mysqli_query($koneksi, "UPDATE tracker SET status='$new_status' WHERE id='$id_status' AND user_id='$user_id'");
    header("Location: tracker.php");
    exit;
}

// === Hapus data latihan ===
if (isset($_GET['hapus'])) {
    $hapus_id = (int)$_GET['hapus'];
    $delete = mysqli_query($koneksi, "DELETE FROM tracker WHERE id='$hapus_id' AND user_id='$user_id'");
    if (!$delete) die("Hapus gagal: " . mysqli_error($koneksi));
    header("Location: tracker.php");
    exit;
}

// Ambil data tracker user yang login
$trackers = mysqli_query($koneksi, "SELECT * FROM tracker WHERE user_id='$user_id' ORDER BY tanggal_latihan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Workout Tracker - FitTask</title>
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
form input,form textarea,form button {width:100%;margin-bottom:0.8rem;padding:8px 10px;border-radius:6px;border:none;outline:none;font-size:0.9rem;}
form input,form textarea {background:rgba(255,255,255,0.9);color:#0f172a;}
form button {background-color:#3b82f6;color:white;cursor:pointer;transition:0.3s;}
form button:hover {background-color:#2563eb;}
table {width:100%;border-collapse:collapse;background:rgba(30,41,59,0.55);backdrop-filter:blur(8px);border-radius:8px;overflow:hidden;}
th,td {padding:10px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);}
th {background:rgba(15,23,42,0.7);color:#60a5fa;}
tr:hover {background:rgba(59,130,246,0.1);}
a.hapus,a.edit,a.status-btn {padding:5px 10px;border-radius:6px;color:white;text-decoration:none;transition:0.3s;margin:0 3px;display:inline-block;}
a.hapus {background:#ef4444;}
a.hapus:hover {background:#dc2626;}
a.edit {background:#22c55e;}
a.edit:hover {background:#16a34a;}
a.status-btn.done {background:#22c55e;color:white;}
a.status-btn.pending {background:#facc15;color:black;}
a.status-btn.done:hover {background:#16a34a;}
a.status-btn.pending:hover {background:#fbbf24;}
footer {text-align:center;padding:1rem;color:#94a3b8;font-size:0.8rem;margin-top:2rem;}
</style>
</head>
<body>
<div class="navbar">
    <button class="menu-btn" id="menuBtn"><div></div><div></div><div></div></button>
    <h2>Workout Tracker - <?= htmlspecialchars($user['username']); ?></h2>
</div>

<div class="sidebar" id="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="tracker.php" class="active">Tracker</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <div class="form-card">
        <h2>Tambah Latihan</h2>
        <form method="POST">
            <input type="date" name="tanggal_latihan" required>
            <input type="text" name="exercise" placeholder="Nama latihan" required>
            <input type="number" name="sets" placeholder="Jumlah set" required>
            <input type="number" name="reps" placeholder="Reps per set" required>
            <input type="number" name="weight" placeholder="Berat (kg)" step="0.1" required>
            <textarea name="note" placeholder="Catatan tambahan (opsional)"></textarea>
            <button type="submit" name="add">Tambah</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Tanggal</th>
            <th>Exercise</th>
            <th>Sets</th>
            <th>Reps</th>
            <th>Weight (kg)</th>
            <th>Note</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php if (mysqli_num_rows($trackers) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($trackers)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['tanggal_latihan']); ?></td>
                    <td><?= htmlspecialchars($row['exercise']); ?></td>
                    <td><?= htmlspecialchars($row['sets']); ?></td>
                    <td><?= htmlspecialchars($row['reps']); ?></td>
                    <td><?= htmlspecialchars($row['weight']); ?></td>
                    <td><?= htmlspecialchars($row['note']); ?></td>
                    <td>
                        <?= htmlspecialchars($row['status']); ?>
                    </td>
                    <td>
                        <a class="edit" href="edittracker.php?id=<?= $row['id']; ?>">Edit</a>
                        <a class="hapus" href="tracker.php?hapus=<?= $row['id']; ?>" onclick="return confirm('Yakin hapus latihan ini?')">Hapus</a>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a class="status-btn done" href="tracker.php?status=done&status_id=<?= $row['id']; ?>">Sudah Dilakukan</a>
                        <?php else: ?>
                            <a class="status-btn pending" href="tracker.php?status=pending&status_id=<?= $row['id']; ?>">Belum</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="color:#94a3b8;">Belum ada latihan yang ditambahkan.</td></tr>
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
