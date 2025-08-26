<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php';

// Keamanan
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /admin?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_absensi'])) {
    // Validasi CSRF TOKEN
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
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

    // Variabel notifikasi baru
    $sukses_masuk_baru = 0;
    $sukses_pulang_baru = 0;
    $diupdate_masuk = 0;
    $diupdate_pulang = 0;
    $gagal_nip = 0;
    $dilewati_dl = 0;
    $baris_ke = 0;

    while (($line_str = fgets($file_handle)) !== FALSE) {
        $baris_ke++;
        if ($baris_ke <= 2) continue; // Lewati 2 baris header

        $parts = explode(',', $line_str);
        if (count($parts) < 6) continue;

        $scan_pulang = trim(array_pop($parts));
        $scan_masuk = trim(array_pop($parts));
        $tanggal_str = trim(array_shift($parts));
        $nip = trim(array_shift($parts));

        if (empty($nip)) continue;

        // Cek NIP pegawai
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
            $hari_angka = $tanggal_obj->format('w'); // 0=Minggu, 6=Sabtu

            // --- LOGIKA 1: PRIORITASKAN DINAS LUAR ---
            $sql_cek_dl = "SELECT id_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Dinas Luar'";
            $stmt_cek_dl = mysqli_prepare($koneksi, $sql_cek_dl);
            mysqli_stmt_bind_param($stmt_cek_dl, "is", $id_pegawai, $tanggal_db);
            mysqli_stmt_execute($stmt_cek_dl);
            if (mysqli_stmt_get_result($stmt_cek_dl)->num_rows > 0) {
                $dilewati_dl++;
                continue; // Lewati baris ini jika sudah ada data Dinas Luar
            }

            // Tentukan jam kerja
            $batas_masuk_str = ($hari_angka >= 1 && $hari_angka <= 5) ? '07:30:00' : '08:00:00';
            $batas_pulang_str = ($hari_angka >= 1 && $hari_angka <= 5) ? '16:00:00' : '14:00:00';

            // --- LOGIKA 2: PROSES SCAN MASUK ---
            if (!empty($scan_masuk)) {
                $waktu_mesin = new DateTime($tanggal_db . ' ' . $scan_masuk);

                $sql_cek_app = "SELECT id_absensi, waktu_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Masuk'";
                $stmt_cek_app = mysqli_prepare($koneksi, $sql_cek_app);
                mysqli_stmt_bind_param($stmt_cek_app, "is", $id_pegawai, $tanggal_db);
                mysqli_stmt_execute($stmt_cek_app);
                $result_app = mysqli_stmt_get_result($stmt_cek_app);
                $data_app = $result_app->fetch_assoc();

                if ($data_app) { // Jika ada data dari aplikasi, bandingkan
                    $waktu_app = new DateTime($data_app['waktu_absensi']);
                    // Pilih waktu yang lebih awal (lebih baik)
                    if ($waktu_mesin < $waktu_app) {
                        $sql_update = "UPDATE tabel_absensi SET waktu_absensi = ?, catatan = 'Finger Mesin (Data Terbaik)' WHERE id_absensi = ?";
                        $stmt_update = mysqli_prepare($koneksi, $sql_update);
                        $waktu_mesin_str = $waktu_mesin->format('Y-m-d H:i:s');
                        mysqli_stmt_bind_param($stmt_update, "si", $waktu_mesin_str, $data_app['id_absensi']);
                        if(mysqli_stmt_execute($stmt_update)) $diupdate_masuk++;
                    }
                } else { // Jika tidak ada data, langsung insert
                    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, catatan) VALUES (?, 'Masuk', ?, 'Finger Absensi Mesin Masuk')";
                    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
                    $waktu_mesin_str = $waktu_mesin->format('Y-m-d H:i:s');
                    mysqli_stmt_bind_param($stmt_insert, "is", $id_pegawai, $waktu_mesin_str);
                    if (mysqli_stmt_execute($stmt_insert)) $sukses_masuk_baru++;
                }
            }

            // --- LOGIKA 3: PROSES SCAN PULANG ---
            if (!empty($scan_pulang)) {
                $waktu_mesin = new DateTime($tanggal_db . ' ' . $scan_pulang);

                $sql_cek_app = "SELECT id_absensi, waktu_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Pulang'";
                $stmt_cek_app = mysqli_prepare($koneksi, $sql_cek_app);
                mysqli_stmt_bind_param($stmt_cek_app, "is", $id_pegawai, $tanggal_db);
                mysqli_stmt_execute($stmt_cek_app);
                $result_app = mysqli_stmt_get_result($stmt_cek_app);
                $data_app = $result_app->fetch_assoc();
                
                if ($data_app) { // Jika ada data dari aplikasi, bandingkan
                    $waktu_app = new DateTime($data_app['waktu_absensi']);
                    // Pilih waktu yang lebih akhir/sore (lebih baik)
                    if ($waktu_mesin > $waktu_app) {
                         $sql_update = "UPDATE tabel_absensi SET waktu_absensi = ?, catatan = 'Finger Mesin (Data Terbaik)' WHERE id_absensi = ?";
                        $stmt_update = mysqli_prepare($koneksi, $sql_update);
                        $waktu_mesin_str = $waktu_mesin->format('Y-m-d H:i:s');
                        mysqli_stmt_bind_param($stmt_update, "si", $waktu_mesin_str, $data_app['id_absensi']);
                        if(mysqli_stmt_execute($stmt_update)) $diupdate_pulang++;
                    }
                } else { // Jika tidak ada data, langsung insert
                    $sql_insert = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, catatan) VALUES (?, 'Pulang', ?, 'Finger Absensi Mesin Pulang')";
                    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
                    $waktu_mesin_str = $waktu_mesin->format('Y-m-d H:i:s');
                    mysqli_stmt_bind_param($stmt_insert, "is", $id_pegawai, $waktu_mesin_str);
                    if (mysqli_stmt_execute($stmt_insert)) $sukses_pulang_baru++;
                }
            }

        } else {
            $gagal_nip++;
        }
    }

    fclose($file_handle);
    // Buat URL redirect dengan parameter notifikasi yang baru
    $query_params = http_build_query([
        'sukses_masuk_baru' => $sukses_masuk_baru,
        'sukses_pulang_baru' => $sukses_pulang_baru,
        'diupdate_masuk' => $diupdate_masuk,
        'diupdate_pulang' => $diupdate_pulang,
        'gagal_nip' => $gagal_nip,
        'dilewati_dl' => $dilewati_dl
    ]);
    header("Location: /admin/impor-absensi?$query_params");
    exit();

} else {
    header("Location: /admin/impor-absensi?error=Tidak ada file yang diunggah.");
    exit();
}
?>