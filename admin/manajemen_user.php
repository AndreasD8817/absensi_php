<?php 
$page_title = 'Manajemen User';
require_once 'partials/header.php'; 

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak. Fitur ini hanya untuk Super Admin.");
    exit();
}

// --- QUERY DIPERBARUI ---
// Ambil semua data pengguna, TERMASUK kolom 'radius_absensi'
$query = "SELECT id_pegawai, nama_lengkap, username, jabatan, role, status, radius_absensi FROM tabel_pegawai ORDER BY nama_lengkap ASC";
$result = mysqli_query($koneksi, $query);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0"><i class="bi bi-people-fill"></i> Manajemen User</h4>
        <a href="/admin/tambah-user" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Tambah User Baru</a>
    </div>
    <div class="card-body">
        <!-- Tampilkan notifikasi -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Jabatan</th>
                        <th>Role</th>
                        <!-- KOLOM BARU DITAMBAHKAN -->
                        <th>Radius</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php $nomor = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?php echo $nomor++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class ="text-center"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                <td class="text-center"><span class="badge bg-dark"><?php echo ucfirst($row['role']); ?></span></td>
                                
                                <!-- ====================================================== -->
                                <!-- ============= LOGIKA TAMPILAN RADIUS BARU ============ -->
                                <!-- ====================================================== -->
                                <td class="text-center">
                                    <?php 
                                    if (is_null($row['radius_absensi'])) {
                                        // Jika radius_absensi adalah NULL, tampilkan "Global"
                                        echo '<span class="badge bg-info text-dark"><i class="bi bi-globe"></i> Global</span>';
                                    } else {
                                        // Jika ada nilainya, tampilkan nilainya
                                        echo '<span class="badge bg-success"><i class="bi bi-rulers"></i> ' . htmlspecialchars($row['radius_absensi']) . ' Meter</span>';
                                    }
                                    ?>
                                </td>
                                <!-- ====================================================== -->

                                <td class="text-center">
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Non-aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/edit-user?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-warning btn-sm" title="Edit User">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <!-- Tombol aksi tidak akan muncul untuk user yang sedang login -->
                                    <?php if ($_SESSION['id_pegawai'] != $row['id_pegawai']): ?>
                                        <?php if ($row['status'] == 'aktif'): ?>
                                            <a href="/admin/proses/ubah-status?id=<?php echo $row['id_pegawai']; ?>&status=non-aktif" class="btn btn-secondary btn-sm" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan user ini?');" title="Non-aktifkan">
                                                <i class="bi bi-toggle-off"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="/admin/proses/ubah-status?id=<?php echo $row['id_pegawai']; ?>&status=aktif" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan user ini?');" title="Aktifkan">
                                                <i class="bi bi-toggle-on"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/proses/proses-hapus-user?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN: Menghapus user juga akan menghapus semua data absensinya. Apakah Anda yakin?');" title="Hapus User">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data pengguna.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
