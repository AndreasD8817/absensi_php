<?php
// =================================================================
// CONTROLLER: dashboard.php
// File ini sekarang hanya bertugas sebagai Controller.
// Tugasnya:
// 1. Mengatur keamanan dan sesi.
// 2. Mengambil data dari database (Model).
// 3. Menyiapkan variabel untuk ditampilkan.
// 4. Memanggil file View untuk menampilkan halaman.
// =================================================================

session_start();
require_once __DIR__ . '/config/database.php';

// 1. Keamanan: Redirect jika belum login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: /login");
    exit();
}

// 2. Persiapan Data: Ambil dari session
$id_pegawai = $_SESSION['id_pegawai'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];
$hari_ini = date('Y-m-d');

// 3. Logika Bisnis: Cek status absensi terakhir hari ini dari database (Model)
$sql_cek = "SELECT tipe_absensi FROM tabel_absensi 
            WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? 
            ORDER BY waktu_absensi DESC LIMIT 1";

$stmt_cek = mysqli_prepare($koneksi, $sql_cek);
mysqli_stmt_bind_param($stmt_cek, "is", $id_pegawai, $hari_ini);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

$status_terakhir = null;
if ($row = mysqli_fetch_assoc($result_cek)) {
    $status_terakhir = $row['tipe_absensi'];
}

// 4. Siapkan variabel untuk View
$bisa_absen_masuk = ($status_terakhir === null);
$bisa_absen_pulang = ($status_terakhir === 'Masuk');
$sudah_selesai = ($status_terakhir === 'Pulang' || $status_terakhir === 'Dinas Luar');


// 5. Panggil View: Setelah semua data siap, panggil file view untuk menampilkannya.
// Semua variabel PHP di atas akan bisa diakses di dalam file view.
require_once 'templates/dashboard_view.php';