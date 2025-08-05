<?php
// Mulai sesi
session_start();

// Panggil file koneksi database
require_once '../config/database.php';

// Cek apakah data dikirim dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($username) || empty($password)) {
        header("Location: ../index.php?error=Username dan Password tidak boleh kosong");
        exit();
    }

    // Ambil semua data yang diperlukan, termasuk 'role' dan 'status'
    $sql = "SELECT id_pegawai, nama_lengkap, username, password, role, status FROM tabel_pegawai WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    
    if ($stmt === false) {
        header("Location: ../index.php?error=Terjadi kesalahan pada server");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Cek apakah username ditemukan
    if ($user = mysqli_fetch_assoc($result)) {
        
        // PENTING: Cek status akun SEBELUM verifikasi password
        if ($user['status'] == 'non-aktif') {
            header("Location: ../index.php?error=Akun Anda telah dinonaktifkan. Silakan hubungi Super Koor Arvin.");
            exit();
        }

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Password benar, buat semua sesi yang diperlukan
            $_SESSION['id_pegawai'] = $user['id_pegawai'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect ke halaman dashboard utama
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Password salah
            header("Location: ../index.php?error=Password salah");
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("Location: ../index.php?error=Username tidak ditemukan");
        exit();
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Jika file diakses langsung, redirect ke halaman login
    header("Location: ../index.php");
    exit();
}
