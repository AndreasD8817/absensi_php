<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_pegawai'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

require_once 'config/database.php';

function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $r = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $r * $c;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['catatan']) || !isset($data['foto'])) {
    echo json_encode(['sukses' => false, 'pesan' => 'Request tidak valid atau data kurang lengkap.']);
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];
$tipe_absensi = $data['tipe'];
$latitude_user = $data['latitude'];
$longitude_user = $data['longitude'];
$catatan = trim($data['catatan']);
$fotoBase64 = $data['foto'];

if (empty($catatan)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Catatan wajib diisi.']);
    exit();
}
if (empty($fotoBase64)) {
    echo json_encode(['sukses' => false, 'pesan' => 'Foto selfie wajib diambil.']);
    exit();
}

$pengaturan = [];
$sql_pengaturan = "SELECT nama_pengaturan, nilai_pengaturan FROM tabel_pengaturan";
$result_pengaturan = mysqli_query($koneksi, $sql_pengaturan);
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    $pengaturan[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}

$jarak = hitungJarak($latitude_user, $longitude_user, $pengaturan['lokasi_lat'], $pengaturan['lokasi_lon']);

if ($jarak > (int)$pengaturan['radius_meter']) {
    echo json_encode(['sukses' => false, 'pesan' => 'Absensi Gagal! Anda berada ' . round($jarak) . ' meter dari lokasi kantor.']);
    exit();
}

$folder_foto = 'uploads/foto_absen/';
if (!is_dir($folder_foto)) {
    mkdir($folder_foto, 0777, true);
}
$nama_file_foto = $id_pegawai . '_' . time() . '_' . uniqid() . '.jpg';
$path_foto = $folder_foto . $nama_file_foto;

$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64));
if (file_put_contents($path_foto, $imageData)) {
    $waktu_sekarang = date('Y-m-d H:i:s');
    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, latitude, longitude, catatan, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql_insert);
    
    // PERBAIKAN DI SINI: Tipe data diubah dari "isssdds" menjadi "issddss"
    mysqli_stmt_bind_param($stmt, "issddss", $id_pegawai, $tipe_absensi, $waktu_sekarang, $latitude_user, $longitude_user, $catatan, $nama_file_foto);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['sukses' => true, 'pesan' => 'Absen ' . $tipe_absensi . ' berhasil pada jam ' . date('H:i') . ' dengan foto.']);
    } else {
        if (file_exists($path_foto)) {
            unlink($path_foto);
        }
        echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan data absensi.']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan foto selfie.']);
}

mysqli_close($koneksi);