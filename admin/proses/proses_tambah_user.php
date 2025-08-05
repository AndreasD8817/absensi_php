<?php
session_start();
require_once '../../config/database.php';

if ($_SESSION['role'] != 'superadmin') {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $jabatan = trim($_POST['jabatan']);
    $role = $_POST['role'];

    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        header("Location: ../tambah_user.php?error=Semua field wajib diisi.");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Cek username duplikat
    $sql_cek = "SELECT id_pegawai FROM tabel_pegawai WHERE username = ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $username);
    mysqli_stmt_execute($stmt_cek);
    if (mysqli_stmt_get_result($stmt_cek)->num_rows > 0) {
        header("Location: ../tambah_user.php?error=Username sudah digunakan.");
        exit();
    }

    $sql = "INSERT INTO tabel_pegawai (nama_lengkap, username, password, jabatan, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $nama_lengkap, $username, $hashed_password, $jabatan, $role);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../manajemen_user.php?success=User baru berhasil ditambahkan.");
    } else {
        header("Location: ../tambah_user.php?error=Gagal menambahkan user.");
    }
}
?>
