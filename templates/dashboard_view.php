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

<?php
// Panggil sidebar
require_once __DIR__ . '/partials/navbar.php'; 
?>

<div class="main-content">
    
    <header class="dashboard-header">
        <h1 class="title">Dashboard Absensi</h1>
        <p class="subtitle">Selamat datang kembali, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]); ?>!</p>
    </header>

    <div class="dashboard-container">
        
        <div id="notifikasi" class="alert" style="display:none;"></div>
        
        <div class="row">
            <div class="col-lg-7 col-md-12 mb-4">
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
            <div class="col-lg-5 col-md-12">
                <section class="time-section p-4 rounded bg-white shadow-sm mb-4">
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
        </div>

    </div>
</div>


<?php
// Panggil semua modal yang diperlukan
require_once 'partials/modal_absen.php';
require_once 'partials/modal_dinas_luar.php';
require_once 'partials/modal_pengaturan.php';
require_once 'partials/modal_panduan.php';
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi Toggle Sidebar
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('active'));
    }

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

    // Fungsi Kalender
    let currentDate = new Date();
    const calendarDays = document.getElementById('calendar-days');
    const calendarTitle = document.getElementById('calendar-title');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');

    function generateCalendar(date) {
        calendarDays.innerHTML = '';
        const year = date.getFullYear();
        const month = date.getMonth();
        calendarTitle.textContent = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        const firstDay = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();
        
        for (let i = 0; i < firstDay; i++) {
            calendarDays.innerHTML += `<div class="day other-month"></div>`;
        }

        for (let i = 1; i <= totalDays; i++) {
            let dayClass = 'day';
            const today = new Date();
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                dayClass += ' today';
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