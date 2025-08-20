</main> <footer class="mt-auto py-3 bg-light">
    <div class="container text-center">
        <hr>
        <p class="text-muted mt-3">
            &copy; <?php echo date('Y'); ?> <strong>Aplikasi Absensi (RekAbsen)</strong> - Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> oleh Super Koor Arvin.
        </p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
                window.location.href = "/auth/logout"; // Logout jika waktu habis
            }
        }, 1000);
    }

    function resetTimers() {
        // Hapus semua timer yang sedang berjalan
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
        fetch('/dashboard', { method: 'HEAD' }) // Kirim request ringan ke halaman yang ada
            .then(() => {
                console.log('Sesi diperpanjang.');
                idleModal.hide();
                resetTimers();
            });
    }

    // Event listener
    window.onload = resetTimers;
    document.onmousemove = resetTimers;
    document.onkeypress = resetTimers;
    document.onclick = resetTimers;
    document.onscroll = resetTimers;
    
    stayLoggedInButton.addEventListener('click', keepSessionAlive);

});
</script>

</body>
</html>