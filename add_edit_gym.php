<?php
session_start();
include 'koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_SESSION['user_id'];
$result = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($result);
if (!$user || $user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Ambil data gym jika mode edit
$gym = null;
if (isset($_GET['id'])) {
    $id_gym = (int)$_GET['id'];
    $res = mysqli_query($koneksi, "SELECT * FROM gyms WHERE id='$id_gym'");
    $gym = mysqli_fetch_assoc($res);
}

// Pastikan folder uploads ada & writable
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
chmod($uploadDir, 0777);

// Proses form submit
if (isset($_POST['simpan'])) {
    $nama_gym = mysqli_real_escape_string($koneksi, $_POST['nama_gym']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $harga    = (float)$_POST['harga'];
    $rating   = (float)$_POST['rating'];

    // Upload gambar
    $gambar = $gym['gambar'] ?? null;
    if (!empty($_FILES['gambar']['name'])) {
        $fileName = time() . "_" . basename($_FILES["gambar"]["name"]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetFilePath)) {
            $gambar = "uploads/" . $fileName; // simpan path relatif
        } else {
            die("Gagal upload gambar. Pastikan folder 'uploads' writable.");
        }
    }

    // Cek kolom gambar
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM gyms LIKE 'gambar'");
    $gambarColumnExists = mysqli_num_rows($check) > 0;

    if ($gym) {
        // Update
        $query = $gambarColumnExists
            ? "UPDATE gyms SET nama_gym='$nama_gym', alamat='$alamat', harga_per_bulan='$harga', rating='$rating', gambar='$gambar' WHERE id=" . $gym['id']
            : "UPDATE gyms SET nama_gym='$nama_gym', alamat='$alamat', harga_per_bulan='$harga', rating='$rating' WHERE id=" . $gym['id'];
    } else {
        // Insert
        $query = $gambarColumnExists
            ? "INSERT INTO gyms (nama_gym, alamat, harga_per_bulan, rating, gambar) VALUES ('$nama_gym','$alamat','$harga','$rating','$gambar')"
            : "INSERT INTO gyms (nama_gym, alamat, harga_per_bulan, rating) VALUES ('$nama_gym','$alamat','$harga','$rating')";
    }

    if (!mysqli_query($koneksi, $query)) {
        die("Query error: " . mysqli_error($koneksi));
    }

    header("Location: dashboardadmin.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $gym ? 'Edit' : 'Tambah'; ?> Gym - FitTask</title>
</head>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #0b1120 0%, #1e293b 100%);
    color: #e2e8f0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

/* === TITLE === */
h1 {
    text-align: center;
    color: #60a5fa;
    margin-bottom: 1.5rem;
    text-shadow: 0 0 10px rgba(96, 165, 250, 0.4);
}

/* === FORM === */
form {
    background: rgba(30, 41, 59, 0.55);
    backdrop-filter: blur(10px);
    padding: 2rem;
    border-radius: 1rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Input & Textarea Style */
form input[type="text"],
form input[type="number"],
form textarea,
form input[type="file"] {
    padding: 0.9rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    background-color: #1e293b;
    color: #e2e8f0;
    box-shadow: inset 0 0 4px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

form input::placeholder,
form textarea::placeholder {
    color: #94a3b8;
}

form input:focus,
form textarea:focus {
    outline: none;
    box-shadow: 0 0 6px #3b82f6;
}

form textarea {
    resize: vertical;
    min-height: 80px;
}

/* File input style */
form input[type="file"] {
    background: transparent;
    color: #cbd5e1;
    padding: 0.4rem 0;
}

/* Button */
form button[type="submit"] {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 0.9rem 1.2rem;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    font-weight: 600;
    letter-spacing: 0.3px;
}

form button[type="submit"]:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
}

/* Link Kembali */
a {
    display: inline-block;
    margin-top: 1.2rem;
    text-align: center;
    color: #60a5fa;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

a:hover {
    color: #93c5fd;
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
    form {
        padding: 1.5rem;
    }

    h1 {
        font-size: 1.3rem;
    }
}

</style>
<body>
    <h1><?= $gym ? 'Edit' : 'Tambah'; ?> Gym</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_gym" placeholder="Nama Gym" value="<?= htmlspecialchars($gym['nama_gym'] ?? ''); ?>" required>
        <textarea name="alamat" placeholder="Alamat Gym" required><?= htmlspecialchars($gym['alamat'] ?? ''); ?></textarea>
        <input type="number" name="harga" placeholder="Harga per Bulan" value="<?= $gym['harga_per_bulan'] ?? ''; ?>" required>
        <input type="number" name="rating" placeholder="Rating (0-5)" min="0" max="5" step="0.1" value="<?= $gym['rating'] ?? ''; ?>" required>
        <input type="file" name="gambar">
        <button type="submit" name="simpan"><?= $gym ? 'Update' : 'Tambah'; ?></button>
    </form>
    <a href="dashboardadmin.php">Kembali ke Dashboard</a>
</body>
</html>
