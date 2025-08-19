<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Keamanan: Hanya Super Admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    die("Akses ditolak.");
}

// Ambil parameter filter dari URL
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$id_pegawai = isset($_GET['id_pegawai']) ? (int)$_GET['id_pegawai'] : 0;
$tanggal_awal = isset($_GET['tanggal_awal']) && !empty($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '2000-01-01';
$tanggal_akhir = isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Bangun query SQL dengan filter yang sama seperti di halaman log, TAPI TANPA LIMIT
$params = [];
$types = '';
$where_clauses = [];

$sql = "SELECT l.waktu_log, p.nama_lengkap, l.level_akses, l.aktivitas 
        FROM log_aktivitas l
        JOIN tabel_pegawai p ON l.id_pegawai = p.id_pegawai";

if (!empty($keyword)) {
    $where_clauses[] = "l.aktivitas LIKE ?";
    $params[] = "%" . $keyword . "%";
    $types .= 's';
}
if ($id_pegawai > 0) {
    $where_clauses[] = "l.id_pegawai = ?";
    $params[] = $id_pegawai;
    $types .= 'i';
}
// Selalu filter berdasarkan tanggal, defaultnya rentang waktu yang sangat lebar
$where_clauses[] = "DATE(l.waktu_log) BETWEEN ? AND ?";
$params[] = $tanggal_awal;
$params[] = $tanggal_akhir;
$types .= 'ss';

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY l.waktu_log DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Siapkan header untuk file download CSV
$nama_file = "log_aktivitas_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tulis header kolom di file CSV
fputcsv($output, ['Waktu', 'Nama Pengguna', 'Level', 'Aktivitas']);

// Loop melalui data dan tulis ke file CSV
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>