<?php
// Mendefinisikan base path proyek untuk semua link
define('BASE_PATH', '');
// Mendapatkan path URL yang diminta, tanpa query string
$request_uri = strtok($_SERVER['REQUEST_URI'], '?');

// Menghapus sub-direktori jika aplikasi Anda tidak berada di root domain
// Contoh: jika URL adalah localhost/dashboard, kita ingin mendapatkan /dashboard
$base_path = ''; // <-- SESUAIKAN JIKA PERLU
$request = str_replace($base_path, '', $request_uri);
$request = trim($request, '/');

// Mengatur routing berdasarkan permintaan
switch ($request) {
    // --- Routing Utama ---
    case '':
    case '/':
        require 'login.php';
        break;
    case 'login':
        require 'login.php';
        break;
    case 'dashboard':
        require 'dashboard.php';
        break;
    case 'riwayat-absensi':
        require 'riwayat_absensi.php';
        break;

    // --- Routing untuk Proses Form ---
    case 'auth/proses-login':
        require 'auth/proses_login.php';
        break;
    case 'auth/logout':
        require 'auth/logout.php';
        break;
    case 'proses-absensi':
        require 'proses_absensi.php';
        break;
    case 'proses-dinas-luar':
        require 'proses_dinas_luar.php';
        break;

    // --- Routing untuk Halaman Admin ---
    case 'admin':
    case 'admin/':
        require 'admin/index.php';
        break;
    case 'admin/manajemen-user':
        require 'admin/manajemen_user.php';
        break;
    case 'admin/tambah-user':
        require 'admin/tambah_user.php';
        break;
    case 'admin/edit-user': // Kita butuh ID, jadi ini akan ditangani secara khusus
        if (isset($_GET['id'])) {
            require 'admin/edit_user.php';
        } else {
            http_response_code(404);
            require '404.php'; // Halaman Error
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
    case 'admin/laporan-absensi':
        require 'admin/laporan_absensi.php';
        break;
    case 'admin/pengaturan':
        require 'admin/pengaturan.php';
        break;
        
    // --- Routing untuk Proses di Folder Admin ---
    // (Tambahkan semua file dari folder admin/proses/ di sini)
    case 'admin/proses/proses-edit-user':
        require 'admin/proses/proses_edit_user.php';
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
    // ... Tambahkan file proses lainnya di sini

    default:
        // Jika tidak ada route yang cocok, tampilkan halaman 404
        http_response_code(404);
        require '404.php'; // Anda perlu membuat file ini
        break;
}