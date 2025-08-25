<?php
// Mulai sesi
session_start();

// Panggil file koneksi database
require_once __DIR__ . '/../config/database.php';
// PANGGIL CSRF HELPER BARU
require_once __DIR__ . '/../config/csrf_helper.php';

// Cek apakah data dikirim dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($username) || empty($password)) {
        header("Location: /login?error=Username dan Password tidak boleh kosong");
        exit();
    }

    // Ambil semua data yang diperlukan, termasuk 'role' dan 'status'
    $sql = "SELECT id_pegawai, nama_lengkap, username, password, role, status FROM tabel_pegawai WHERE username = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    
    if ($stmt === false) {
        header("Location: /login?error=Terjadi kesalahan pada server");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Cek apakah username ditemukan
    if ($user = mysqli_fetch_assoc($result)) {
        
        // PENTING: Cek status akun SEBELUM verifikasi password
        if ($user['status'] == 'non-aktif') {
            header("Location: /login?error=Akun Anda telah dinonaktifkan Sementara.");
            exit();
        }

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Password benar, buat semua sesi yang diperlukan
            $_SESSION['id_pegawai'] = $user['id_pegawai'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // === BARU: BUAT CSRF TOKEN SAAT LOGIN BERHASIL ===
            generate_csrf_token();

            // === TAMBAHKAN LOG DI SINI ===
            catat_log($koneksi, $user['id_pegawai'], $user['role'], 'Login berhasil ke sistem.');
            // =============================

            // Redirect ke halaman dashboard utama
            header("Location: /dashboard");
            exit();
        } else {
            // Password salah
            header("Location: /login?error=Password salah");
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("Location: /login?error=Username tidak ditemukan");
        exit();
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Jika file diakses langsung, redirect ke halaman login
    header("Location: /login");
    exit();
}
