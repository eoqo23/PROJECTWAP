<?php
include 'koneksi.php';

// Ambil query pencarian
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Jika query kosong, tampilkan semua gym
if ($query === '') {
    $result = mysqli_query($koneksi, "SELECT * FROM gyms ORDER BY id DESC");
} else {
    $safeQuery = mysqli_real_escape_string($koneksi, $query);
    $result = mysqli_query($koneksi, "
        SELECT * FROM gyms 
        WHERE nama_gym LIKE '%$safeQuery%' 
        OR alamat LIKE '%$safeQuery%' 
        ORDER BY id DESC
    ");
}

// Jika tidak ada hasil
if (mysqli_num_rows($result) === 0) {
    echo "<p style='grid-column:1/-1;text-align:center;color:#cbd5e1;'>Tidak ada gym ditemukan untuk \"<b>" . htmlspecialchars($query) . "</b>\"</p>";
    exit;
}

// Loop hasil dan tampilkan dalam format yang sama seperti dashboard
while ($gym = mysqli_fetch_assoc($result)): ?>
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
