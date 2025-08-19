<?php
/**
 * File: config/log_helper.php
 * Deskripsi: Berisi fungsi untuk mencatat aktivitas pengguna ke database.
 */

/**
 * Fungsi untuk mencatat aktivitas ke dalam tabel log_aktivitas.
 *
 * @param mysqli $koneksi Objek koneksi database yang aktif.
 * @param int $id_pegawai ID dari pengguna yang melakukan aktivitas.
 * @param string $role Role pengguna (pegawai, admin, superadmin).
 * @param string $aktivitas Deskripsi singkat dari aktivitas yang dilakukan.
 * @return void
 */
function catat_log($koneksi, $id_pegawai, $role, $aktivitas) {
    // Query SQL untuk memasukkan data log baru
    $sql = "INSERT INTO log_aktivitas (id_pegawai, level_akses, aktivitas) VALUES (?, ?, ?)";
    
    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($koneksi, $sql);
    
    // Pastikan statement berhasil dibuat sebelum melanjutkan
    if ($stmt) {
        // Bind parameter ke query
        mysqli_stmt_bind_param($stmt, "iss", $id_pegawai, $role, $aktivitas);
        
        // Eksekusi query
        mysqli_stmt_execute($stmt);
        
        // Tutup statement
        mysqli_stmt_close($stmt);
    }
}
?>