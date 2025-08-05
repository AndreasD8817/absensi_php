<?php
session_start();
require_once '../../config/database.php';

if ($_SESSION['role'] != 'superadmin') {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pegawai = (int)$_POST['id_pegawai'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $jabatan = trim($_POST['jabatan']);
    $role = $_POST['role'];

    if (empty($nama_lengkap) || empty($username) || empty($role)) {
        header("Location: ../edit_user.php?id=$id_pegawai&error=Field tidak boleh kosong.");
        exit();
    }

    // Jika password diisi, update password. Jika tidak, biarkan password lama.
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE tabel_pegawai SET nama_lengkap = ?, username = ?, password = ?, jabatan = ?, role = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $nama_lengkap, $username, $hashed_password, $jabatan, $role, $id_pegawai);
    } else {
        $sql = "UPDATE tabel_pegawai SET nama_lengkap = ?, username = ?, jabatan = ?, role = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $nama_lengkap, $username, $jabatan, $role, $id_pegawai);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../manajemen_user.php?success=Data user berhasil diperbarui.");
    } else {
        header("Location: ../edit_user.php?id=$id_pegawai&error=Gagal memperbarui data.");
    }
}
?>
