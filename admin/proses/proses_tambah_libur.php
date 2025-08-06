<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Security check
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /admin?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $keterangan = trim($_POST['keterangan']);

    // Validasi sederhana
    if (empty($tanggal) || empty($keterangan)) {
        header("Location: /admin/kelola-libur?error=Tanggal dan keterangan tidak boleh kosong.");
        exit();
    }

    // Cek apakah tanggal sudah ada (karena tanggal adalah PRIMARY KEY)
    $sql_cek = "SELECT tanggal FROM tabel_hari_libur WHERE tanggal = ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $tanggal);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: /admin/kelola-libur?error=Tanggal libur sudah ada di database.");
        exit();
    }
    mysqli_stmt_close($stmt_cek);

    // Insert data baru
    $sql = "INSERT INTO tabel_hari_libur (tanggal, keterangan) VALUES (?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $tanggal, $keterangan);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /admin/kelola-libur?success=Hari libur berhasil ditambahkan.");
    } else {
        header("Location: /admin/kelola-libur?error=Gagal menambahkan hari libur.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Redirect jika file diakses langsung
    header("Location: /admin/kelola-libur");
    exit();
}
?>
