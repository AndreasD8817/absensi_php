<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<button class="btn btn-primary sidebar-toggle" type="button" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<div class="user-sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/assets/img/logo/icon.png" alt="Avatar" class="logo">
        <h5 class="user-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h5>
        <p class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></p>
    </div>

    <ul class="nav flex-column sidebar-nav">
        <li class="nav-item">
            <a class="nav-link" href="/dashboard">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard Utama</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="action-buttons">
        <p class="action-title">AKSI HARI INI</p>
        <button class="btn btn-masuk <?php if(!$bisa_absen_masuk) echo 'disabled'; ?>" <?php if(!$bisa_absen_masuk) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalAbsen" onclick="bukaModalAbsen('Masuk')">
            <i class="bi bi-box-arrow-in-right"></i> Absen Masuk
        </button>
        <button class="btn btn-pulang <?php if(!$bisa_absen_pulang) echo 'disabled'; ?>" <?php if(!$bisa_absen_pulang) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalAbsen" onclick="bukaModalAbsen('Pulang')">
            <i class="bi bi-box-arrow-right"></i> Absen Pulang
        </button>
        <button class="btn btn-dl <?php if(!$bisa_dinas_luar) echo 'disabled'; ?>" <?php if(!$bisa_dinas_luar) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalDinasLuar">
            <i class="bi bi-briefcase-fill"></i> Dinas Luar
        </button>
    </div>

    <hr class="sidebar-divider">
    
    <div class="history-button-wrapper">
         <a class="btn btn-history" href="/riwayat-absensi">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Lihat Riwayat Absensi</span>
        </a>
    </div>


    <ul class="nav flex-column sidebar-nav sidebar-footer">
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin')): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin">
                    <i class="bi bi-shield-lock-fill"></i> Panel Admin
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalPanduan">
                <i class="bi bi-book-half"></i> Panduan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalPengaturan">
                <i class="bi bi-gear-fill"></i> Pengaturan Akun
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/auth/logout">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </li>
    </ul>
</div>