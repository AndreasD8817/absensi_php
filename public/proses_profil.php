<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf_helper.php'; // Panggil helper

// Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: /login");
    exit();
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Gunakan '===' atau '=='
    // === VALIDASI CSRF TOKEN DI SINI ===
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        // Redirect dengan pesan error yang lebih ramah pengguna
        header("Location: /dashboard?error=Sesi tidak valid. Silakan coba lagi.");
        exit();
    }


// Ambil data dari form
$id_pegawai = $_SESSION['id_pegawai'];
$username_baru = trim($_POST['username']);
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$konfirmasi_password = $_POST['konfirmasi_password'];

} else {
    // Jika bukan POST, langsung redirect
    header("Location: /dashboard");
    exit();
}

// Ambil data pengguna saat ini dari database untuk verifikasi
$sql_current_user = "SELECT username, password FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt = mysqli_prepare($koneksi, $sql_current_user);
mysqli_stmt_bind_param($stmt, "i", $id_pegawai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// --- LOGIKA UPDATE ---

// 1. Cek apakah username diubah dan apakah sudah ada yang pakai
if ($username_baru !== $user['username']) {
    $sql_cek_username = "SELECT id_pegawai FROM tabel_pegawai WHERE username = ? AND id_pegawai != ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek_username);
    mysqli_stmt_bind_param($stmt_cek, "si", $username_baru, $id_pegawai);
    mysqli_stmt_execute($stmt_cek);
    if (mysqli_stmt_get_result($stmt_cek)->num_rows > 0) {
        header("Location: /dashboard?error=Username '" . htmlspecialchars($username_baru) . "' sudah digunakan.");
        exit();
    }
}

// 2. Logika untuk mengubah password (jika diisi)
$update_password = false;
if (!empty($password_lama) || !empty($password_baru)) {
    // Verifikasi password lama
    if (!password_verify($password_lama, $user['password'])) {
        header("Location: /dashboard?error=Password lama yang Anda masukkan salah.");
        exit();
    }
    // Validasi password baru
    if (empty($password_baru)) {
        header("Location: /dashboard?error=Password baru tidak boleh kosong.");
        exit();
    }
    if ($password_baru !== $konfirmasi_password) {
        header("Location: /dashboard?error=Konfirmasi password baru tidak cocok.");
        exit();
    }
    $update_password = true;
}

// 3. Bangun dan eksekusi query UPDATE
if ($update_password) {
    // Jika password diubah
    $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    $sql_update = "UPDATE tabel_pegawai SET username = ?, password = ? WHERE id_pegawai = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $username_baru, $hashed_password_baru, $id_pegawai);
} else {
    // Jika hanya username yang diubah
    $sql_update = "UPDATE tabel_pegawai SET username = ? WHERE id_pegawai = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $username_baru, $id_pegawai);
}

// Eksekusi query
if (mysqli_stmt_execute($stmt_update)) {
    // Update sesi jika username berubah
    $_SESSION['username'] = $username_baru;
    header("Location: /dashboard?sukses=Data akun berhasil diperbarui.");
} else {
    header("Location: /dashboard?error=Gagal memperbarui data. Silakan coba lagi.");
}
exit();