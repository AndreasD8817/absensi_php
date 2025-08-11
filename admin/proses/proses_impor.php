<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php'; // Panggil helper CSRF

// Keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /admin?error=Akses ditolak");

    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_absensi'])) {
    // === VALIDASI CSRF TOKEN ===
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.'); // Hentikan jika tidak valid
    }
    
    if ($_FILES['file_absensi']['error'] !== UPLOAD_ERR_OK) {
        header("Location: /admin/impor-absensi?error=Terjadi kesalahan saat mengunggah file.");
        exit();
    }

    $file_tmp_path = $_FILES['file_absensi']['tmp_name'];

    $file_handle = fopen($file_tmp_path, 'r');
    if (!$file_handle) {
        header("Location: /admin/impor-absensi?error=Gagal membuka file yang diunggah.");
        exit();
    }

    $sukses_masuk = 0;
    $sukses_pulang = 0;
    $gagal_nip = 0;
    $baris_ke = 0;

    while (($line_str = fgets($file_handle)) !== FALSE) {
        $baris_ke++;
        if ($baris_ke <= 2) continue; // Lewati 2 baris header

        // --- LOGIKA PARSING BARU YANG LEBIH ANDAL ---
        $parts = explode(',', $line_str);
        if (count($parts) < 6) continue;

        // Ambil data dari ujung kanan (yang pasti)
        $scan_pulang = trim(array_pop($parts));
        $scan_masuk = trim(array_pop($parts));
        
        // Ambil data dari ujung kiri (yang pasti)
        $tanggal_str = trim(array_shift($parts));
        $nip = trim(array_shift($parts));
        
        // Sisa dari array adalah Nama dan Jabatan, kita tidak perlukan untuk impor ini
        // --- AKHIR LOGIKA PARSING BARU ---

        if (empty($nip)) continue;

        $sql_pegawai = "SELECT id_pegawai FROM tabel_pegawai WHERE nip = ?";
        $stmt_pegawai = mysqli_prepare($koneksi, $sql_pegawai);
        mysqli_stmt_bind_param($stmt_pegawai, "s", $nip);
        mysqli_stmt_execute($stmt_pegawai);
        $result_pegawai = mysqli_stmt_get_result($stmt_pegawai);
        
        if ($pegawai_row = mysqli_fetch_assoc($result_pegawai)) {
            $id_pegawai = $pegawai_row['id_pegawai'];

            $tanggal_obj = DateTime::createFromFormat('d-m-Y', $tanggal_str);
            if (!$tanggal_obj) continue;
            $tanggal_db = $tanggal_obj->format('Y-m-d');

            // --- Proses Scan Masuk ---
            if (!empty($scan_masuk)) {
                $waktu_absensi = $tanggal_db . ' ' . $scan_masuk;
                
                $sql_cek = "SELECT id_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Masuk'";
                $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
                mysqli_stmt_bind_param($stmt_cek, "is", $id_pegawai, $tanggal_db);
                mysqli_stmt_execute($stmt_cek);
                if (mysqli_stmt_get_result($stmt_cek)->num_rows == 0) {
                    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, latitude, longitude, catatan, foto) VALUES (?, 'Masuk', ?, ?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
                    $lat = null; $lon = null; $catatan = 'Finger Absensi Mesin Masuk'; $foto = null;
                    mysqli_stmt_bind_param($stmt_insert, "isddss", $id_pegawai, $waktu_absensi, $lat, $lon, $catatan, $foto);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $sukses_masuk++;
                    }
                }
            }

            // --- Proses Scan Pulang ---
            if (!empty($scan_pulang)) {
                $waktu_absensi = $tanggal_db . ' ' . $scan_pulang;

                $sql_cek = "SELECT id_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Pulang'";
                $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
                mysqli_stmt_bind_param($stmt_cek, "is", $id_pegawai, $tanggal_db);
                mysqli_stmt_execute($stmt_cek);
                if (mysqli_stmt_get_result($stmt_cek)->num_rows == 0) {
                    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, latitude, longitude, catatan, foto) VALUES (?, 'Pulang', ?, ?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
                    $lat = null; $lon = null; $catatan = 'Finger Absensi Mesin Pulang'; $foto = null;
                    mysqli_stmt_bind_param($stmt_insert, "isddss", $id_pegawai, $waktu_absensi, $lat, $lon, $catatan, $foto);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $sukses_pulang++;
                    }
                }
            }

        } else {
            $gagal_nip++;
        }
    }

    fclose($file_handle);
    header("Location: /admin/impor-absensi?sukses_masuk=$sukses_masuk&sukses_pulang=$sukses_pulang&gagal_nip=$gagal_nip");
    exit();

} else {
    header("Location: /admin/impor-absensi?error=Tidak ada file yang diunggah.");
    exit();
}
?>
