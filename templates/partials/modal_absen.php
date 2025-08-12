<div class="modal fade" id="modalAbsen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAbsenLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formAbsen" onsubmit="kirimAbsensi(event)">
            <input type="hidden" id="absenTipe" name="tipe">
            <input type="hidden" id="fotoBase64" name="foto">
            <div class="row mb-3">
              <div class="mb-3">
                  <label class="form-label">Lokasi Terdeteksi</label>
                  <p id="alamatTerdeteksi" class="form-control-plaintext text-muted"><i>Menunggu data lokasi...</i></p>
              </div>
                <div class="col-md-6">
                    <label class="form-label">Preview Kamera</label>
                    <video id="videoElement" width="100%" height="auto" autoplay playsinline></video>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-info" id="btnSwitchCamera">Ganti Kamera</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hasil Foto</label>
                    <canvas id="previewCanvas" style="display:none;"></canvas> 
                    <img id="previewImage" src="#" alt="Hasil Foto" style="display:none; width: 100%; height: auto;">
                    <p id="noFotoText" class="text-muted">Belum ada foto diambil.</p>
                </div>
            </div>
            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Wajib Diisi)</label>
                <textarea class="form-control" id="catatan" name="catatan" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnTakePhoto">Ambil Foto</button>
                <button type="submit" class="btn btn-success" id="btnSubmitAbsen" disabled>Kirim Absensi</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>