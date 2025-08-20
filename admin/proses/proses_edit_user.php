<?php
session_start();
// Menggunakan path yang benar sesuai struktur file Anda
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php'; // Panggil helper CSRF

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /admin/manajemen-user?error=Akses ditolak.");
    exit();
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === VALIDASI CSRF TOKEN ===
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.'); // Hentikan jika tidak valid
    }
    // Ambil semua data dari form
    $id_pegawai = (int)$_POST['id_pegawai'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $jabatan = trim($_POST['jabatan']);
    $role = $_POST['role'];

    // --- LOGIKA BARU ---
    // Jika field radius diisi, ambil nilainya sebagai angka. Jika kosong, simpan sebagai NULL.
    $radius_absensi = !empty(trim($_POST['radius_absensi'])) ? (int)trim($_POST['radius_absensi']) : NULL;

    // Validasi dasar
    if (empty($nama_lengkap) || empty($username) || empty($role) || empty($id_pegawai)) {
        header("Location: /admin/edit-user?id=$id_pegawai&error=Nama, username, dan role wajib diisi.");
        exit();
    }
    
    // Logika untuk update password (mengikuti file lama Anda):
    // Jika field password TIDAK kosong, maka kita update passwordnya.
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // --- QUERY DIPERBARUI ---
        $sql = "UPDATE tabel_pegawai SET nama_lengkap = ?, username = ?, password = ?, jabatan = ?, role = ?, radius_absensi = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        // Tipe data: s=string, i=integer
        mysqli_stmt_bind_param($stmt, "sssssii", $nama_lengkap, $username, $hashed_password, $jabatan, $role, $radius_absensi, $id_pegawai);
    } else {
        // Jika field password KOSONG, kita update semua kecuali password.
        // --- QUERY DIPERBARUI ---
        $sql = "UPDATE tabel_pegawai SET nama_lengkap = ?, username = ?, jabatan = ?, role = ?, radius_absensi = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        // Tipe data: s=string, i=integer
        mysqli_stmt_bind_param($stmt, "ssssii", $nama_lengkap, $username, $jabatan, $role, $radius_absensi, $id_pegawai);
    }

    if (mysqli_stmt_execute($stmt)) {
        // === CATAT LOG AKTIVITAS ===
        $aktivitas = "Berhasil memperbarui data user: '$nama_lengkap' (ID: $id_pegawai).";
        catat_log($koneksi, $_SESSION['id_pegawai'], $_SESSION['role'], $aktivitas);
        // ============================
        header("Location: /admin/manajemen-user?success=Data user berhasil diperbarui.");
    } else {
        // Cek jika error karena username duplikat
        if (mysqli_errno($koneksi) == 1062) {
             header("Location: /admin/edit-user?id=$id_pegawai&error=Username '" . htmlspecialchars($username) . "' sudah digunakan.");
        } else {
            header("Location: /admin/edit-user?id=$id_pegawai&error=Gagal memperbarui data user. Error: " . mysqli_error($koneksi));
        }
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Jika bukan POST, redirect
    header("Location: /admin/manajemen-user");
    exit();
}
?>
