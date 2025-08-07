<?php
// Pengaturan untuk lingkungan produksi (hosting)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');
// Pengaturan untuk koneksi ke database
$db_host = 'localhost';     // Biasanya 'localhost' atau '127.0.0.1' di Laragon
//$db_host = '192.168.62.156';
$db_user = 'root';          // User default MySQL di Laragon
$db_pass = '';              // Password default MySQL di Laragon kosong
$db_name = 'db_absensi';    // Nama database yang kita buat

// Membuat koneksi menggunakan MySQLi
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
// Jika gagal, hentikan skrip dan tampilkan pesan error
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Mengatur zona waktu default
date_default_timezone_set('Asia/Jakarta');
?>