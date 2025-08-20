<?php
// =================================================================
// CONTROLLER: dashboard.php
// =================================================================

session_start();
// === TAMBAHKAN BLOK AUTO LOGOUT DI SINI ===
$idle_timeout = 1800; // 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $idle_timeout)) {
    session_unset();
    session_destroy();
    header("Location: /login?error=Sesi Anda telah berakhir karena tidak ada aktivitas.");
    exit();
}
$_SESSION['last_activity'] = time(); // Perbarui waktu aktivitas
// =======================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf_helper.php'; // Panggil helper CSRF

// === TAMBAHKAN BARIS INI ===
generate_csrf_token(); // Pastikan token selalu ada di sesi

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

// =================================================================
// === BARU: Ambil data untuk dashboard view baru ===
// =================================================================
$absen_hari_ini = [
    'masuk' => null,
    'pulang' => null
];

$sql_absensi_hari_ini = "SELECT tipe_absensi, waktu_absensi 
                         FROM tabel_absensi 
                         WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? 
                         ORDER BY waktu_absensi ASC";

$stmt_absensi = mysqli_prepare($koneksi, $sql_absensi_hari_ini);
mysqli_stmt_bind_param($stmt_absensi, "is", $id_pegawai, $hari_ini);
mysqli_stmt_execute($stmt_absensi);
$result_absensi = mysqli_stmt_get_result($stmt_absensi);

while ($row = mysqli_fetch_assoc($result_absensi)) {
    if ($row['tipe_absensi'] == 'Masuk') {
        $absen_hari_ini['masuk'] = new DateTime($row['waktu_absensi']);
    } elseif ($row['tipe_absensi'] == 'Pulang') {
        $absen_hari_ini['pulang'] = new DateTime($row['waktu_absensi']);
    }
}
// =================================================================

// 5. Panggil View
require_once 'templates/dashboard_view.php';