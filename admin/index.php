<?php 
$page_title = 'Dashboard Admin';
require_once 'partials/header.php'; 
?>

<div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-body-secondary text-center">
    <div class="col-lg-8 px-0">
        <h1 class="display-4 fst-italic">Selamat Datang di Panel Admin</h1>
        <p class="lead my-3">Dari sini Anda dapat mengelola data absensi, hari libur, dan pengguna sistem.</p>
    </div>
</div>

<div class="row text-center justify-content-center">
    <!-- Card 1: Kelola Hari Libur (Bisa diakses Admin & Superadmin) -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/kelola-libur" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-calendar-plus-fill icon-lg text-info"></i>
                    <h5 class="card-title mt-3">Input Hari Libur</h5>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 2: Laporan Absensi (Bisa diakses Admin & Superadmin) -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/laporan-absensi" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-file-earmark-spreadsheet-fill icon-lg text-success"></i>
                    <h5 class="card-title mt-3">Laporan Absensi</h5>
                </div>
            </div>
        </a>
    </div>

    <!-- Fitur Khusus Superadmin -->
    <?php if ($_SESSION['role'] == 'superadmin'): ?>
    <!-- Card 3: Impor Data Absensi -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/impor-absensi" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-upload icon-lg text-primary"></i>
                    <h5 class="card-title mt-3">Impor Absensi</h5>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4: Impor Data Pegawai (BARU) -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/impor-pegawai" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-person-lines-fill icon-lg text-secondary"></i>
                    <h5 class="card-title mt-3">Impor Pegawai</h5>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 5: Manajemen User -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/manajemen-user" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-people-fill icon-lg text-danger"></i>
                    <h5 class="card-title mt-3">Manajemen User</h5>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 6: Pengaturan Jarak -->
    <div class="col-md-4 mb-3">
        <a href="/absensi_php/admin/pengaturan" class="card-menu">
            <div class="card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-pin-map-fill icon-lg text-warning"></i>
                    <h5 class="card-title mt-3">Pengaturan Jarak</h5>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
