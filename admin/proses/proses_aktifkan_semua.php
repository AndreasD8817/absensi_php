<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php';

// Keamanan: Hanya Super Admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: /admin?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF Token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        header("Location: /admin/manajemen-user?error=Sesi tidak valid, coba lagi.");
        exit();
    }

    // Query untuk mengaktifkan semua pegawai dengan role 'pegawai'
    $sql = "UPDATE tabel_pegawai SET status = 'aktif' WHERE role = 'pegawai'";
    $stmt = mysqli_prepare($koneksi, $sql);

    if (mysqli_stmt_execute($stmt)) {
        $jumlah_terpengaruh = mysqli_stmt_affected_rows($stmt);

        // Catat Log Aktivitas
        $aktivitas = "Mengaktifkan semua ($jumlah_terpengaruh) pegawai.";
        catat_log($koneksi, $_SESSION['id_pegawai'], $_SESSION['role'], $aktivitas);

        header("Location: /admin/manajemen-user?success=$jumlah_terpengaruh pegawai berhasil diaktifkan.");
    } else {
        header("Location: /admin/manajemen-user?error=Gagal mengaktifkan pegawai.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
} else {
    header("Location: /admin/manajemen-user");
    exit();
}
?>