<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php';

// Keamanan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /admin?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF Token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
    
    // Ambil semua data dari form, termasuk data penggajian
    $pengaturan_baru = [
        'lokasi_lat'   => $_POST['lokasi_lat'],
        'lokasi_lon'   => $_POST['lokasi_lon'],
        'radius_meter' => $_POST['radius_meter'],
        'gaji_harian'  => $_POST['gaji_harian'],
        'potongan_tetap' => $_POST['potongan_tetap']
    ];

    $error = false;
    $log_perubahan = [];

    // Loop untuk mengupdate setiap pengaturan
    foreach ($pengaturan_baru as $nama => $nilai) {
        // Validasi sederhana
        if (!is_numeric($nilai)) {
            header("Location: /admin/pengaturan?error=Nilai untuk " . htmlspecialchars($nama) . " harus berupa angka.");
            exit();
        }

        // Gunakan INSERT ... ON DUPLICATE KEY UPDATE untuk menyederhanakan
        $sql = "INSERT INTO tabel_pengaturan (nama_pengaturan, nilai_pengaturan) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE nilai_pengaturan = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $nama, $nilai, $nilai);
        
        if (!mysqli_stmt_execute($stmt)) {
            $error = true;
        } else {
            // Kumpulkan perubahan untuk log
            $log_perubahan[] = "$nama menjadi '$nilai'";
        }
    }

    if ($error) {
        header("Location: /admin/pengaturan?error=Gagal memperbarui satu atau lebih pengaturan.");
    } else {
        // Catat log aktivitas jika ada perubahan
        if (!empty($log_perubahan)) {
            $aktivitas = "Memperbarui pengaturan sistem: " . implode(', ', $log_perubahan) . ".";
            catat_log($koneksi, $_SESSION['id_pegawai'], $_SESSION['role'], $aktivitas);
        }
        header("Location: /admin/pengaturan?success=Pengaturan berhasil diperbarui.");
    }
    exit();

} else {
    header("Location: /admin/pengaturan");
    exit();
}
