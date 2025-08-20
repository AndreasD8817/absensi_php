<footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">

            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold text-primary">RekAbsen</h5>
                <p>
                    Sistem absensi modern berbasis lokasi untuk meningkatkan kedisiplinan dan efisiensi pegawai.
                </p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Navigasi</h5>
                <p><a href="/dashboard" class="text-white" style="text-decoration: none;">Dashboard</a></p>
                <p><a href="/riwayat-absensi" class="text-white" style="text-decoration: none;">Riwayat Absensi</a></p>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin')): ?>
                    <p><a href="/admin" class="text-white" style="text-decoration: none;">Panel Admin</a></p>
                <?php endif; ?>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Kontak</h5>
                
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-geo-alt-fill me-3 mt-1 flex-shrink-0"></i>
                    <span>Yos Sudarso 18-22, Surabaya, Jawa Timur, ID</span>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-envelope-fill me-3 mt-1 flex-shrink-0"></i>
                    <a href="mailto:support.rekabsen@dprdsby.id" class="text-white" style="text-decoration: none;">support.rekabsen@dprdsby.id</a>
                </div>
                 <!-- <div class="d-flex align-items-start">
                    <i class="bi bi-telephone-fill me-3 mt-1 flex-shrink-0"></i>
                    <a href="https://wa.me/6289667009776" target="_blank" class="text-white" style="text-decoration: none;">+62 812 3456 7890</a>
                </div> -->
                </div>
        </div>

        <hr class="mb-4">

        <div class="text-center">
             <p>
                Copyright &copy;<?php echo date('Y'); ?>
                <a href="#" style="text-decoration: none;">
                    <strong class="text-primary">RekAbsen</strong>
                </a>. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>

<div class="modal fade" id="idleModal" tabindex="-1" aria-labelledby="idleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="idleModalLabel"><i class="bi bi-hourglass-split"></i> Sesi Akan Segera Berakhir</h5>
      </div>
      <div class="modal-body">
        <p>Sesi Anda akan otomatis berakhir karena tidak ada aktivitas.</p>
        <p>Sesi berakhir dalam: <strong id="countdownTimer" class="text-danger">60</strong> detik.</p>
        <p>Klik tombol di bawah untuk melanjutkan sesi Anda.</p>
      </div>
      <div class="modal-footer">
        <a href="/auth/logout" class="btn btn-danger">Logout Sekarang</a>
        <button type="button" class="btn btn-primary" id="stayLoggedInButton">Saya Masih di Sini</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan kode ini hanya berjalan jika pengguna sudah login
    <?php if (isset($_SESSION['id_pegawai'])): ?>
    
    let idleTimer;
    let warningTimer;
    let countdownInterval;
    const idleTimeout = 1800 * 1000; // 30 menit dalam milidetik
    const warningTime = 60 * 1000; // Peringatan muncul 1 menit sebelum timeout

    const idleModal = new bootstrap.Modal(document.getElementById('idleModal'));
    const countdownElement = document.getElementById('countdownTimer');
    const stayLoggedInButton = document.getElementById('stayLoggedInButton');

    function showIdleWarning() {
        let countdown = warningTime / 1000;
        countdownElement.textContent = countdown;
        idleModal.show();
        
        countdownInterval = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                // Logout jika waktu habis
                window.location.href = "/auth/logout"; 
            }
        }, 1000);
    }

    function resetTimers() {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        clearInterval(countdownInterval);

        // Setel ulang timer untuk peringatan
        warningTimer = setTimeout(showIdleWarning, idleTimeout - warningTime);
        
        // Setel ulang timer untuk logout paksa
        idleTimer = setTimeout(function() {
            window.location.href = "/auth/logout";
        }, idleTimeout);
    }
    
    // Fungsi untuk "menyentuh" server dan mereset sesi PHP
    function keepSessionAlive() {
        // Kirim request ringan ke halaman dashboard yang pasti ada
        fetch('/dashboard', { method: 'HEAD' })
            .then(() => {
                console.log('Sesi diperpanjang.');
                idleModal.hide();
                resetTimers();
            });
    }

    // Event listener untuk mereset timer saat ada aktivitas
    window.onload = resetTimers;
    document.onmousemove = resetTimers;
    document.onkeypress = resetTimers;
    document.onclick = resetTimers;
    document.onscroll = resetTimers;
    
    // Event listener untuk tombol di modal
    stayLoggedInButton.addEventListener('click', keepSessionAlive);

    <?php endif; ?>
});
</script>