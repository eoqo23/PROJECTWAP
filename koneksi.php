<?php
$host = "mysql";       // nama service MySQL di docker-compose
$user = "user";
$pass = "user123";
$db   = "projectwapdb";

$koneksi = null; // pastikan variabel ada

$attempts = 0;
while ($attempts < 5) {
    $koneksi = @mysqli_connect($host, $user, $pass, $db);
    if ($koneksi) break;   // koneksi berhasil
    $attempts++;
    sleep(2);              // tunggu 2 detik sebelum retry
}

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
