<?php 
$page_title = 'Pengaturan Aplikasi';
require_once 'partials/header.php'; 

// Keamanan ekstra, hanya untuk superadmin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak.");
    exit();
}

// Ambil semua data pengaturan saat ini dari database
$pengaturan = [];
$sql = "SELECT nama_pengaturan, nilai_pengaturan FROM tabel_pengaturan";
$result = mysqli_query($koneksi, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $pengaturan[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-gear-fill"></i> Pengaturan Absensi Global</h4>
    </div>
    <div class="card-body">
        <!-- Tampilkan notifikasi -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="/admin/proses/proses-pengaturan" method="POST">
            <div class="mb-3">
                <label for="lokasi_lat" class="form-label">Latitude Lokasi Kantor</label>
                <input type="text" class="form-control" id="lokasi_lat" name="lokasi_lat" value="<?php echo htmlspecialchars($pengaturan['lokasi_lat'] ?? ''); ?>" required>
                <div class="form-text">Dapatkan dari Google Maps. Contoh: -7.263062</div>
            </div>
            <div class="mb-3">
                <label for="lokasi_lon" class="form-label">Longitude Lokasi Kantor</label>
                <input type="text" class="form-control" id="lokasi_lon" name="lokasi_lon" value="<?php echo htmlspecialchars($pengaturan['lokasi_lon'] ?? ''); ?>" required>
                <div class="form-text">Dapatkan dari Google Maps. Contoh: 112.745645</div>
            </div>
            <div class="mb-3">
                <label for="radius_meter" class="form-label">Radius Jarak Absensi (dalam meter)</label>
                <input type="number" class="form-control" id="radius_meter" name="radius_meter" value="<?php echo htmlspecialchars($pengaturan['radius_meter'] ?? '100'); ?>" required>
                <div class="form-text">Jarak toleransi maksimal pegawai bisa melakukan absensi dari lokasi kantor.</div>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
