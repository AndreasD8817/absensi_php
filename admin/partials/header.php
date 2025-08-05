<?php
// Selalu mulai sesi di setiap halaman admin
session_start();

// "Penjaga Gerbang"
// 1. Cek apakah sesi login ada
// 2. Cek apakah role user adalah 'admin' atau 'superadmin'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    // Jika tidak, tendang ke halaman login
    header("Location: /absensi_php/login?error=Akses ditolak");
    exit();
}

// Panggil file koneksi database jika diperlukan di banyak tempat
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Judul halaman akan dinamis -->
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Aplikasi Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/absensi_php/admin">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="/absensi_php/dashboard">Dashboard Pegawai</a>
            </li>
            <!-- Menu lain bisa ditambahkan di sini -->
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/absensi_php/auth/logout">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">
