<?php
// =================================================================
// CONTROLLER: dashboard.php
// =================================================================

session_start();
require_once __DIR__ . '/../config/database.php';

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

// === BARU: Ambil data username untuk modal pengaturan ===
$sql_user = "SELECT username FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_user = mysqli_prepare($koneksi, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $id_pegawai);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);
// ========================================================

// 3. Logika Bisnis: Cek status absensi terakhir hari ini
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

// Logika untuk menentukan batas jam pulang
$hari_angka = date('w'); 
$waktu_sekarang = date('H:i:s');
$batas_pulang_str = ''; 

switch ($hari_angka) {
    case 1: case 2: case 3: case 4: case 5:
        $batas_pulang_str = '16:00:00'; 
        break;
    case 6: // Sabtu
        $batas_pulang_str = '14:00:00'; 
        break;
}

$lewat_jam_pulang = false;
if ($batas_pulang_str != '' && $waktu_sekarang > $batas_pulang_str) {
    $lewat_jam_pulang = true;
}

// 4. Siapkan variabel untuk View
$sudah_selesai = ($status_terakhir === 'Pulang' || $status_terakhir === 'Dinas Luar');
$bisa_absen_masuk = ($status_terakhir === null && !$lewat_jam_pulang);
$bisa_absen_pulang = ($status_terakhir === 'Masuk' || ($lewat_jam_pulang && $status_terakhir === null));
$bisa_dinas_luar = ($status_terakhir === null);

// 5. Panggil View
require_once 'templates/dashboard_view.php';