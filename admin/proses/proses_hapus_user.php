<?php
session_start();
require_once '../../config/database.php';

if ($_SESSION['role'] != 'superadmin') {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if (isset($_GET['id'])) {
    $id_pegawai = (int)$_GET['id'];

    // Jangan biarkan user menghapus dirinya sendiri
    if ($id_pegawai == $_SESSION['id_pegawai']) {
        header("Location: ../manajemen_user.php?error=Anda tidak dapat menghapus akun Anda sendiri.");
        exit();
    }

    $sql = "DELETE FROM tabel_pegawai WHERE id_pegawai = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pegawai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../manajemen_user.php?success=User berhasil dihapus.");
    } else {
        header("Location: ../manajemen_user.php?error=Gagal menghapus user.");
    }
}
?>
