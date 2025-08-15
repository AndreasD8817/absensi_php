<div class="modal fade" id="modalPanduan" tabindex="-1" aria-labelledby="modalPanduanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalPanduanLabel"><i class="bi bi-book-half me-2"></i> PANDUAN PENGGUNA REKABSEN - WAJIB DIBACA!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light">
        <div class="alert alert-warning border-start border-5 border-danger">
          <h4 class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>PERHATIAN!</h4>
          <p class="fw-semibold mb-0">Halaman ini berisi panduan penting yang HARUS dipahami sebelum menggunakan aplikasi. Kesalahan dalam penggunaan dapat mempengaruhi rekam jejak absensi Anda.</p>
        </div>
        
        <hr class="border-2 border-top border-primary opacity-75">

        <h4 class="fw-bold text-primary mb-3"><i class="bi bi-list-check me-2"></i>A. UNTUK SEMUA PENGGUNA (WAJIB DICERMATI)</h4>
        <p class="fw-semibold text-muted">Fitur-fitur berikut harus dipahami oleh seluruh pengguna aplikasi RekAbsen.</p>

        <div class="accordion" id="panduanAccordion">
            
            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingSatu">
                    <button class="accordion-button bg-primary bg-opacity-10 text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSatu" aria-expanded="true" aria-controls="collapseSatu">
                        <i class="bi bi-door-open-fill me-2"></i> 1. PROSEDUR LOGIN APLIKASI
                    </button>
                </h2>
                <div id="collapseSatu" class="accordion-collapse collapse show" aria-labelledby="headingSatu" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body bg-white">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 py-2"><i class="bi bi-arrow-right-circle-fill text-primary me-2"></i>Buka alamat URL aplikasi melalui browser yang didukung</li>
                            <li class="list-group-item border-0 py-2"><i class="bi bi-arrow-right-circle-fill text-primary me-2"></i>Masukkan <span class="badge bg-primary">Username</span> dan <span class="badge bg-primary">Password</span> yang telah terdaftar</li>
                            <li class="list-group-item border-0 py-2"><i class="bi bi-arrow-right-circle-fill text-primary me-2"></i>Klik tombol <span class="badge bg-success text-white">LOGIN</span> untuk mengakses sistem</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingDua">
                    <button class="accordion-button bg-primary bg-opacity-10 text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDua" aria-expanded="false" aria-controls="collapseDua">
                       <i class="bi bi-camera-fill me-2"></i> 2. TATA CARA ABSENSI (MASUK & PULANG)
                    </button>
                </h2>
                <div id="collapseDua" class="accordion-collapse collapse" aria-labelledby="headingDua" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body bg-white">
                        <div class="alert alert-info border-start border-3 border-info">
                            <strong><i class="bi bi-info-circle-fill me-2"></i>PENTING:</strong> Pastikan GPS dan kamera aktif sebelum melakukan absensi.
                        </div>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 py-2">Pilih menu <span class="badge bg-primary">Absen Masuk</span> atau <span class="badge bg-danger">Absen Pulang</span> di Dashboard</li>
                            <li class="list-group-item border-0 py-2">Izinkan akses <strong>lokasi</strong> dan <strong>kamera</strong> ketika diminta browser</li>
                            <li class="list-group-item border-0 py-2">Tunggu hingga sistem mendeteksi alamat Anda (pastikan tulisan "Menunggu data lokasi..." berubah)</li>
                            <li class="list-group-item border-0 py-2">Ambil foto wajah dengan menekan tombol <span class="badge bg-primary">Ambil Foto</span></li>
                            <li class="list-group-item border-0 py-2">Verifikasi foto yang muncul beserta watermark waktu dan lokasi</li>
                            <li class="list-group-item border-0 py-2"><span class="text-danger fw-bold">WAJIB</span> isi kolom <span class="badge bg-secondary">Catatan</span> dengan aktivitas yang dilakukan</li>
                            <li class="list-group-item border-0 py-2">Tekan <span class="badge bg-success">Kirim Absensi</span> untuk menyimpan data</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingTiga">
                    <button class="accordion-button bg-primary bg-opacity-10 text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTiga" aria-expanded="false" aria-controls="collapseTiga">
                        <i class="bi bi-geo-alt-fill me-2"></i> 3. PROSEDUR ABSENSI DINAS LUAR
                    </button>
                </h2>
                <div id="collapseTiga" class="accordion-collapse collapse" aria-labelledby="headingTiga" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body bg-white">
                        <div class="alert alert-danger border-start border-3 border-danger">
                            <strong><i class="bi bi-exclamation-octagon-fill me-2"></i>PERINGATAN:</strong> Pelanggaran prosedur ini dapat berakibat pada ketidakhadiran yang tidak tercatat.
                        </div>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 py-2">Klik menu <span class="badge bg-info text-dark">Dinas Luar Kota</span> di Dashboard</li>
                            <li class="list-group-item border-0 py-2">Upload <span class="badge bg-dark">Surat Tugas</span> dalam format PDF/JPG (maks. 2MB)</li>
                            <li class="list-group-item border-0 py-2">Isi <span class="badge bg-secondary">Keterangan</span> dengan detail kegiatan dinas</li>
                            <li class="list-group-item border-0 py-2">Tekan tombol <span class="badge bg-success">Kirim</span> untuk mengajukan permohonan</li>
                            <li class="list-group-item border-0 py-2 fw-bold text-danger"><i class="bi bi-lightning-charge-fill me-2"></i>Untuk kegiatan dinas di luar kota >1 hari,pada hari pertama WAJIB kirim PDF/JPG,selanjutnya pada hari berikutnya foto Bergeotag setiap harinya</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingEmpat">
                    <button class="accordion-button bg-primary bg-opacity-10 text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmpat" aria-expanded="false" aria-controls="collapseEmpat">
                        <i class="bi bi-clock-history me-2"></i> 4. MELIHAT RIWAYAT ABSENSI
                    </button>
                </h2>
                <div id="collapseEmpat" class="accordion-collapse collapse" aria-labelledby="headingEmpat" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body bg-white">
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 py-2">Pilih menu <span class="badge bg-success">Data Absensi</span> di bagian "Laporan"</li>
                            <li class="list-group-item border-0 py-2">Sistem akan menampilkan rekapitulasi absensi Anda</li>
                            <li class="list-group-item border-0 py-2">Gunakan filter tanggal untuk melihat periode tertentu</li>
                        </ol>
                        <!-- <div class="mt-3 p-3 bg-light rounded">
                            <i class="bi bi-tip me-2"></i><strong>Tips:</strong> Anda bisa ekspor data ke Excel untuk keperluan pribadi.
                        </div> -->
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingLima">
                    <button class="accordion-button bg-primary bg-opacity-10 text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLima" aria-expanded="false" aria-controls="collapseLima">
                        <i class="bi bi-shield-lock-fill me-2"></i> 5. KEAMANAN AKUN (USERNAME & PASSWORD)
                    </button>
                </h2>
                <div id="collapseLima" class="accordion-collapse collapse" aria-labelledby="headingLima" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body bg-white">
                        <div class="alert alert-warning border-start border-3 border-warning">
                            <strong><i class="bi bi-shield-exclamation me-2"></i>KEAMANAN:</strong> Jangan bagikan kredensial login Anda kepada siapapun.
                        </div>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 py-2">Klik nama Anda di pojok kanan atas, pilih <span class="badge bg-purple">Pengaturan Akun</span></li>
                            <li class="list-group-item border-0 py-2">Untuk ubah username, isi kolom username baru</li>
                            <li class="list-group-item border-0 py-2">Untuk ubah password:
                                <ul class="mt-2">
                                    <li>Isi <span class="badge bg-secondary">Password Lama</span></li>
                                    <li>Masukkan <span class="badge bg-primary">Password Baru</span></li>
                                    <li>Konfirmasi <span class="badge bg-primary">Password Baru</span></li>
                                </ul>
                            </li>
                            <li class="list-group-item border-0 py-2">Jika tidak ingin mengubah password, biarkan kolom password <span class="badge bg-secondary">kosong</span></li>
                            <li class="list-group-item border-0 py-2">Tekan <span class="badge bg-success">Simpan Perubahan</span> untuk menyimpan</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>

      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-danger fw-bold" data-bs-dismiss="modal"><i class="bi bi-x-circle-fill me-2"></i>TUTUP PANDUAN</button>
      </div>
    </div>
  </div>
</div>

<style>
    .bg-purple {
        background-color: #6f42c1;
    }
    .text-purple {
        color: #6f42c1;
    }
    .border-3 {
        border-width:3px !important;
    }
    .accordion-button:not(.collapsed)::after {
        filter: brightness(0) invert(1);
    }
</style>