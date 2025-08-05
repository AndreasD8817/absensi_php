<?php
session_start();
require_once '../../config/database.php';

// Security check
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];

    $sql = "DELETE FROM tabel_hari_libur WHERE tanggal = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../kelola_libur.php?success=Hari libur berhasil dihapus.");
    } else {
        header("Location: ../kelola_libur.php?error=Gagal menghapus hari libur.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Redirect jika file diakses langsung
    header("Location: ../kelola_libur.php");
    exit();
}
?>
