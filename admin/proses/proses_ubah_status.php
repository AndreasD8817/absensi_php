<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Keamanan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_pegawai = (int)$_GET['id'];
    $status_baru = $_GET['status'];

    // Validasi nilai status
    if ($status_baru != 'aktif' && $status_baru != 'non-aktif') {
        header("Location: /admin/manajemen-user?error=Status tidak valid.");
        exit();
    }

    // Mencegah superadmin menonaktifkan dirinya sendiri
    if ($id_pegawai == $_SESSION['id_pegawai']) {
        header("Location: /admin/manajemen-user?error=Anda tidak dapat mengubah status akun Anda sendiri.");
        exit();
    }

    $sql = "UPDATE tabel_pegawai SET status = ? WHERE id_pegawai = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pegawai);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /admin/manajemen-user?success=Status user berhasil diperbarui.");
    } else {
        header("Location: /admin/manajemen-user?error=Gagal memperbarui status user.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    header("Location: /admin/manajemen-user");
    exit();
}
?>
