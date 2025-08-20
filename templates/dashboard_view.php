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
    <style>
        /* CSS Tambahan untuk Dashboard Baru */
        .main-content {
            padding: 0;
            background: #f4f7fc;
            color: var(--dark);
        }

        .dashboard-header {
            background-color: #fff;
            padding: 2rem;
            border-bottom: 1px solid #dee2e6;
        }

        .dashboard-header .title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #343a40;
        }

        .dashboard-header .subtitle {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .dashboard-container {
            padding: 2rem;
        }
        
        .time-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            text-align: center;
        }

        .digital-clock {
            font-size: 4rem;
            font-weight: 700;
            color: #212529;
        }

        .date-display {
            font-size: 1.25rem;
            color: #495057;
        }

        .calendar-container, .attendance-status {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .calendar-title { font-size: 1.25rem; font-weight: 600; }
        .calendar-nav button { background: #0d6efd; border: none; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; }
        .weekdays { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-weight: 600; margin-bottom: 0.5rem; color: #6c757d; }
        .days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; }
        .day { text-align: center; padding: 0.75rem; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
        .day:hover { background: #e9ecef; }
        .today { background: #0d6efd; color: white; font-weight: 700; }
        .other-month { opacity: 0.4; }
        
        .status-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
        .status-content { display: flex; justify-content: space-around; align-items: center; text-align: center; }
        .status-value { font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; }
        .status-label { font-size: 0.9rem; color: #6c757d; }
    </style>
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
            <div class="col-lg-8">
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
            <div class="col-lg-4">
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