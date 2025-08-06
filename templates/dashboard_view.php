<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Aplikasi Absensi</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a class="nav-link" href="/auth/logout">Logout</a>
        </li>
    </ul>
  </div>
</nav>

<div class="container mt-4">
    <div class="alert alert-success">
        <h4 class="alert-heading">Selamat Datang!</h4>
        <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Silakan lakukan absensi.</p>
    </div>

    <div id="notifikasi" class="alert" style="display:none;"></div>
    
    <?php if ($sudah_selesai): ?>
        <div class="alert alert-info text-center">
            <h5>Anda sudah menyelesaikan absensi hari ini. Terima kasih.</h5>
        </div>
    <?php else: ?>
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_masuk) echo 'disabled-card'; ?>" onclick="bukaModalAbsen('Masuk')" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-in-right icon-lg text-success"></i>
                            <h5 class="card-title mt-3">Absen Masuk</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_pulang) echo 'disabled-card'; ?>" onclick="bukaModalAbsen('Pulang')" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-right icon-lg text-danger"></i>
                            <h5 class="card-title mt-3">Absen Pulang</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_masuk) echo 'disabled-card'; ?>" data-bs-toggle="modal" data-bs-target="#modalDinasLuar">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-briefcase-fill icon-lg text-warning"></i>
                            <h5 class="card-title mt-3">Dinas Luar Kota</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <hr class="my-4">

    <h4 class="mb-3">Laporan</h4>
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <a href="/riwayat-absensi" class="card-menu">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-text-fill icon-lg text-primary"></i>
                        <h5 class="card-title mt-3">Data Absensi</h5>
                    </div>
                </div>
            </a>
        </div>
        <!-- === PERUBAHAN DI SINI === -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
        <div class="col-md-4 mb-3">
            <a href="/admin" class="card-menu">
                <div class="card shadow-sm border-danger">
                    <div class="card-body">
                        <i class="bi bi-shield-lock-fill icon-lg text-danger"></i>
                        <h5 class="card-title mt-3">Panel Admin</h5>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <!-- === AKHIR PERUBAHAN === -->
    </div>
</div>
<?php
require_once 'templates/partials/modal_absen.php';
require_once 'templates/partials/modal_dinas_luar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js" defer></script>

</body>
</html>