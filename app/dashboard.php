<?php
// =================================================================
// CONTROLLER: dashboard.php (Versi Perbaikan)
// =================================================================

session_start();
// Blok auto logout
$idle_timeout = 1800; // 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $idle_timeout)) {
    session_unset();
    session_destroy();
    header("Location: /login?error=Sesi Anda telah berakhir karena tidak ada aktivitas.");
    exit();
}
$_SESSION['last_activity'] = time(); // Perbarui waktu aktivitas

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf_helper.php';

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
$bulan_awal = date('Y-m-01');
$bulan_akhir = date('Y-m-t');

// Ambil data username untuk modal pengaturan
$sql_user = "SELECT username FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_user = mysqli_prepare($koneksi, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $id_pegawai);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

// 3. Logika Bisnis: Cek status absensi terakhir hari ini
// --- PERUBAHAN DI SINI: Ambil juga file_surat_tugas jika ada ---
$sql_cek = "SELECT a.tipe_absensi, dl.file_surat_tugas 
            FROM tabel_absensi a
            LEFT JOIN tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi
            WHERE a.id_pegawai = ? AND DATE(a.waktu_absensi) = ? 
            ORDER BY a.waktu_absensi DESC LIMIT 1";
$stmt_cek = mysqli_prepare($koneksi, $sql_cek);
mysqli_stmt_bind_param($stmt_cek, "is", $id_pegawai, $hari_ini);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

$status_terakhir = null;
$file_dinas_luar_hari_ini = null; // Variabel baru untuk menyimpan nama file
if ($row = mysqli_fetch_assoc($result_cek)) {
    $status_terakhir = $row['tipe_absensi'];
    if ($status_terakhir === 'Dinas Luar') {
        $file_dinas_luar_hari_ini = $row['file_surat_tugas'];
    }
}

// =================================================================
// === PERBAIKAN UTAMA: AMBIL SEMUA HARI LIBUR UNTUK KALENDER ===
// =================================================================
$daftar_libur = [];
// Query untuk mengambil semua hari libur pada rentang bulan ini
$sql_libur = "SELECT tanggal, keterangan FROM tabel_hari_libur WHERE tanggal BETWEEN ? AND ?";
$stmt_libur = mysqli_prepare($koneksi, $sql_libur);
mysqli_stmt_bind_param($stmt_libur, "ss", $bulan_awal, $bulan_akhir);
mysqli_stmt_execute($stmt_libur);
$result_libur = mysqli_stmt_get_result($stmt_libur);
// Simpan hasilnya dalam array agar bisa digunakan di view
while($row_libur = mysqli_fetch_assoc($result_libur)) {
    $daftar_libur[$row_libur['tanggal']] = $row_libur['keterangan'];
}
// Cek apakah hari ini adalah hari libur yang ditetapkan admin
$is_libur_ditetapkan = isset($daftar_libur[$hari_ini]);
// ===============================================================


// Logika untuk menentukan batas jam pulang dan hari libur
$hari_angka = date('w'); // 0 untuk Minggu, 6 untuk Sabtu
$waktu_sekarang = date('H:i:s');
$batas_pulang_str = ''; 
$is_hari_kerja = true; // Asumsikan hari ini adalah hari kerja

// Jika hari ini adalah Minggu ATAU terdaftar sebagai hari libur
if ($hari_angka == 0 || $is_libur_ditetapkan) { 
    $is_hari_kerja = false;
}

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
$bisa_absen_masuk = ($status_terakhir === null && !$lewat_jam_pulang && $is_hari_kerja);
$bisa_absen_pulang = (($status_terakhir === 'Masuk' || ($lewat_jam_pulang && $status_terakhir === null)) && $is_hari_kerja);
$bisa_dinas_luar = ($status_terakhir === null && $is_hari_kerja);

// Jika hari ini bukan hari kerja, pastikan semua tombol non-aktif
if (!$is_hari_kerja) {
    $bisa_absen_masuk = false;
    $bisa_absen_pulang = false;
    $bisa_dinas_luar = false;
}

// Ambil data untuk rekap hari ini
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

// 5. Panggil View
require_once __DIR__ . '/../templates/dashboard_view.php';
