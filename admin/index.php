<?php 
$page_title = 'Dashboard Admin';
require_once 'partials/header.php'; 

// === LOGIKA PENGAMBILAN DATA UNTUK KARTU STATISTIK ===
$hari_ini = date('Y-m-d');

// 1. Total Karyawan
$result_total_pegawai = mysqli_query($koneksi, "SELECT COUNT(id_pegawai) as total FROM tabel_pegawai WHERE status = 'aktif'");
$total_pegawai = mysqli_fetch_assoc($result_total_pegawai)['total'];

// 2. Hadir Hari Ini
$result_hadir = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_pegawai) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = '$hari_ini' AND tipe_absensi = 'Masuk'");
$total_hadir_hari_ini = mysqli_fetch_assoc($result_hadir)['total'];

// 3. Terlambat Hari Ini (Asumsi jam masuk > 07:30)
$result_terlambat = mysqli_query($koneksi, "SELECT COUNT(id_absensi) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = '$hari_ini' AND tipe_absensi = 'Masuk' AND TIME(waktu_absensi) > '07:30:00'");
$total_terlambat = mysqli_fetch_assoc($result_terlambat)['total'];

// 4. Dinas Luar Hari Ini
$result_dl = mysqli_query($koneksi, "SELECT COUNT(id_absensi) as total FROM tabel_absensi WHERE DATE(waktu_absensi) = '$hari_ini' AND tipe_absensi = 'Dinas Luar'");
$total_dinas_luar = mysqli_fetch_assoc($result_dl)['total'];

// 5. Mangkir Hari Ini (Logika Sederhana: Total Aktif - (Hadir + Dinas Luar))
$total_mangkir = $total_pegawai - ($total_hadir_hari_ini + $total_dinas_luar);
if ($total_mangkir < 0) {
    $total_mangkir = 0; // Pastikan tidak ada nilai negatif
}

// 6. Rata-rata Kehadiran (Placeholder, karena butuh logika lebih kompleks)
// Untuk sementara kita hitung persentase kehadiran hari ini
$rata_rata_kehadiran = ($total_pegawai > 0) ? round(($total_hadir_hari_ini / $total_pegawai) * 100) : 0;

?>

<style>
    /* Variabel CSS */
    :root {
        --primary: #4361ee;
        --secondary: #3a0ca3;
        --accent: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        --success: #4cc9f0;
        --warning: #ffd166;
        --danger: #ef476f;
        --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
    }

    /* Override beberapa style dasar dari layout utama */
    .main-content {
        padding: 0;
        background: linear-gradient(-45deg, #1a2a6c, #2b5876, #4e54c8, #2b5876);
        background-size: 400% 400%;
        animation: gradient 15s ease infinite;
        color: var(--light);
    }

    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Header Styles */
    .dashboard-header {
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }

    .dashboard-header .title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #fff;
    }

    .dashboard-header .subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 1rem;
    }

    /* Main Content */
    .dashboard-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    /* Card Styles */
    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition: all 0.4s ease;
        opacity: 0;
        transform: translateY(30px);
        animation: slideIn 0.8s ease-out forwards;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    .stat-card:nth-child(5) { animation-delay: 0.5s; }
    .stat-card:nth-child(6) { animation-delay: 0.6s; }

    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .card-content {
        display: flex;
        align-items: center;
    }

    .card-icon {
        font-size: 2rem;
        margin-right: 1.5rem;
        color: var(--light);
        background: var(--gradient);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .card-icon {
        transform: rotate(10deg) scale(1.1);
    }

    .card-details .card-title {
        font-size: 1rem;
        font-weight: 600;
        opacity: 0.8;
        margin: 0;
    }
    
    .card-details .card-value {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

</style>

<header class="dashboard-header">
    <h1 class="title">Selamat Datang, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</h1>
    <p class="subtitle">Berikut adalah ringkasan aktivitas absensi hari ini.</p>
</header>

<div class="dashboard-container">
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="card-content">
                <div class="card-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="card-details">
                    <p class="card-title">Total Karyawan Aktif</p>
                    <h3 class="card-value"><?php echo $total_pegawai; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="card-content">
                <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="card-details">
                    <p class="card-title">Hadir Hari Ini</p>
                    <h3 class="card-value"><?php echo $total_hadir_hari_ini; ?></h3>
                </div>
            </div>
        </div>
        
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
        
        <div class="stat-card">
            <div class="card-content">
                <div class="card-icon" style="background: linear-gradient(135deg, #17a2b8, #6610f2);">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div class="card-details">
                    <p class="card-title">Dinas Luar Kota</p>
                    <h3 class="card-value"><?php echo $total_dinas_luar; ?></h3>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="card-content">
                <div class="card-icon" style="background: linear-gradient(135deg, #dc3545, #ef476f);">
                    <i class="bi bi-x-octagon-fill"></i>
                </div>
                <div class="card-details">
                    <p class="card-title">Tidak Hadir (Alpha)</p>
                    <h3 class="card-value"><?php echo $total_mangkir; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="card-content">
                <div class="card-icon" style="background: linear-gradient(135deg, #6f42c1, #3a0ca3);">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="card-details">
                    <p class="card-title">Persentase Hadir Hari Ini</p>
                    <h3 class="card-value"><?php echo $rata_rata_kehadiran; ?>%</h3>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'partials/footer.php'; ?>