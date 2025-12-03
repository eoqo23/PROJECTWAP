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

// Pesan flash sederhana
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = '';

// Ambil data tracker berdasarkan ID
if (!isset($_GET['id'])) {
    $_SESSION['flash'] = "ID latihan tidak ditemukan.";
    header("Location: tracker.php");
    exit;
}

$tracker_id = (int)$_GET['id'];
$result = mysqli_query($koneksi, "SELECT * FROM tracker WHERE id='$tracker_id' AND user_id='$user_id'");
if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['flash'] = "Anda tidak memiliki izin untuk mengedit latihan ini.";
    header("Location: tracker.php");
    exit;
}

$tracker = mysqli_fetch_assoc($result);

// === Proses update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal_latihan']);
    $exercise = mysqli_real_escape_string($koneksi, $_POST['exercise']);
    $sets = (int)$_POST['sets'];
    $reps = (int)$_POST['reps'];
    $weight = (float)$_POST['weight'];
    $note = mysqli_real_escape_string($koneksi, $_POST['note']);

    $updateQuery = "UPDATE tracker SET 
        tanggal_latihan='$tanggal',
        exercise='$exercise',
        sets='$sets',
        reps='$reps',
        weight='$weight',
        note='$note'
        WHERE id='$tracker_id' AND user_id='$user_id'";

    if (mysqli_query($koneksi, $updateQuery)) {
        $_SESSION['flash'] = "Latihan berhasil diupdate!";
        header("Location: tracker.php");
        exit;
    } else {
        die("Update gagal: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Latihan - FitTask</title>
<style>
* {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body {background: linear-gradient(135deg,#0b1120 0%,#1e293b 100%); color:#e2e8f0; min-height:100vh; display:flex; justify-content:center; align-items:center;}
.form-card {background:rgba(30,41,59,0.55);backdrop-filter:blur(8px);padding:2rem;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.3);width:400px;}
.form-card h2 {color:#60a5fa;margin-bottom:1rem;text-align:center;}
form input,form textarea,form button {width:100%;margin-bottom:0.8rem;padding:8px 10px;border-radius:6px;border:none;outline:none;font-size:0.9rem;}
form input,form textarea {background:rgba(255,255,255,0.9);color:#0f172a;}
form button {background-color:#3b82f6;color:white;cursor:pointer;transition:0.3s;}
form button:hover {background-color:#2563eb;}
a.cancel {display:block;text-align:center;color:#f87171;text-decoration:none;margin-top:0.5rem;}
.flash {background:#2563eb;color:white;padding:8px;border-radius:6px;text-align:center;margin-bottom:1rem;}
</style>
</head>
<body>
<div class="form-card">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="flash"><?= $_SESSION['flash']; ?></div>
        <?php $_SESSION['flash'] = ''; ?>
    <?php endif; ?>
    <h2>Edit Latihan</h2>
    <form method="POST">
        <input type="date" name="tanggal_latihan" value="<?= htmlspecialchars($tracker['tanggal_latihan']); ?>" required>
        <input type="text" name="exercise" value="<?= htmlspecialchars($tracker['exercise']); ?>" placeholder="Nama latihan" required>
        <input type="number" name="sets" value="<?= htmlspecialchars($tracker['sets']); ?>" placeholder="Jumlah set" required>
        <input type="number" name="reps" value="<?= htmlspecialchars($tracker['reps']); ?>" placeholder="Reps per set" required>
        <input type="number" name="weight" value="<?= htmlspecialchars($tracker['weight']); ?>" placeholder="Berat (kg)" step="0.1" required>
        <textarea name="note" placeholder="Catatan tambahan (opsional)"><?= htmlspecialchars($tracker['note']); ?></textarea>
        <button type="submit" name="update">Update</button>
    </form>
    <a class="cancel" href="tracker.php">Batal</a>
</div>
</body>
</html>
