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

    $id_admin_login = $_SESSION['id_pegawai'];

    // --- PERUBAHAN DI SINI: Query hanya menargetkan role 'pegawai' ---
    $sql = "UPDATE tabel_pegawai SET status = 'non-aktif' WHERE id_pegawai != ? AND role = 'pegawai'";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_admin_login);

    if (mysqli_stmt_execute($stmt)) {
        $jumlah_terpengaruh = mysqli_stmt_affected_rows($stmt);

        // Catat Log Aktivitas
        $aktivitas = "Menonaktifkan semua ($jumlah_terpengaruh) pegawai.";
        catat_log($koneksi, $id_admin_login, $_SESSION['role'], $aktivitas);

        header("Location: /admin/manajemen-user?success=$jumlah_terpengaruh pegawai berhasil dinonaktifkan.");
    } else {
        header("Location: /admin/manajemen-user?error=Gagal menonaktifkan pegawai.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
} else {
    header("Location: /admin/manajemen-user");
    exit();
}
?>