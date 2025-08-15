<?php
// Pastikan sesi sudah dimulai di file utama (seperti index.php)
// agar variabel $_SESSION bisa diakses di sini.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    /* CSS untuk gradien modern */
    .navbar-rekabsen {
        background: linear-gradient(90deg, rgba(3,53,119,1) 0%, rgba(32,96,203,1) 100%);
        backdrop-filter: blur(5px);
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-rekabsen shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/dashboard">
      <img src="/assets/img/logo/favicon1.png" alt="Logo RekAbsen" width="50" height="50" class="me-2">
      <span class="fw-bold fs-4">RekAbsen</span>
    </a>
    <ul class="navbar-nav ms-auto">
    <?php if (isset($_SESSION['id_pegawai'])): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPengaturan"><i class="bi bi-gear-fill me-2"></i>Pengaturan Akun</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPanduan"><i class="bi bi-book-half me-2"></i>Panduan Pengguna</a></li>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin')): ?>
                <li><a class="dropdown-item" href="/admin"><i class="bi bi-shield-lock-fill me-2"></i>Panel Admin</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </li>
    <?php endif; ?>
    </ul>
  </div>
</nav>

<script>
    document.body.style.paddingTop = document.querySelector('.navbar').offsetHeight + 'px';
</script>