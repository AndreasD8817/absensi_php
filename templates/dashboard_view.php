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

<!-- === NAVBAR BARU DENGAN DROPDOWN === -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/dashboard">Aplikasi Absensi</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <!-- Tombol ini akan memicu modal -->
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPengaturan">Pengaturan Akun</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/auth/logout">Logout</a></li>
            </ul>
        </li>
    </ul>
  </div>
</nav>

<div class="container mt-4">
    <!-- Menampilkan notifikasi dari proses update profil -->
    <?php if (isset($_GET['sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['sukses']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-success">
        <h4 class="alert-heading">Selamat Datang!</h4>
        <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Silakan lakukan absensi.</p>
    </div>

    <div id="notifikasi" class="alert" style="display:none;"></div>
    
    <!-- Sisa konten dashboard tidak berubah -->
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
                <a href="#" class="card-menu <?php if($sudah_selesai) echo 'disabled-card'; ?>" data-bs-toggle="modal" data-bs-target="#modalDinasLuar">
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
    </div>
</div>

<?php
// Memanggil modal yang sudah ada
require_once 'templates/partials/modal_absen.php';
require_once 'templates/partials/modal_dinas_luar.php';
?>

<!-- === MODAL PENGATURAN AKUN BARU === -->
<div class="modal fade" id="modalPengaturan" tabindex="-1" aria-labelledby="modalPengaturanLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPengaturanLabel"><i class="bi bi-gear-fill"></i> Pengaturan Akun</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/proses-profil" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <hr>
            <p class="text-muted small">Kosongkan bagian password jika Anda tidak ingin mengubahnya.</p>
            <div class="mb-3">
                <label for="password_lama" class="form-label">Password Lama (wajib diisi jika ingin ganti password)</label>
                <input type="password" class="form-control" id="password_lama" name="password_lama">
            </div>
            <div class="mb-3">
                <label for="password_baru" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="password_baru" name="password_baru">
            </div>
            <div class="mb-3">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js"></script>

</body>
</html>