<?php 
$page_title = 'Impor Data Pegawai';
require_once 'partials/header.php'; 

// Keamanan ekstra, hanya untuk superadmin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /absensi_php/login?error=Akses ditolak.");
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-person-plus-fill"></i> Impor Data Pegawai dari Mesin</h4>
    </div>
    <div class="card-body">
        <!-- Tampilkan notifikasi -->
        <?php if(isset($_GET['ditambah'])): ?>
            <div class="alert alert-success">
                Impor selesai! <strong><?php echo (int)$_GET['ditambah']; ?> pegawai baru ditambahkan</strong> dan <strong><?php echo (int)$_GET['diupdate']; ?> data pegawai diperbarui</strong>.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="alert alert-info">
            <h5 class="alert-heading">Petunjuk Penggunaan:</h5>
            <ul>
                <li>Fitur ini akan membaca file <code>data pegawai.txt</code> dari mesin absensi.</li>
                <li>Sistem akan menggunakan <strong>NIP</strong> sebagai kunci utama.</li>
                <li>Jika NIP dari file belum ada di database, pegawai baru akan <strong>ditambahkan</strong> dengan password default '000000'.</li>
                <li>Jika NIP sudah ada, data nama dan jabatan pegawai tersebut akan <strong>diperbarui</strong>.</li>
                <li>Pastikan format file adalah teks dengan pemisah titik koma (<code>;</code>).</li>
            </ul>
        </div>
        
        <form action="/absensi_php/admin/proses/impor-pegawai" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file_pegawai" class="form-label">Pilih File Teks Data Pegawai (.txt)</label>
                <input class="form-control" type="file" id="file_pegawai" name="file_pegawai" accept=".txt" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-gear-fill"></i> Proses Impor Pegawai
            </button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
