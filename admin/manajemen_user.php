<?php 
$page_title = 'Manajemen User';
require_once 'partials/header.php'; 

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: index.php?error=Akses ditolak. Fitur ini hanya untuk Super Admin.");
    exit();
}

// Ambil semua data pengguna untuk ditampilkan, termasuk status
$query = "SELECT id_pegawai, nama_lengkap, username, jabatan, role, status FROM tabel_pegawai ORDER BY nama_lengkap ASC";
$result = mysqli_query($koneksi, $query);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0"><i class="bi bi-people-fill"></i> Manajemen User</h4>
        <a href="tambah_user.php" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Tambah User Baru</a>
    </div>
    <div class="card-body">
        <!-- Tampilkan notifikasi -->
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
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Jabatan</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php $nomor = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $nomor++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($row['role']); ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Non-aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <!-- Tombol aksi tidak akan muncul untuk user yang sedang login -->
                                    <?php if ($_SESSION['id_pegawai'] != $row['id_pegawai']): ?>
                                        <?php if ($row['status'] == 'aktif'): ?>
                                            <a href="proses/proses_ubah_status.php?id=<?php echo $row['id_pegawai']; ?>&status=non-aktif" class="btn btn-secondary btn-sm" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan user ini?');">
                                                <i class="bi bi-toggle-off"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="proses/proses_ubah_status.php?id=<?php echo $row['id_pegawai']; ?>&status=aktif" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan user ini?');">
                                                <i class="bi bi-toggle-on"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="proses/proses_hapus_user.php?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN: Menghapus user juga akan menghapus semua data absensinya. Apakah Anda yakin?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data pengguna.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
