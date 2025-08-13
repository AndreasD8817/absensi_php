<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/csrf_helper.php'; // Panggil helper CSRF

// === VALIDASI CSRF TOKEN dari HEADER ===
$header_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validate_csrf_token($header_token)) {
    echo json_encode(['sukses' => false, 'pesan' => 'CSRF Token tidak valid. Silakan muat ulang halaman.']);
    exit();
}

// Cek sesi login
if (!isset($_SESSION['id_pegawai'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Akses ditolak. Silakan login.']);
    exit();
}

require_once __DIR__ . '/../config/database.php';


// --- VALIDASI INPUT & FILE ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['keterangan']) || empty($_FILES['surat_tugas'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Data tidak lengkap.']);
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];
$keterangan = $_POST['keterangan'];
$latitude_user = $_POST['latitude'];
$longitude_user = $_POST['longitude'];
$file_surat = $_FILES['surat_tugas'];

// Cek error upload
if ($file_surat['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['sukses' => false, 'pesan' => 'Terjadi error saat mengunggah file.']);
    exit();
}

// Cek ukuran file (maks 2MB)
if ($file_surat['size'] > 2 * 1024 * 1024) {
    echo json_encode(['sukses' => false, 'pesan' => 'Ukuran file terlalu besar. Maksimal 2 MB.']);
    exit();
}

// Cek tipe file (MIME type)
$allowed_types = ['application/pdf', 'image/jpeg'];
$file_info = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($file_info, $file_surat['tmp_name']);
finfo_close($file_info);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Tipe file tidak diizinkan. Hanya PDF atau JPG.']);
    exit();
}

// --- PROSES UPLOAD FILE ---
// Tujuan folder, relatif dari lokasi file (public/proses_dinas_luar.php)
$folder_upload = 'public/uploads/foto_dinas_luar/';

// Cek apakah folder sudah ada, jika tidak, buat folder tersebut
if (!is_dir($folder_upload)) {
    // Parameter ketiga 'true' memungkinkan pembuatan folder secara rekursif
    mkdir($folder_upload, 0777, true);
}

$nama_file_unik = $id_pegawai . '_' . time() . '_' . basename($file_surat['name']);
$path_tujuan = $folder_upload . $nama_file_unik;

if (!move_uploaded_file($file_surat['tmp_name'], $path_tujuan)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Gagal memindahkan file yang diunggah.']);
    exit();
}

// --- SIMPAN KE DATABASE (TRANSACTION) ---
mysqli_begin_transaction($koneksi);

try {
    // 1. Insert ke tabel_absensi
    $waktu_sekarang = date('Y-m-d H:i:s');
    $tipe_absensi = 'Dinas Luar';
    $sql_absensi = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, latitude, longitude) VALUES (?, ?, ?, ?, ?)";
    $stmt_absensi = mysqli_prepare($koneksi, $sql_absensi);
    mysqli_stmt_bind_param($stmt_absensi, "issdd", $id_pegawai, $tipe_absensi, $waktu_sekarang, $latitude_user, $longitude_user);
    mysqli_stmt_execute($stmt_absensi);

    // Dapatkan ID dari absensi yang baru saja di-insert
    $id_absensi_baru = mysqli_insert_id($koneksi);
    if ($id_absensi_baru == 0) {
        throw new Exception("Gagal mendapatkan ID absensi.");
    }

    // 2. Insert ke tabel_dinas_luar
    $sql_dinas = "INSERT INTO tabel_dinas_luar (id_absensi, file_surat_tugas, keterangan) VALUES (?, ?, ?)";
    $stmt_dinas = mysqli_prepare($koneksi, $sql_dinas);
    mysqli_stmt_bind_param($stmt_dinas, "iss", $id_absensi_baru, $nama_file_unik, $keterangan);
    mysqli_stmt_execute($stmt_dinas);

    // Jika semua berhasil, commit transaksi
    mysqli_commit($koneksi);
    echo json_encode(['sukses' => true, 'pesan' => 'Absensi Dinas Luar berhasil direkam.']);

} catch (Exception $e) {
    // Jika ada error, rollback semua perubahan
    mysqli_rollback($koneksi);
    // Hapus file yang sudah terlanjur di-upload jika terjadi error database
    if (file_exists($path_tujuan)) {
        unlink($path_tujuan);
    }
    echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan data ke database. ' . $e->getMessage()]);
}

mysqli_close($koneksi);
?>