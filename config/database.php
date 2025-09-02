<?php
// Memuat autoloader dari Composer
require_once __DIR__ . '/../vendor/autoload.php';

// --- DEFINISI BASE_URL DINAMIS ---
// Kode ini akan secara otomatis mendeteksi alamat dasar website Anda
// baik di server lokal (Laragon) maupun di hosting produksi.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Menentukan path dasar dari skrip yang sedang berjalan
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Menghapus subdirektori '/public' jika ada, untuk mendapatkan root proyek yang benar
$base_path = rtrim(str_replace('/public', '', $script_name), '/');
// Mendefinisikan konstanta BASE_URL yang bisa diakses di seluruh file
define('BASE_URL', $protocol . $host . $base_path);
// ------------------------------------

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

// === TAMBAHKAN BARIS INI UNTUK MENYAMAKAN ZONA WAKTU DATABASE ===
mysqli_set_charset($koneksi, 'utf8mb4');
//mysqli_query($koneksi, "SET time_zone = 'Asia/Jakarta'");
mysqli_query($koneksi, "SET time_zone = '+07:00'");
// ===============================================================

// Mengatur zona waktu default
date_default_timezone_set('Asia/Jakarta');
// ======================================================
// === MEMANGGIL HELPER LOG UNTUK PENCATATAN AKTIVITAS ===
// ======================================================
require_once __DIR__ . '/log_helper.php';
?>
