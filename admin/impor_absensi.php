<?php 
$page_title = 'Impor Data Absensi';
require_once 'partials/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-upload"></i> Impor Data dari Mesin Absensi</h4>
    </div>
    <div class="card-body">
        <!-- Tampilkan notifikasi -->
        <?php if(isset($_GET['sukses_masuk'])): ?>
            <div class="alert alert-success">
                Impor berhasil! <?php echo (int)$_GET['sukses_masuk']; ?> data "Scan Masuk" dan <?php echo (int)$_GET['sukses_pulang']; ?> data "Scan Pulang" telah ditambahkan.
                <?php if(isset($_GET['gagal_nip']) && $_GET['gagal_nip'] > 0): ?>
                    <br><strong>Peringatan:</strong> <?php echo (int)$_GET['gagal_nip']; ?> baris data dilewati karena NIP pegawai tidak ditemukan di database.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="alert alert-info">
            <h5 class="alert-heading">Petunjuk Penggunaan:</h5>
            <ol>
                <li>Ekspor data dari mesin absensi Fingerspot dalam format **Text**.</li>
                <li>Pastikan kolom **NIP** pada file ekspor sesuai dengan NIP yang terdaftar di sistem.</li>
                <li>Pilih file teks tersebut pada form di bawah ini dan klik "Proses Impor".</li>
                <li>Sistem akan secara otomatis memasukkan data absensi berdasarkan NIP. Data yang sudah ada tidak akan diimpor ulang.</li>
            </ol>
        </div>
        
        <form action="proses/proses_impor.php" method="POST" enctype="multipart/form-data">
            <?php csrf_input_field(); ?>
            <div class="mb-3">
                <label for="file_absensi" class="form-label">Pilih File Teks Absensi (.txt)</label>
                <input class="form-control" type="file" id="file_absensi" name="file_absensi" accept=".txt" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-gear-fill"></i> Proses Impor
            </button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
