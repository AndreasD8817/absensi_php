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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
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
$username_lama = $user['username']; // Simpan username lama untuk log

// --- LOGIKA UPDATE DAN PENCATATAN LOG YANG DIPERBARUI ---

$perubahan_username = ($username_baru !== $username_lama);
$perubahan_password = false;

// 1. Validasi Perubahan Username
if ($perubahan_username) {
    $sql_cek_username = "SELECT id_pegawai FROM tabel_pegawai WHERE username = ? AND id_pegawai != ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek_username);
    mysqli_stmt_bind_param($stmt_cek, "si", $username_baru, $id_pegawai);
    mysqli_stmt_execute($stmt_cek);
    if (mysqli_stmt_get_result($stmt_cek)->num_rows > 0) {
        header("Location: /dashboard?error=Username '" . htmlspecialchars($username_baru) . "' sudah digunakan.");
        exit();
    }
}

// 2. Validasi Perubahan Password
if (!empty($password_lama) || !empty($password_baru) || !empty($konfirmasi_password)) {
    if (!password_verify($password_lama, $user['password'])) {
        header("Location: /dashboard?error=Password lama yang Anda masukkan salah.");
        exit();
    }
    if (empty($password_baru)) {
        header("Location: /dashboard?error=Password baru tidak boleh kosong.");
        exit();
    }
    if ($password_baru !== $konfirmasi_password) {
        header("Location: /dashboard?error=Konfirmasi password baru tidak cocok.");
        exit();
    }
    $perubahan_password = true;
}

// 3. Bangun Query dan Pesan Log berdasarkan perubahan
$query_dijalankan = false;

if ($perubahan_username && $perubahan_password) {
    // KASUS 1: Username DAN Password diubah
    $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    $sql_update = "UPDATE tabel_pegawai SET username = ?, password = ? WHERE id_pegawai = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $username_baru, $hashed_password_baru, $id_pegawai);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $aktivitas_log = "Mengubah profil diri: username diubah dari '$username_lama' menjadi '$username_baru' dan password juga diperbarui.";
        catat_log($koneksi, $id_pegawai, $_SESSION['role'], $aktivitas_log);
        $query_dijalankan = true;
    }

} elseif ($perubahan_username) {
    // KASUS 2: HANYA Username yang diubah
    $sql_update = "UPDATE tabel_pegawai SET username = ? WHERE id_pegawai = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $username_baru, $id_pegawai);

    if (mysqli_stmt_execute($stmt_update)) {
        $aktivitas_log = "Mengubah profil diri: username diubah dari '$username_lama' menjadi '$username_baru'.";
        catat_log($koneksi, $id_pegawai, $_SESSION['role'], $aktivitas_log);
        $query_dijalankan = true;
    }

} elseif ($perubahan_password) {
    // KASUS 3: HANYA Password yang diubah
    $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    $sql_update = "UPDATE tabel_pegawai SET password = ? WHERE id_pegawai = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $hashed_password_baru, $id_pegawai);

    if (mysqli_stmt_execute($stmt_update)) {
        $aktivitas_log = "Mengubah profil diri: password telah diperbarui.";
        catat_log($koneksi, $id_pegawai, $_SESSION['role'], $aktivitas_log);
        $query_dijalankan = true;
    }
}

// 4. Handle Redirect setelah semua proses selesai
if ($query_dijalankan) {
    $_SESSION['username'] = $username_baru; // Update sesi dengan username baru
    header("Location: /dashboard?sukses=Data akun berhasil diperbarui.");
} elseif (!$perubahan_username && !$perubahan_password) {
    // Tidak ada perubahan apa pun
    header("Location: /dashboard?error=Tidak ada perubahan yang disimpan.");
} else {
    // Gagal eksekusi query
    header("Location: /dashboard?error=Gagal memperbarui data. Silakan coba lagi.");
}
exit();
?>