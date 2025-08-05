<?php 
$page_title = 'Kelola Hari Libur';
require_once 'partials/header.php'; 

// Ambil semua data hari libur untuk ditampilkan
$query = "SELECT tanggal, keterangan FROM tabel_hari_libur ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>

<div class="row">
    <!-- Kolom Form Tambah Libur -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-plus-circle-fill"></i> Tambah Hari Libur</h5>
            </div>
            <div class="card-body">
                <form action="proses/proses_tambah_libur.php" method="POST">
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Contoh: Cuti Bersama Idul Fitri" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Tabel Daftar Libur -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-calendar-check-fill"></i> Daftar Hari Libur & Cuti Bersama</h5>
            </div>
            <div class="card-body">
                <!-- Tampilkan notifikasi sukses atau error -->
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php $nomor = 1; while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $nomor++; ?></td>
                                        <td><?php echo date('d F Y', strtotime($row['tanggal'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        <td>
                                            <a href="proses/proses_hapus_libur.php?tanggal=<?php echo $row['tanggal']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data hari libur.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
