<div class="modal fade" id="modalDinasLuar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Absensi Dinas Luar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formDinasLuar" onsubmit="kirimDinasLuar(event)">
            <div class="mb-3">
                <label for="surat_tugas" class="form-label">Upload Surat Tugas (PDF/JPG, maks 2MB)</label>
                <input class="form-control" type="file" id="surat_tugas" name="surat_tugas" accept=".pdf,.jpg,.jpeg" required>
            </div>
            <div class="mb-3">
                <label for="keterangan_dinas" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan_dinas" name="keterangan" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>