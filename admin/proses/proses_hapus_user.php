<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php'; // Panggil helper CSRF

if ($_SESSION['role'] != 'superadmin') {
    header("Location: /admin?error=Akses ditolak");

    exit();
}
// === VALIDASI CSRF TOKEN dari GET ===
if (!validate_csrf_token($_GET['csrf_token'])) {
    die('CSRF token validation failed.'); // Hentikan jika tidak valid
}

if (isset($_GET['id'])) {
    $id_pegawai = (int)$_GET['id'];

    // Jangan biarkan user menghapus dirinya sendiri
    if ($id_pegawai == $_SESSION['id_pegawai']) {
        header("Location: /admin/manajemen-user?error=Anda tidak dapat menghapus akun Anda sendiri.");
        exit();
    }

    $sql = "DELETE FROM tabel_pegawai WHERE id_pegawai = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pegawai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /admin/manajemen-user?success=User berhasil dihapus.");
    } else {
        header("Location: /admin/manajemen-user?error=Gagal menghapus user.");
    }
}
?>
