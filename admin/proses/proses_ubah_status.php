<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php'; // Panggil helper CSRF

// Keamanan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak");
    exit();
}
// === VALIDASI CSRF TOKEN dari GET ===
if (!validate_csrf_token($_POST['csrf_token'])) {
    die('CSRF token validation failed.'); // Hentikan jika tidak valid
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id_pegawai = (int)$_POST['id'];
    $status_baru = $_POST['status'];

    // Validasi nilai status
    if ($status_baru != 'aktif' && $status_baru != 'non-aktif') {
        header("Location: /admin/manajemen-user?error=Status tidak valid.");
        exit();
    }

    // Mencegah superadmin menonaktifkan dirinya sendiri
    if ($id_pegawai == $_SESSION['id_pegawai']) {
        header("Location: /admin/manajemen-user?error=Anda tidak dapat mengubah status akun Anda sendiri.");
        exit();
    }

    $sql = "UPDATE tabel_pegawai SET status = ? WHERE id_pegawai = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status_baru, $id_pegawai);

    if (mysqli_stmt_execute($stmt)) {
         // === TAMBAHKAN LOG DI SINI ===
        $aktivitas = "Mengubah status user ID: $id_pegawai menjadi '$status_baru'.";
        catat_log($koneksi, $_SESSION['id_pegawai'], $_SESSION['role'], $aktivitas);
        // =============================
        header("Location: /admin/manajemen-user?success=Status user berhasil diperbarui.");
    } else {
        header("Location: /admin/manajemen-user?error=Gagal memperbarui status user.");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    header("Location: /admin/manajemen-user");
    exit();
}
?>
