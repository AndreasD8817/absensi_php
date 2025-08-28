<?php
// Pastikan sesi dimulai jika belum, untuk mengakses variabel SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Memuat helper CSRF untuk keamanan form
require_once __DIR__ . '/../../config/csrf_helper.php';
?>
<div class="modal fade" id="modalPengaturan" tabindex="-1" aria-labelledby="modalPengaturanLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPengaturanLabel"><i class="bi bi-gear-fill me-2"></i>Pengaturan Akun</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <!-- Form untuk mengubah username dan password -->
        <form action="/proses-profil" method="POST" id="formPengaturanAkun">
          <?php csrf_input_field(); // Menambahkan token CSRF tersembunyi ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <hr>
            <p class="text-muted small">Isi bagian di bawah ini hanya jika Anda ingin mengubah password.</p>
            <div class="mb-3">
                <label for="password_lama" class="form-label">Password Lama</label>
                <input type="password" class="form-control" id="password_lama" name="password_lama" placeholder="Masukkan password saat ini">
            </div>
            <div class="mb-3">
                <label for="password_baru" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="password_baru" name="password_baru" placeholder="Masukkan password baru">
            </div>
            <div class="mb-3">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru">
            </div>
        </form>

      </div>
      <!-- ====================================================== -->
      <!-- ============ PERBAIKAN TATA LETAK TOMBOL ============= -->
      <!-- ====================================================== -->
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <!-- Grup Tombol Kiri (Hanya Tampil untuk Superadmin ID 1) -->
        <div>
            <?php
            if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin' && $_SESSION['id_pegawai'] == 1) :
            ?>
                <a href="/admin" class="btn btn-primary">
                    <i class="bi bi-shield-lock-fill"></i> Admin
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Grup Tombol Kanan -->
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" form="formPengaturanAkun" class="btn btn-success">
                <i class="bi bi-save-fill"></i> Simpan
            </button>
        </div>
      </div>
    </div>
  </div>
</div>
