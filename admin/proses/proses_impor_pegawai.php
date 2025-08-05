<?php
session_start();
require_once '../../config/database.php';

// Keamanan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: ../../index.php?error=Akses ditolak");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_pegawai'])) {
    
    if ($_FILES['file_pegawai']['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../impor_pegawai.php?error=Terjadi kesalahan saat mengunggah file.");
        exit();
    }

    $file_tmp_path = $_FILES['file_pegawai']['tmp_name'];

    $file_handle = fopen($file_tmp_path, 'r');
    if (!$file_handle) {
        header("Location: ../impor_pegawai.php?error=Gagal membuka file yang diunggah.");
        exit();
    }

    $ditambah = 0;
    $diupdate = 0;
    $baris_ke = 0;

    // Baca file baris per baris
    while (($line = fgets($file_handle)) !== FALSE) {
        $baris_ke++;
        // Lewati baris header dan baris kosong
        if ($baris_ke <= 2) {
            continue;
        }

        $data = explode(';', $line);
        if (count($data) < 3) continue;

        $nip = trim($data[0]);
        $nama = trim($data[1]);
        $jabatan = trim($data[2]);

        if (empty($nip) || empty($nama)) continue;

        $role = (strtolower($jabatan) == 'admin') ? 'admin' : 'pegawai';

        // Cek apakah NIP sudah ada di database
        $sql_cek_nip = "SELECT id_pegawai FROM tabel_pegawai WHERE nip = ?";
        $stmt_cek_nip = mysqli_prepare($koneksi, $sql_cek_nip);
        mysqli_stmt_bind_param($stmt_cek_nip, "s", $nip);
        mysqli_stmt_execute($stmt_cek_nip);
        $result_cek_nip = mysqli_stmt_get_result($stmt_cek_nip);

        if ($row = mysqli_fetch_assoc($result_cek_nip)) {
            // NIP DITEMUKAN -> UPDATE DATA
            $id_pegawai = $row['id_pegawai'];
            $sql_update = "UPDATE tabel_pegawai SET nama_lengkap = ?, jabatan = ?, role = ? WHERE id_pegawai = ?";
            $stmt_update = mysqli_prepare($koneksi, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sssi", $nama, $jabatan, $role, $id_pegawai);
            if (mysqli_stmt_execute($stmt_update)) {
                $diupdate++;
            }
        } else {
            // NIP TIDAK DITEMUKAN -> INSERT DATA BARU
            $password_default = '000000';
            $hashed_password = password_hash($password_default, PASSWORD_DEFAULT);
            
            // --- LOGIKA BARU UNTUK USERNAME UNIK ---
            $username_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
            $username = $username_base;

            // Cek apakah username dasar sudah ada
            $sql_cek_user = "SELECT id_pegawai FROM tabel_pegawai WHERE username = ?";
            $stmt_cek_user = mysqli_prepare($koneksi, $sql_cek_user);
            mysqli_stmt_bind_param($stmt_cek_user, "s", $username);
            mysqli_stmt_execute($stmt_cek_user);
            
            // Jika sudah ada, tambahkan NIP agar unik
            if (mysqli_stmt_get_result($stmt_cek_user)->num_rows > 0) {
                $username = $username_base . $nip;
            }
            // --- AKHIR LOGIKA BARU ---

            $sql_insert = "INSERT INTO tabel_pegawai (nip, nama_lengkap, username, password, jabatan, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $nip, $nama, $username, $hashed_password, $jabatan, $role);
            if (mysqli_stmt_execute($stmt_insert)) {
                $ditambah++;
            }
        }
    }

    fclose($file_handle);
    header("Location: ../impor_pegawai.php?ditambah=$ditambah&diupdate=$diupdate");
    exit();

} else {
    header("Location: ../impor_pegawai.php?error=Tidak ada file yang diunggah.");
    exit();
}
?>
