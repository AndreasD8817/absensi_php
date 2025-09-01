<?php
// Tetap mengirim header 200 OK untuk halaman coming soon
http_response_code(200);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segera Hadir | REKABSEN</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;700;900&display=swap');
        
        :root {
            --primary: #6C5CE7;
            --secondary: #FD79A8;
            --accent: #00CEFF;
            --dark: #2D3436;
            --light: #F9F9F9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            padding: 1rem;
        }
        
        .comingsoon-container {
            text-align: center;
            padding: 2rem 1.5rem;
            max-width: 800px;
            width: 100%;
            position: relative;
            z-index: 10;
        }
        
        h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        p {
            font-size: clamp(1rem, 3vw, 1.2rem);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .highlight {
            font-weight: 700;
            color: var(--accent);
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 2rem 0;
        }
        
        .countdown-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 1rem 0.8rem;
            border-radius: 10px;
            min-width: 70px;
            width: calc(25% - 1rem);
            max-width: 90px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .countdown-number {
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            font-weight: 700;
        }
        
        .countdown-label {
            font-size: 0.7rem;
            opacity: 0.8;
            text-transform: uppercase;
        }
        
        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            margin: 2rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            width: 65%;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            border-radius: 5px;
            animation: progress-animation 2s ease-in-out infinite alternate;
        }
        
        @keyframes progress-animation {
            0% { width: 65%; }
            100% { width: 70%; }
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .social-link {
            color: var(--light);
            background: rgba(255, 255, 255, 0.1);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
            filter: blur(40px);
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: var(--accent);
            top: -100px;
            left: -100px;
            animation: float 15s infinite ease-in-out;
        }
        
        .shape-2 {
            width: 400px;
            height: 400px;
            background: var(--secondary);
            bottom: -150px;
            right: -100px;
            animation: float 18s infinite ease-in-out reverse;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            background: var(--light);
            top: 50%;
            left: 30%;
            animation: float 12s infinite ease-in-out;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(50px, 50px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        
        .icon {
            font-size: clamp(3rem, 10vw, 5rem);
            margin-bottom: 1rem;
            animation: bounce 2s infinite ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .copyright {
            margin-top: 2.5rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .social-link svg {
            width: 20px;
            height: 20px;
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.2rem;
            border-radius: 12px;
            width: 150px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-text {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        @media (max-width: 480px) {
            .comingsoon-container {
                padding: 1.5rem 1rem;
            }
            
            .countdown-item {
                width: calc(50% - 1rem);
                max-width: none;
                margin-bottom: 0.5rem;
            }
            
            .feature-item {
                width: calc(50% - 1rem);
                padding: 1rem 0.8rem;
            }
            
            .social-link {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="comingsoon-container">
        <div class="icon">🚀</div>
        <h1>Segera Hadir!</h1>
        <p><span class="highlight">REKABSEN</span> - Solusi revolusioner untuk manajemen kehadiran digital yang akan membantu Anda melakukan presensi.</p>
        
        <div class="countdown">
            <div class="countdown-item">
                <div class="countdown-number" id="days">00</div>
                <div class="countdown-label">Hari</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="hours">00</div>
                <div class="countdown-label">Jam</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="minutes">00</div>
                <div class="countdown-label">Menit</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="seconds">00</div>
                <div class="countdown-label">Detik</div>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">⏱️</div>
                <div class="feature-text">Presensi Real-time</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📊</div>
                <div class="feature-text">Laporan Detail</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <div class="feature-text">Keamanan Data</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📱</div>
                <div class="feature-text">Akses Mobile</div>
            </div>
        </div>
        
        <p>Kami sedang mempersiapkan sesuatu yang istimewa untuk Anda. Ikuti media sosial kami untuk update terbaru.</p>
        
        <div class="social-links">
            <a href="https://instagram.com/rekabsen" class="social-link" target="_blank" title="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
            </a>
            <a href="https://facebook.com/rekabsen" class="social-link" target="_blank" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
            <a href="https://twitter.com/rekabsen" class="social-link" target="_blank" title="Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                </svg>
            </a>
            <a href="https://github.com/rekabsen" class="social-link" target="_blank" title="GitHub">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                </svg>
            </a>
        </div>
        
        <div class="copyright">
            <p>&copy; 2025 REKABSEN. Hak Cipta Dilindungi.</p>
        </div>
    </div>
    
    <script>
    // Fungsi untuk update countdown setiap detik
    function updateCountdown() {
        // Waktu saat ini
        const now = new Date();
        
        // TARGET WAKTU PELUNCURAN (SILAHKAN EDIT BAGIAN INI)
        // Format: Tahun, Bulan (0-11), Tanggal, Jam, Menit
        // Contoh: 2 September 2025 pukul 10:00
        const target = new Date(2025, 7, 22, 13, 0); 
        // Catatan: Bulan dimulai dari 0 (0=Januari, 11=Desember)
        // ----------------------------------------------------------
        
        // Hitung selisih waktu antara sekarang dan target
        const diff = target - now;
        
        // Jika waktu peluncuran sudah lewat
        if (diff <= 0) {
            document.getElementById('days').textContent = '00';
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }
        
        // Hitung hari, jam, menit, detik dari selisih waktu
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Update tampilan countdown di halaman
        document.getElementById('days').textContent = days.toString().padStart(2, '0');
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    }
    
    // Jalankan fungsi updateCountdown setiap 1 detik
    setInterval(updateCountdown, 1000);
    
    // Jalankan sekali saat pertama kali halaman dimuat
    updateCountdown();
    </script>
</body>
</html>