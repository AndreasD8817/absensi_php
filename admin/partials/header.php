<?php
// Selalu mulai sesi di setiap halaman admin
session_start();

// "Penjaga Gerbang"
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /login?error=Akses ditolak");
    exit();
}

// Logika Auto Logout (tetap sama)
$idle_timeout = 1800; // 30 menit
if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time > $idle_timeout) {
        session_unset();
        session_destroy();
        header("Location: /login?error=Sesi Anda telah berakhir karena tidak ada aktivitas.");
        exit();
    }
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../../config/database.php';
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
    
    <link rel="stylesheet" href="/assets/css/sidebar.css">
</head>
<body>

<button class="btn btn-primary sidebar-toggle" type="button" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>


<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/assets/img/logo/favicon.png" alt="Logo">
        <h5>RekAbsen Panel</h5>
    </div>

    <div class="sidebar-nav-wrapper">
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/admin">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard Admin</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/laporan-absensi">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Laporan Absensi</span>
                </a>
            </li>
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin/laporan-penggajian">
                    <i class="bi bi-cash-stack"></i>
                    <span>Laporan Penggajian</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin/edit-absensi">
                    <i class="bi bi-pencil-square"></i>
                    <span>Kelola Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/kelola-libur">
                    <i class="bi bi-calendar-plus-fill"></i>
                    <span>Input Hari Libur</span>
                </a>
            </li>

            <?php if ($_SESSION['role'] == 'superadmin'): ?>
                <hr class="text-white-50">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/manajemen-user">
                        <i class="bi bi-people-fill"></i>
                        <span>Manajemen User</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/impor-pegawai">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Impor Pegawai</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/impor-absensi">
                        <i class="bi bi-upload"></i>
                        <span>Impor Absensi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/pengaturan">
                        <i class="bi bi-pin-map-fill"></i>
                        <span>Pengaturan Jarak & Potongan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/log-aktivitas">
                        <i class="bi bi-clock-history"></i>
                        <span>Log Aktivitas</span>
                    </a>
                </li>
            <?php endif; ?>

            <hr class="text-white-50">

            <li class="nav-item">
                <a class="nav-link" href="/dashboard">
                    <i class="bi bi-person-workspace"></i>
                    <span>Dashboard Pegawai</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="/auth/logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div> </div>

<div class="main-content">
    <main class="container-fluid">