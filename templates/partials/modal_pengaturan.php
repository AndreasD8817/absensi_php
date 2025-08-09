<div class="modal fade" id="modalPengaturan" tabindex="-1" aria-labelledby="modalPengaturanLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPengaturanLabel"><i class="bi bi-gear-fill"></i> Pengaturan Akun</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/proses-profil" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <hr>
            <p class="text-muted small">Kosongkan bagian password jika Anda tidak ingin mengubahnya.</p>
            <div class="mb-3">
                <label for="password_lama" class="form-label">Password Lama (wajib diisi jika ingin ganti password)</label>
                <input type="password" class="form-control" id="password_lama" name="password_lama">
            </div>
            <div class="mb-3">
                <label for="password_baru" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="password_baru" name="password_baru">
            </div>
            <div class="mb-3">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>