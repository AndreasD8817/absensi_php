<?php
session_start();
// Menggunakan path yang benar sesuai struktur file Anda
require_once __DIR__ . '/../../config/database.php';


// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /absensi_php/admin/manajemen-user?error=Akses ditolak.");
    exit();
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil semua data dari form
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $jabatan = trim($_POST['jabatan']);
    $role = $_POST['role'];
    
    // --- LOGIKA BARU ---
    // Jika field radius diisi, ambil nilainya sebagai angka. Jika kosong, simpan sebagai NULL.
    $radius_absensi = !empty(trim($_POST['radius_absensi'])) ? (int)trim($_POST['radius_absensi']) : NULL;

    // Validasi dasar
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        header("Location: /absensi_php/admin/tambah-user?error=Nama, username, password, dan role wajib diisi.");
        exit();
    }
    
    // Cek dulu apakah username sudah ada (mengikuti logika dari file lama Anda)
    $sql_cek = "SELECT id_pegawai FROM tabel_pegawai WHERE username = ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $username);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);
    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: /absensi_php/admin/tambah-user?error=Username '" . htmlspecialchars($username) . "' sudah digunakan.");
        exit();
    }
    mysqli_stmt_close($stmt_cek);

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // --- QUERY DIPERBARUI ---
    // Tambahkan kolom radius_absensi ke dalam query INSERT
    $sql = "INSERT INTO tabel_pegawai (nama_lengkap, username, password, jabatan, role, radius_absensi) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    
    // Bind parameter. Tipe data: s=string, i=integer.
    // Variabel $radius_absensi akan berisi angka atau nilai NULL.
    mysqli_stmt_bind_param($stmt, "sssssi", $nama_lengkap, $username, $hashed_password, $jabatan, $role, $radius_absensi);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /absensi_php/admin/manajemen-user?success=User baru berhasil ditambahkan.");
    } else {
        header("Location: /absensi_php/admin/tambah-user?error=Gagal menambahkan user. Error: " . mysqli_error($koneksi));
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Jika bukan POST, redirect
    header("Location: /absensi_php/admin/tambah-user");
    exit();
}
?>
