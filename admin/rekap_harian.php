<?php
// admin/rekap_harian.php
$page_title = "Rekap Absensi Harian"; // Set page title
require_once 'partials/header.php'; // Include header which handles session and database

// Ambil data pegawai yang aktif
$query_pegawai = "SELECT id_pegawai, nama_lengkap, nip FROM tabel_pegawai WHERE status = 'aktif' ORDER BY nama_lengkap ASC";
$result_pegawai = mysqli_query($koneksi, $query_pegawai); 
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Rekap Absensi Harian (PDF)</h6>
        </div>
        <div class="card-body">
            <form action="proses/proses_rekap_harian_pdf.php" method="POST" target="_blank">
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="tanggal_awal">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_akhir">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label class="font-weight-bold">Pilih Pegawai:</label>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-info" id="selectAll">Pilih Semua</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">Hapus Semua</button>
                    </div>
                    
                    <div class="row" style="max-height: 400px; overflow-y: auto; border: 1px solid #e3e6f0; padding: 15px; border-radius: 5px;">
                        <?php if (mysqli_num_rows($result_pegawai) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_pegawai)): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-pegawai" 
                                               id="pegawai_<?php echo $row['id_pegawai']; ?>" 
                                               name="pegawai[]" 
                                               value="<?php echo $row['id_pegawai']; ?>">
                                        <label class="custom-control-label" for="pegawai_<?php echo $row['id_pegawai']; ?>">
                                            <?php echo htmlspecialchars($row['nama_lengkap']); ?> 
                                            <small class="text-muted">(<?php echo $row['nip'] ?: '-'; ?>)</small>
                                        </label>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted">Tidak ada data pegawai aktif.</div>
                        <?php endif; ?>
                    </div>
                    <small class="text-danger">* Wajib memilih minimal satu pegawai.</small>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" name="cetak_pdf" class="btn btn-success btn-block">
                        <i class="fas fa-file-pdf"></i> Generate PDF Rekap Harian
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const checkboxes = document.querySelectorAll('.check-pegawai');

    selectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = true);
    });

    deselectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
    });
});
</script>

<?php include 'partials/footer.php'; ?>