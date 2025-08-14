<?php

/**
 * ----------------------------------------------------
 * PENGATURAN MODE MAINTENANCE
 * ----------------------------------------------------
 * Ubah menjadi 'true' untuk mengaktifkan mode maintenance,
 * atau 'false' untuk menonaktifkan dan menjalankan situs secara normal.
 */
$maintenance_mode = false;

// ===================================================================

// Jika mode maintenance aktif, tampilkan halaman maintenance dan hentikan skrip.
if ($maintenance_mode) {
    require 'app/maintenance.php';
    exit();
}

// Jika mode maintenance TIDAK aktif, kode di bawah ini akan dijalankan.

// Mendapatkan path URL yang diminta, tanpa query string

// Mendapatkan path URL yang diminta, tanpa query string
$request_uri = strtok($_SERVER['REQUEST_URI'], '?');
$request = trim($request_uri, '/');

// Mengatur routing berdasarkan permintaan
switch ($request) {
    // --- Routing Utama ---
    
    // KASUS 1: Ketika pengguna mengakses halaman root (misal: rekabsen.dprdsby.id)
    // Ini akan memuat halaman login sebagai halaman utama.
    case '':
        require 'app/login.php';
        break;

    // KASUS 2: Ketika pengguna secara eksplisit mengetik /login
    case 'login':
        require 'app/login.php';
        break;
        
    case 'dashboard':
        require 'app/dashboard.php';
        break;
    case 'proses-profil':
        require 'public/proses_profil.php';
        break;
    case 'riwayat-absensi':
        require 'app/riwayat_absensi.php';
        break;

    // --- Routing untuk Proses Form ---
    case 'auth/proses-login':
        require 'auth/proses_login.php';
        break;
    case 'auth/logout':
        require 'auth/logout.php';
        break;
    case 'proses-absensi':
        require 'public/proses_absensi.php';
        break;
    case 'proses-dinas-luar':
        require 'public/proses_dinas_luar.php';
        break;

    // --- Routing untuk Halaman Admin ---
    case 'admin':
        require 'admin/index.php';
        break;
    case 'admin/manajemen-user':
        require 'admin/manajemen_user.php';
        break;
    case 'admin/tambah-user':
        require 'admin/tambah_user.php';
        break;
    case 'admin/edit-user': 
        if (isset($_GET['id'])) {
            require 'admin/edit_user.php';
        } else {
            http_response_code(404);
            require 'app/404.php';
        }
        break;
    case 'admin/impor-pegawai':
        require 'admin/impor_pegawai.php';
        break;
    case 'admin/impor-absensi':
        require 'admin/impor_absensi.php';
        break;
    case 'admin/kelola-libur':
        require 'admin/kelola_libur.php';
        break;
    case 'admin/edit-absensi':
        require 'admin/edit_absensi.php';
        break;
    case 'admin/laporan-absensi':
        require 'admin/laporan_absensi.php';
        break;
    case 'admin/pengaturan':
        require 'admin/pengaturan.php';
        break;
        
    // --- Routing untuk Proses di Folder Admin ---
    case 'admin/proses/proses-edit-user':
        require 'admin/proses/proses_edit_user.php';
        break;
    // TAMBAHKAN CASE BARU DI SINI
    case 'admin/proses/proses-edit-absensi':
        require 'admin/proses/proses_edit_absensi.php';
        break;
    case 'admin/proses/proses-hapus-user':
        require 'admin/proses/proses_hapus_user.php';
        break;
    case 'admin/proses/impor-pegawai':
        require 'admin/proses/proses_impor_pegawai.php';
        break;
    case 'admin/proses/impor-absensi': 
        require 'admin/proses/proses_impor.php';
        break;
    case 'admin/proses/tambah-libur':
        require 'admin/proses/proses_tambah_libur.php';
        break;
    case 'admin/proses/hapus-libur':
        require 'admin/proses/proses_hapus_libur.php';
        break;
    case 'admin/proses/ubah-status':
        require 'admin/proses/proses_ubah_status.php';
        break;
    case 'admin/proses/proses-pengaturan':
        require 'admin/proses/proses_pengaturan.php';
        break;
    case 'admin/proses/proses-tambah-user':
        require 'admin/proses/proses_tambah_user.php';
        break;

    default:
        // Jika tidak ada route yang cocok, tampilkan halaman 404
        http_response_code(404);
        require 'app/404.php';
}