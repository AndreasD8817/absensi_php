<?php
// Selalu mulai sesi di setiap halaman admin
session_start();

// "Penjaga Gerbang"
// 1. Cek apakah sesi login ada
// 2. Cek apakah role user adalah 'admin' atau 'superadmin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    // Jika tidak, tendang ke halaman login
    header("Location: /login?error=Akses ditolak");
    exit();
}

// Panggil file koneksi database jika diperlukan di banyak tempat
require_once __DIR__ . '/../../config/database.php';
// PANGGIL CSRF HELPER
require_once __DIR__ . '/../../config/csrf_helper.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/assets/img/logo/favicon.png" type="image/png">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Aplikasi Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            padding-top: 70px; /* Memberi ruang untuk sticky navbar */
        }
        .card-menu {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .icon-lg {
            font-size: 3rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/admin">
        <img src="/assets/img/logo/favicon.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
        RekAbsen Panel
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-menu-button-wide-fill"></i> Menu Admin
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/admin/kelola-libur"><i class="bi bi-calendar-plus-fill me-2"></i>Input Hari Libur</a></li>
                    <li><a class="dropdown-item" href="/admin/laporan-absensi"><i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Laporan Absensi</a></li>
                    <li><a class="dropdown-item" href="/admin/edit-absensi"><i class="bi bi-pencil-square me-2"></i>Kelola Absensi</a></li>

                    <?php if ($_SESSION['role'] == 'superadmin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Menu Super Admin</h6></li>
                        <li><a class="dropdown-item" href="/admin/impor-absensi"><i class="bi bi-upload me-2"></i>Impor Absensi</a></li>
                        <li><a class="dropdown-item" href="/admin/impor-pegawai"><i class="bi bi-person-lines-fill me-2"></i>Impor Pegawai</a></li>
                        <li><a class="dropdown-item" href="/admin/manajemen-user"><i class="bi bi-people-fill me-2"></i>Manajemen User</a></li>
                        <li><a class="dropdown-item" href="/admin/pengaturan"><i class="bi bi-pin-map-fill me-2"></i>Pengaturan Jarak</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="/dashboard"><i class="bi bi-person-workspace"></i> Dashboard Pegawai</a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
  </div>
</nav>
<main class="container mt-4">