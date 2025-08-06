<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Security check
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /admin?error=Akses ditolak");

    exit();
}

if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];

    $sql = "DELETE FROM tabel_hari_libur WHERE tanggal = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /admin/kelola-libur?success=Hari libur berhasil dihapus.");
    } else {
        header("Location: /admin/kelola-libur?error=Gagal menghapus hari libur.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Redirect jika file diakses langsung
    header("Location: /admin/kelola-libur");
    exit();
}
?>
