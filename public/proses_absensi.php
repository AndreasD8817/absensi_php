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

// Pastikan hanya pegawai yang sudah login yang bisa mengakses.
if (!isset($_SESSION['id_pegawai'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

require_once __DIR__ . '/../config/database.php';


/**
 * Fungsi untuk menghitung jarak antara dua titik koordinat geografis.
 * Mengembalikan jarak dalam satuan meter.
 */
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $r = 6371000; // Radius bumi dalam meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $r * $c;
}

// Ambil data yang dikirim dari aplikasi (via JSON).
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['catatan']) || !isset($data['foto']) || !isset($data['tipe']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Request tidak valid atau data tidak lengkap.']);
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];
$tipe_absensi = $data['tipe'];
$latitude_user = $data['latitude'];
$longitude_user = $data['longitude'];
$catatan = trim($data['catatan']);
$fotoBase64 = $data['foto'];

// Validasi input dasar.
if (empty($catatan)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Catatan wajib diisi.']);
    exit();
}
if (empty($fotoBase64)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Foto selfie wajib diambil.']);
    exit();
}

// --- LOGIKA BARU DIMULAI DI SINI ---

// 1. Ambil data pegawai yang sedang login untuk memeriksa radius kustom.
// Kita menggunakan prepared statement untuk keamanan.
$sql_pegawai = "SELECT radius_absensi FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_pegawai = mysqli_prepare($koneksi, $sql_pegawai);
mysqli_stmt_bind_param($stmt_pegawai, "i", $id_pegawai);
mysqli_stmt_execute($stmt_pegawai);
$result_pegawai = mysqli_stmt_get_result($stmt_pegawai);
$data_pegawai = mysqli_fetch_assoc($result_pegawai);
mysqli_stmt_close($stmt_pegawai);

// 2. Ambil pengaturan global (lokasi kantor dan radius default).
$pengaturan = [];
$sql_pengaturan = "SELECT nama_pengaturan, nilai_pengaturan FROM tabel_pengaturan";
$result_pengaturan = mysqli_query($koneksi, $sql_pengaturan);
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    $pengaturan[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}
$lokasi_lat_kantor = $pengaturan['lokasi_lat'];
$lokasi_lon_kantor = $pengaturan['lokasi_lon'];
$radius_global = (int)$pengaturan['radius_meter'];

// 3. LOGIKA UTAMA: Tentukan radius mana yang akan digunakan untuk validasi.
$radius_validasi = $radius_global; // Secara default, gunakan radius global.

// Periksa apakah pegawai punya radius khusus (tidak NULL di database).
if ($data_pegawai && !is_null($data_pegawai['radius_absensi'])) {
    // Jika ada, ganti nilai radius validasi dengan radius khusus milik pegawai.
    $radius_validasi = (int)$data_pegawai['radius_absensi'];
}

// 4. Hitung jarak aktual pengguna dari lokasi kantor.
$jarak = hitungJarak($latitude_user, $longitude_user, $lokasi_lat_kantor, $lokasi_lon_kantor);

// 5. Validasi jarak MENGGUNAKAN radius yang sudah ditentukan (global atau khusus).
if ($jarak > $radius_validasi) {
    // Pesan error dibuat lebih informatif, menunjukkan jarak dan radius yang diizinkan.
    echo json_encode(['sukses' => false, 'pesan' => 'Absensi Gagal! Anda berada ' . round($jarak) . ' meter dari lokasi. Radius yang diizinkan untuk Anda adalah ' . $radius_validasi . ' meter.']);
    exit();
}

// --- LOGIKA BARU BERAKHIR DI SINI ---

// Proses penyimpanan foto (tidak ada perubahan dari kode sebelumnya).
$folder_foto = 'uploads/foto_absen/';
if (!is_dir($folder_foto)) {
    mkdir($folder_foto, 0777, true);
}
$nama_file_foto = $id_pegawai . '_' . time() . '_' . uniqid() . '.jpg';
$path_foto = $folder_foto . $nama_file_foto;

$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
if (file_put_contents($path_foto, $imageData)) {
    // Jika foto berhasil disimpan, masukkan data absensi ke database.
    $waktu_sekarang = date('Y-m-d H:i:s');
    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, latitude, longitude, catatan, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
    
    // Bind parameter dengan tipe data yang benar (integer, string, string, double, double, string, string).
    mysqli_stmt_bind_param($stmt_insert, "issddss", $id_pegawai, $tipe_absensi, $waktu_sekarang, $latitude_user, $longitude_user, $catatan, $nama_file_foto);

    if (mysqli_stmt_execute($stmt_insert)) {
        // === TAMBAHKAN LOG DI SINI ===
        $aktivitas = "Melakukan absensi '$tipe_absensi' via aplikasi.";
        catat_log($koneksi, $id_pegawai, $_SESSION['role'], $aktivitas);
        // =============================
        echo json_encode(['sukses' => true, 'pesan' => 'Absen ' . $tipe_absensi . ' berhasil pada jam ' . date('H:i') . '.']);
    } else {
        // Jika gagal insert DB, hapus foto yang sudah terupload untuk mencegah sampah file.
        if (file_exists($path_foto)) {
            unlink($path_foto);
        }
        echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan data absensi ke database.']);
    }
    mysqli_stmt_close($stmt_insert);
} else {
    echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan file foto selfie.']);
}

mysqli_close($koneksi);
?>
