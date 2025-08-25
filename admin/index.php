<?php 
$page_title = 'Dashboard Admin';
require_once 'partials/header.php'; 

// --- PENGATURAN WAKTU & TANGGAL ---
$hari_ini = date('Y-m-d');
$hari_angka = date('w');
$batas_masuk_str = '07:30:00'; 
$batas_pulang_str = '16:00:00';
if ($hari_angka == 6) { $batas_masuk_str = '08:00:00'; $batas_pulang_str = '14:00:00'; } 
elseif ($hari_angka == 0) { $batas_masuk_str = null; $batas_pulang_str = null; }

// --- PENGAMBILAN DATA STATISTIK PEGAWAI ---
$result_total_pegawai = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE role = 'pegawai'");
$total_pegawai_role = mysqli_fetch_assoc($result_total_pegawai)['total'];
$result_nonaktif = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE role = 'pegawai' AND status = 'non-aktif'");
$total_nonaktif = mysqli_fetch_assoc($result_nonaktif)['total'];
$result_admin = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE role IN ('admin', 'superadmin')");
$total_admin = mysqli_fetch_assoc($result_admin)['total'];
$result_radius = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE radius_absensi IS NOT NULL");
$total_radius_khusus = mysqli_fetch_assoc($result_radius)['total'];

// --- PENGAMBILAN DATA STATISTIK ABSENSI HARI INI ---
$result_hadir = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_pegawai) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = '$hari_ini' AND tipe_absensi = 'Masuk'");
$total_hadir_hari_ini = mysqli_fetch_assoc($result_hadir)['total'];
$result_dl = mysqli_query($koneksi, "SELECT COUNT(id_absensi) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = '$hari_ini' AND tipe_absensi = 'Dinas Luar'");
$total_dinas_luar = mysqli_fetch_assoc($result_dl)['total'];
$total_terlambat = 0;
$total_pulang_cepat = 0;
if ($batas_masuk_str && $batas_pulang_str) {
    $sql_terlambat = "SELECT COUNT(id_absensi) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = ? AND tipe_absensi = 'Masuk' AND TIME(waktu_absensi) > ?";
    $stmt_terlambat = mysqli_prepare($koneksi, $sql_terlambat);
    mysqli_stmt_bind_param($stmt_terlambat, "ss", $hari_ini, $batas_masuk_str);
    mysqli_stmt_execute($stmt_terlambat);
    $total_terlambat = mysqli_stmt_get_result($stmt_terlambat)->fetch_assoc()['total'];
    $sql_cepat_pulang = "SELECT COUNT(id_absensi) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = ? AND tipe_absensi = 'Pulang' AND TIME(waktu_absensi) < ?";
    $stmt_cepat_pulang = mysqli_prepare($koneksi, $sql_cepat_pulang);
    mysqli_stmt_bind_param($stmt_cepat_pulang, "ss", $hari_ini, $batas_pulang_str);
    mysqli_stmt_execute($stmt_cepat_pulang);
    $total_pulang_cepat = mysqli_stmt_get_result($stmt_cepat_pulang)->fetch_assoc()['total'];
}

// --- KALKULASI STATISTIK GABUNGAN ---
$result_pegawai_aktif = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE status = 'aktif' AND role = 'pegawai'");
$total_pegawai_aktif = mysqli_fetch_assoc($result_pegawai_aktif)['total'];
$total_mangkir = $total_pegawai_aktif - ($total_hadir_hari_ini + $total_dinas_luar);
if ($total_mangkir < 0) $total_mangkir = 0;
$persentase_hadir = ($total_pegawai_aktif > 0) ? round((($total_hadir_hari_ini + $total_dinas_luar) / $total_pegawai_aktif) * 100) : 0;

// === INI BARIS YANG DIPERBAIKI / DITAMBAHKAN ===
$hadir_tepat_waktu = $total_hadir_hari_ini - $total_terlambat;

// Data untuk tabel Aktivitas Real-time
$aktivitas_terbaru = [];
$sql_aktivitas = "SELECT p.nama_lengkap, a.tipe_absensi, a.waktu_absensi 
                  FROM tabel_absensi a 
                  JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai 
                  WHERE DATE(a.waktu_absensi) = ? 
                  ORDER BY a.waktu_absensi DESC 
                  LIMIT 10";
$stmt_aktivitas = mysqli_prepare($koneksi, $sql_aktivitas);
mysqli_stmt_bind_param($stmt_aktivitas, "s", $hari_ini);
mysqli_stmt_execute($stmt_aktivitas);
$result_aktivitas = mysqli_stmt_get_result($stmt_aktivitas);
while ($row = mysqli_fetch_assoc($result_aktivitas)) {
    $aktivitas_terbaru[] = $row;
}
?>

<style>
    :root {
        --primary: #4361ee; 
        --secondary: #3a0ca3; 
        --accent: #f72585;
        --light: #f8f9fa; 
        --dark: #212529; 
        --success: #4cc9f0;
        --warning: #ffd166; 
        --danger: #ef476f;
        --card-bg: rgba(255, 255, 255, 0.08);
        --card-border: rgba(255, 255, 255, 0.12);
        --text-primary: #ffffff;
        --text-secondary: rgba(255, 255, 255, 0.7);
    }
    
    .main-content {
        padding: 0; 
        background: linear-gradient(-45deg, #1a2a6c, #2b5776, #4e54c8, #2b5776);
        background-size: 400% 400%; 
        animation: gradient 15s ease infinite; 
        color: var(--light);
        min-height: 100vh;
    }
    
    @keyframes gradient { 
        0% { background-position: 0% 50%; } 
        50% { background-position: 100% 50%; } 
        100% { background-position: 0% 50%; } 
    }
    
    .dashboard-header {
        background: rgba(0, 0, 0, 0.2); 
        backdrop-filter: blur(10px); 
        padding: 2rem 1.5rem; 
        text-align: center;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); 
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 2rem;
    }
    
    .dashboard-header .title { 
        font-size: 2.5rem; 
        font-weight: 700; 
        color: #fff; 
        margin-bottom: 0.5rem;
    }
    
    .dashboard-header .subtitle { 
        font-size: 1.1rem; 
        opacity: 0.85;
        font-weight: 300;
    }
    
    .dashboard-container { 
        max-width: 1600px; 
        margin: 0 auto; 
        padding: 0 1.5rem 3rem; 
    }
    
    .dashboard-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
    }
    
    .section-title i {
        margin-right: 0.75rem;
        font-size: 1.8rem;
    }
    
    .stat-card-link { 
        text-decoration: none; 
        color: inherit;
        display: block;
        transition: transform 0.3s ease;
    }
    
    .stat-card-link:hover {
        transform: translateY(-5px);
    }
    
    .stat-card {
        background: var(--card-bg); 
        backdrop-filter: blur(12px); 
        border-radius: 16px; 
        padding: 1.75rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); 
        border: 1px solid var(--card-border);
        transition: all 0.3s ease; 
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card-link:hover .stat-card::before {
        opacity: 1;
    }
    
    .stat-card-link:hover .stat-card { 
        transform: translateY(-8px); 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); 
    }
    
    .card-content { 
        display: flex; 
        align-items: center; 
        position: relative;
        z-index: 2;
    }
    
    .card-icon {
        font-size: 2.2rem; 
        margin-right: 1.5rem; 
        color: var(--light);
        width: 70px; 
        height: 70px; 
        border-radius: 18px; 
        display: flex;
        align-items: center; 
        justify-content: center; 
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .card-details .card-title { 
        font-size: 0.95rem; 
        font-weight: 500; 
        opacity: 0.85; 
        margin: 0 0 0.25rem; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .card-details .card-value { 
        font-size: 2.5rem; 
        font-weight: 700; 
        line-height: 1.2; 
        color: var(--text-primary);
        margin: 0;
    }
    
    /* Modal Styles */
    .modal-content { 
        background: #2a3b52; 
        color: var(--light); 
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .modal-header { 
        border-bottom: 1px solid rgba(255, 255, 255, 0.15); 
        padding: 1.5rem;
        background: rgba(0, 0, 0, 0.2);
    }
    
    .modal-title {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .btn-close { 
        filter: invert(1); 
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }
    
    .btn-close:hover {
        opacity: 1;
    }
    
    /* Table Styles */
    .table-container-modern { 
        border-radius: 14px; 
        overflow: hidden; 
        background: rgba(255, 255, 255, 0.05); 
        backdrop-filter: blur(15px); 
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .table-modern { 
        width: 100%; 
        border-collapse: separate;
        border-spacing: 0;
        margin: 0; 
        color: var(--light);
    }
    
    .table-modern thead { 
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.2), rgba(58, 12, 163, 0.2));
    }
    
    .table-modern th { 
        padding: 1.25rem 1.5rem; 
        text-align: center; 
        font-weight: 600; 
        font-size: 0.9rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        color: rgba(255, 255, 255, 0.8);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-modern td { 
        padding: 1.25rem 1.5rem; 
        border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
        vertical-align: middle;
        transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }
    
    .activity-row { 
        transition: all 0.3s ease;
    }
    
    .activity-row:hover { 
        background: rgba(255, 255, 255, 0.05);
        transform: translateX(4px);
    }
    
    .time-cell { 
        font-weight: 600; 
        text-align: center;
        width: 120px;
    }
    
    .time-badge { 
        font-size: 1.1rem; 
        font-weight: 700; 
        color: #fff;
        display: block;
    }
    
    .avatar-placeholder { 
        width: 45px; 
        height: 45px; 
        border-radius: 12px; 
        background: linear-gradient(135deg, var(--primary), var(--secondary)); 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 600; 
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
    }
    
    .name-cell {
        padding: 1rem 1.5rem;
    }
    
    .employee-info {
        display: flex;
        align-items: center;
    }
    
    .employee-name { 
        font-weight: 500;
        margin-left: 1rem;
    }
    
    .status-cell { 
        text-align: center;
        width: 140px;
    }
    
    .status-badge { 
        padding: 0.6rem 1rem; 
        border-radius: 8px; 
        font-size: 0.85rem; 
        font-weight: 600; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center;
        min-width: 100px;
    }
    
    .status-badge.masuk { 
        background: rgba(40, 167, 69, 0.2); 
        color: #28a745; 
        border: 1px solid rgba(40, 167, 69, 0.3);
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.15);
    }
    
    .status-badge.pulang { 
        background: rgba(220, 53, 69, 0.2); 
        color: #dc3545; 
        border: 1px solid rgba(220, 53, 69, 0.3);
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.15);
    }
    
    .status-badge.dinas { 
        background: rgba(255, 193, 7, 0.2); 
        color: #ffc107; 
        border: 1px solid rgba(255, 193, 7, 0.3);
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.15);
    }
    
    .no-data { 
        color: rgba(255, 255, 255, 0.5); 
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .text-muted {
        color: rgba(255, 255, 255, 0.6) !important;
        font-size: 0.85rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-header .title {
            font-size: 2rem;
        }
        
        .dashboard-header .subtitle {
            font-size: 1rem;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            font-size: 1.8rem;
            margin-right: 1.25rem;
        }
        
        .card-details .card-value {
            font-size: 2.2rem;
        }
        
        .table-modern th,
        .table-modern td {
            padding: 1rem;
        }
        
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            font-size: 0.9rem;
        }
    }
    
    @media (max-width: 576px) {
        .dashboard-container {
            padding: 0 1rem 2rem;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .stat-card {
            padding: 1.5rem;
        }
        
        .card-content {
            flex-direction: column;
            text-align: center;
        }
        
        .card-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1.3rem;
        }
        
        .table-modern {
            display: block;
            overflow-x: auto;
        }
        
        .employee-info {
            justify-content: center;
        }
    }
</style>

<header class="dashboard-header">
    <h1 class="title">Selamat Datang, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</h1>
    <p class="subtitle">Ringkasan aktivitas dan data kepegawaian terkini</p>
</header>

            <div class="dashboard-container">
                <h4 class="section-title"><i class="bi bi-people-fill"></i> Statistik Kepegawaian</h4>
    <div class="dashboard-grid">
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="total_pegawai" data-title="Daftar Total Pegawai">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #0d6efd, #6f42c1);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Total Pegawai</p>
                        <h3 class="card-value"><?php echo $total_pegawai_role; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="non_aktif" data-title="Daftar Pegawai Non-Aktif">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #6c757d, #343a40);">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Pegawai Non-Aktif</p>
                        <h3 class="card-value"><?php echo $total_nonaktif; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="total_admin" data-title="Daftar Admin & Superadmin">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #198754, #157347);">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Total Admin</p>
                        <h3 class="card-value"><?php echo $total_admin; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="radius_khusus" data-title="Pegawai dengan Radius Khusus">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #fd7e14, #dc3545);">
                        <i class="bi bi-rulers"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Radius Khusus</p>
                        <h3 class="card-value"><?php echo $total_radius_khusus; ?></h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <h4 class="section-title"><i class="bi bi-calendar-check"></i> Aktivitas Hari Ini (<?php echo date('d F Y'); ?>)</h4>
    <div class="dashboard-grid">
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="hadir" data-title="Daftar Pegawai Hadir Hari Ini">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Hadir</p>
                        <h3 class="card-value"><?php echo $total_hadir_hari_ini; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="terlambat" data-title="Daftar Pegawai Terlambat Hari Ini">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Terlambat</p>
                        <h3 class="card-value"><?php echo $total_terlambat; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="pulang_cepat" data-title="Daftar Pegawai Pulang Cepat Hari Ini">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #dc3545, #ef476f);">
                        <i class="bi bi-box-arrow-left"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Pulang Cepat</p>
                        <h3 class="card-value"><?php echo $total_pulang_cepat; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="dinas_luar" data-title="Daftar Pegawai Dinas Luar Hari Ini">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #17a2b8, #6610f2);">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Dinas Luar</p>
                        <h3 class="card-value"><?php echo $total_dinas_luar; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#detailModal" data-type="tidak_hadir" data-title="Daftar Pegawai Tidak Hadir Hari Ini">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #dc3545, #b22222);">
                        <i class="bi bi-x-octagon-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Tidak Hadir</p>
                        <h3 class="card-value"><?php echo $total_mangkir; ?></h3>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="#" class="stat-card-link" data-bs-toggle="modal" data-bs-target="#chartModal">
            <div class="stat-card">
                <div class="card-content">
                    <div class="card-icon" style="background: linear-gradient(135deg, #6f42c1, #3a0ca3);">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div class="card-details">
                        <p class="card-title">Persentase Hadir</p>
                        <h3 class="card-value"><?php echo $persentase_hadir; ?>%</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <h4 class="section-title"><i class="bi bi-lightning-charge-fill"></i> Aktivitas Terbaru</h4>
    <div class="row mt-2">
        <div class="col-12">
            <div class="table-container-modern">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="time-column">Waktu</th>
                            <th class="name-column">Nama Pegawai</th>
                            <th class="status-column">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($aktivitas_terbaru)): ?>
                            <tr>
                                <td colspan="3" class="no-data">
                                    <div class="d-flex flex-column align-items-center py-5">
                                        <i class="bi bi-inbox fs-1 mb-2 opacity-50"></i>
                                        <p class="mb-0">Belum ada aktivitas hari ini</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($aktivitas_terbaru as $aktivitas): ?>
                                <tr class="activity-row">
                                    <td class="time-cell">
                                        <div class="time-badge">
                                            <?php echo date('H:i', strtotime($aktivitas['waktu_absensi'])); ?>
                                        </div>
                                        <small class="text-muted"><?php echo date('s', strtotime($aktivitas['waktu_absensi'])); ?> detik</small>
                                    </td>
                                    <td class="name-cell">
                                        <div class="employee-info">
                                            <div class="avatar-placeholder">
                                                <?php 
                                                    $nama_parts = explode(' ', $aktivitas['nama_lengkap']);
                                                    $inisial = strtoupper(substr($nama_parts[0], 0, 1));
                                                    if (count($nama_parts) > 1) {
                                                        $inisial .= strtoupper(substr(end($nama_parts), 0, 1));
                                                    }
                                                    echo $inisial;
                                                ?>
                                            </div>
                                            <div class="employee-name"><?php echo htmlspecialchars($aktivitas['nama_lengkap']); ?></div>
                                        </div>
                                    </td>
                                    <td class="status-cell">
                                        <?php if($aktivitas['tipe_absensi'] == 'Masuk'): ?>
                                            <span class="status-badge masuk"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</span>
                                        <?php elseif($aktivitas['tipe_absensi'] == 'Pulang'): ?>
                                            <span class="status-badge pulang"><i class="bi bi-box-arrow-right me-1"></i> Pulang</span>
                                        <?php else: ?>
                                            <span class="status-badge dinas"><i class="bi bi-briefcase me-1"></i> Dinas Luar</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detailModalBody">
        <div class="text-center p-5">
          <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 mb-0">Memuat data...</p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="chartModal" tabindex="-1" aria-labelledby="chartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="chartModalLabel"><i class="bi bi-pie-chart-fill me-2"></i>Ringkasan Kehadiran Hari Ini</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div style="height: 350px;">
            <canvas id="attendancePieChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailModal = document.getElementById('detailModal');
    detailModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const type = button.getAttribute('data-type');
        const title = button.getAttribute('data-title');
        const modalTitle = detailModal.querySelector('.modal-title');
        modalTitle.textContent = title;
        const modalBody = detailModal.querySelector('.modal-body');
        modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 mb-0">Memuat data...</p></div>';
        
        fetch(`get_modal_data.php?type=${type}`)
            .then(response => response.text())
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(error => {
                modalBody.innerHTML = `<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>`;
                console.error('Error:', error);
            });
    });
    // (BARU) Script untuk modal grafik
    const chartModal = document.getElementById('chartModal');
    let attendanceChart = null; // Variabel untuk menyimpan instance chart

    chartModal.addEventListener('shown.bs.modal', function () {
        // Data dari PHP untuk pie chart
        const data = {
            labels: ['Hadir Tepat Waktu', 'Terlambat', 'Dinas Luar', 'Tidak Hadir'],
            datasets: [{
                data: [
                    <?php echo $hadir_tepat_waktu; ?>, 
                    <?php echo $total_terlambat; ?>, 
                    <?php echo $total_dinas_luar; ?>, 
                    <?php echo $total_mangkir; ?>
                ],
                backgroundColor: [
                    'rgba(76, 201, 240, 0.8)',   // Biru muda
                    'rgba(255, 209, 102, 0.8)', // Kuning
                    'rgba(67, 97, 238, 0.8)',    // Biru tua
                    'rgba(239, 71, 111, 0.8)'     // Merah
                ],
                borderColor: [ '#4CC9F0', '#FFD166', '#4361EE', '#EF476F' ],
                borderWidth: 1
            }]
        };

        // Konfigurasi Chart
        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: 'white' }
                    }
                }
            },
        };

        // Hancurkan chart lama jika ada, lalu buat yang baru
        if (attendanceChart) {
            attendanceChart.destroy();
        }
        attendanceChart = new Chart(document.getElementById('attendancePieChart'), config);
    });
});
</script>

<?php require_once 'partials/footer.php'; ?>