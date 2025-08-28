<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <link rel="icon" href="/assets/img/logo/favicon.png" type="image/png">
    <title>Dashboard Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/user_dashboard.css">
    <link rel="stylesheet" href="/assets/css/dashboard_view.css">
</head>
<body>

<div class="main-content">
    
    <header class="profile-header">
        <div class="d-flex align-items-center">
            <img src="/assets/img/logo/icon.png" alt="Avatar" class="logo">
            <div class="user-info">
                <h5><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h5>
                <p><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></p>
            </div>
        </div>
        <div class="header-actions">
            <a href="/auth/logout" class="btn btn-danger btn-sm" title="Logout">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </a>
        </div>
    </header>

    <div class="dashboard-container">
        
        <div id="notifikasi" class="alert" style="display:none;"></div>
        
        <div class="row">
            <div class="col-12 col-lg-5 mb-4">
                <section class="time-section p-4 rounded bg-white shadow-sm">
                    <div class="digital-clock" id="digital-clock">00:00:00</div>
                    <div class="date-display" id="date-display">...</div>
                </section>
                
                <section class="attendance-status">
                    <h2 class="status-title">Rekap Hari Ini</h2>
                    <div class="status-content">
                        <div class="status-item">
                            <div class="status-value text-success" id="check-in-time">
                                <?php echo $absen_hari_ini['masuk'] ? $absen_hari_ini['masuk']->format('H:i') : '--:--'; ?>
                            </div>
                            <div class="status-label">Absen Masuk</div>
                        </div>
                        <div class="status-item">
                            <div class="status-value text-danger" id="check-out-time">
                                <?php echo $absen_hari_ini['pulang'] ? $absen_hari_ini['pulang']->format('H:i') : '--:--'; ?>
                            </div>
                            <div class="status-label">Absen Pulang</div>
                        </div>
                        <div class="status-item">
                            <div class="status-value text-primary" id="working-hours">
                                <?php
                                if ($absen_hari_ini['masuk'] && $absen_hari_ini['pulang']) {
                                    $durasi = $absen_hari_ini['masuk']->diff($absen_hari_ini['pulang']);
                                    echo $durasi->format('%hj %im');
                                } else {
                                    echo '0j 0m';
                                }
                                ?>
                            </div>
                            <div class="status-label">Total Jam</div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-12 col-lg-7 mb-4">
                <section class="calendar-container">
                    <div class="calendar-header">
                        <h2 class="calendar-title" id="calendar-title"></h2>
                        <div class="calendar-nav">
                            <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                            <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="weekdays">
                        <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>
                    <div class="days" id="calendar-days"></div>
                </section>
            </div>
        </div>
        </div>
</div>

<div class="bottom-nav">
    <a href="/dashboard" class="nav-item-bottom active">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Home</span>
    </a>
    <a href="/riwayat-absensi" class="nav-item-bottom">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>Riwayat</span>
    </a>
    <div class="fab-absen" data-bs-toggle="modal" data-bs-target="#actionModal">
        <i class="bi bi-fingerprint"></i>
    </div>
    <a href="#" class="nav-item-bottom" data-bs-toggle="modal" data-bs-target="#modalPanduan">
        <i class="bi bi-book-half"></i>
        <span>Panduan</span>
    </a>
    <a href="#" class="nav-item-bottom" data-bs-toggle="modal" data-bs-target="#modalPengaturan">
        <i class="bi bi-gear-fill"></i>
        <span>Akun</span>
    </a>
</div>

<?php
require_once 'partials/modal_absen.php';
require_once 'partials/modal_dinas_luar.php';
require_once 'partials/modal_pengaturan.php';
require_once 'partials/modal_panduan.php';
?>
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="actionModalLabel">Pilih Aksi Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body action-buttons">
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
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===================================
    // === 1. LOGIKA DARK MODE BARU ===
    // ===================================
    const darkModeToggle = document.getElementById('darkModeToggle');
    const currentTheme = localStorage.getItem('theme');

    
    // Fungsi Jam Digital
    const clockEl = document.getElementById('digital-clock');
    const dateEl = document.getElementById('date-display');
    function updateClock() {
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    updateClock();
    setInterval(updateClock, 1000);

    // =======================================================
    // === 3. LOGIKA KALENDER DENGAN HARI LIBUR (DIMODIFIKASI) ===
    // =======================================================
    let currentDate = new Date();
    const calendarDays = document.getElementById('calendar-days');
    const calendarTitle = document.getElementById('calendar-title');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    
    
    // Ambil data hari libur dari PHP dan ubah menjadi objek JavaScript
    const holidays = <?php echo json_encode(isset($daftar_libur) ? array_keys($daftar_libur) : []); ?>;

    function generateCalendar(date) {
        calendarDays.innerHTML = '';
        const year = date.getFullYear();
        const month = date.getMonth();
        calendarTitle.textContent = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        const firstDayOfMonth = new Date(year, month, 1).getDay(); // Hari pertama dalam bulan ini
        const totalDaysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Buat sel kosong sebelum tanggal 1
        for (let i = 0; i < firstDayOfMonth; i++) {
            calendarDays.innerHTML += `<div class="day other-month"></div>`;
        }

        // Buat sel untuk setiap tanggal
        for (let i = 1; i <= totalDaysInMonth; i++) {
            let dayClass = 'day';
            const today = new Date();
            const currentLoopDate = new Date(year, month, i);
            const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

            // Cek apakah tanggal saat ini adalah hari ini
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                dayClass += ' today';
            }
            
            // Cek apakah hari Minggu (getDay() == 0) ATAU termasuk hari libur nasional
            if (currentLoopDate.getDay() === 0 || holidays.includes(formattedDate)) {
                dayClass += ' holiday';
            }

            calendarDays.innerHTML += `<div class="${dayClass}">${i}</div>`;
        }
    }
    
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        generateCalendar(currentDate);
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        generateCalendar(currentDate);
    });

    generateCalendar(currentDate);
});
</script>

</body>
</html>