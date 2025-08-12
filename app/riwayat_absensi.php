<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf_helper.php'; // Muat helper CSRF

// Redirect jika belum login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: /login");
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];

// ====================================================================
// === TAMBAHKAN BLOK INI UNTUK MEMPERBAIKI ERROR ===
// Ambil data user yang sedang login untuk keperluan modal pengaturan
$sql_user = "SELECT username FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_user = mysqli_prepare($koneksi, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $id_pegawai);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);
// ====================================================================

$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');

// --- AMBIL DAFTAR HARI LIBUR ---
$daftar_libur = [];
$sql_libur = "SELECT tanggal, keterangan FROM tabel_hari_libur WHERE tanggal BETWEEN ? AND ?";
$stmt_libur = mysqli_prepare($koneksi, $sql_libur);
mysqli_stmt_bind_param($stmt_libur, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt_libur);
$result_libur = mysqli_stmt_get_result($stmt_libur);
while($row_libur = mysqli_fetch_assoc($result_libur)) {
    $daftar_libur[$row_libur['tanggal']] = $row_libur['keterangan'];
}

// --- LOGIKA PAGINATION ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$period = new DatePeriod(
    new DateTime($tanggal_awal),
    new DateInterval('P1D'),
    (new DateTime($tanggal_akhir))->modify('+1 day')
);

$dates = iterator_to_array($period);
$total_records = count($dates);
$total_pages = ceil($total_records / $limit);
$dates_on_page = array_slice($dates, $offset, $limit);

require_once 'templates/riwayat_absensi_view.php';
?>