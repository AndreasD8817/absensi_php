<?php
// Pastikan sesi sudah dimulai di file utama (seperti index.php)
// agar variabel $_SESSION bisa diakses di sini.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/dashboard">
      <img src="/assets/img/logo/rekAbsen.png" alt="Logo RekAbsen" width="70" height="70" class="me-2">
      <span style="font-weight: bold; font-size: 1.5rem;">RekAbsen</span>
    </a>
    <ul class="navbar-nav ms-auto">
    <?php if (isset($_SESSION['id_pegawai'])): // Diubah dari user_id menjadi id_pegawai ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPengaturan"><i class="bi bi-gear-fill me-2"></i>Pengaturan Akun</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </li>
    <?php endif; ?>
</ul>
  </div>
</nav>