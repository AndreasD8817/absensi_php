<?php
// Memuat autoloader dari Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Menggunakan library Dotenv untuk memuat file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Pengaturan untuk lingkungan produksi (hosting)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

// --- PENGAMBILAN DATA DARI .ENV ---
// Ambil variabel dari .env, dengan nilai default jika tidak ditemukan
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? 'dprdsby_db_absensi';

// Membuat koneksi menggunakan MySQLi
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$koneksi) {
    // Tampilkan pesan error yang lebih umum di produksi
    // dan catat detail error ke log untuk developer.
    error_log("Koneksi ke database gagal: " . mysqli_connect_error());
    die("Koneksi ke server gagal. Silakan coba beberapa saat lagi.");
}

// Mengatur zona waktu default
date_default_timezone_set('Asia/Jakarta');
// ======================================================
// === MEMANGGIL HELPER LOG UNTUK PENCATATAN AKTIVITAS ===
// ======================================================
require_once __DIR__ . '/log_helper.php';
?>