<?php
session_start();
require_once '../../config/database.php';

// Keamanan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $pengaturan_baru = [
        'lokasi_lat' => $_POST['lokasi_lat'],
        'lokasi_lon' => $_POST['lokasi_lon'],
        'radius_meter' => $_POST['radius_meter']
    ];

    $error = false;

    // Loop untuk mengupdate setiap pengaturan
    foreach ($pengaturan_baru as $nama => $nilai) {
        // Validasi sederhana
        if (!is_numeric($nilai)) {
            header("Location: ../pengaturan.php?error=Nilai untuk " . htmlspecialchars($nama) . " harus berupa angka.");
            exit();
        }

        $sql = "UPDATE tabel_pengaturan SET nilai_pengaturan = ? WHERE nama_pengaturan = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $nilai, $nama);
        
        if (!mysqli_stmt_execute($stmt)) {
            $error = true;
        }
    }

    if ($error) {
        header("Location: ../pengaturan.php?error=Gagal memperbarui satu atau lebih pengaturan.");
    } else {
        header("Location: ../pengaturan.php?success=Pengaturan berhasil diperbarui.");
    }
    exit();

} else {
    header("Location: ../pengaturan.php");
    exit();
}
?>
