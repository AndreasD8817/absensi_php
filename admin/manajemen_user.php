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
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="card-title mb-0"><i class="bi bi-people-fill"></i> Manajemen User</h4>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="/admin/tambah-user" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Tambah User Baru</a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aktifkanSemuaModal">
                <i class="bi bi-toggle-on"></i> Aktifkan Semua Pegawai
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#nonaktifkanSemuaModal">
                <i class="bi bi-toggle-off"></i> Nonaktifkan Semua Pegawai
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])) : ?>
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
                        <th>Radius</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php $nomor = 1;
                        while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td class="text-center"><?php echo $nomor++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                <td class="text-center"><span class="badge bg-dark"><?php echo ucfirst($row['role']); ?></span></td>
                                <td class="text-center">
                                    <?php
                                    if (is_null($row['radius_absensi'])) {
                                        echo '<span class="badge bg-info text-dark"><i class="bi bi-globe"></i> Global</span>';
                                    } else {
                                        echo '<span class="badge bg-success"><i class="bi bi-rulers"></i> ' . htmlspecialchars($row['radius_absensi']) . ' Meter</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] == 'aktif') : ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Non-aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/edit-user?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-warning btn-sm" title="Edit User">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if ($_SESSION['id_pegawai'] != $row['id_pegawai']) : ?>
                                        <form action="/admin/proses/ubah-status" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status user ini?');">
                                            <?php csrf_input_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $row['id_pegawai']; ?>">
                                            <?php if ($row['status'] == 'aktif') : ?>
                                                <input type="hidden" name="status" value="non-aktif">
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Non-aktifkan">
                                                    <i class="bi bi-toggle-off"></i>
                                                </button>
                                            <?php else : ?>
                                                <input type="hidden" name="status" value="aktif">
                                                <button type="submit" class="btn btn-success btn-sm" title="Aktifkan">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <form action="/admin/proses/proses-hapus-user" method="POST" style="display:inline;" onsubmit="return confirm('PERINGATAN: Menghapus user juga akan menghapus semua data absensinya. Apakah Anda yakin?');">
                                            <?php csrf_input_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $row['id_pegawai']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus User">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data pengguna.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="aktifkanSemuaModal" tabindex="-1" aria-labelledby="aktifkanSemuaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aktifkanSemuaModalLabel">Konfirmasi Aksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengaktifkan <strong>semua pegawai</strong>?</p>
                <p class="text-info"><strong>Info:</strong> Tindakan ini hanya akan memengaruhi pengguna dengan role 'pegawai'.</p>
            </div>
            <div class="modal-footer">
                <form action="/admin/proses/aktifkan-semua" method="POST">
                    <?php csrf_input_field(); ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ya, Aktifkan Semua</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nonaktifkanSemuaModal" tabindex="-1" aria-labelledby="nonaktifkanSemuaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nonaktifkanSemuaModalLabel">Konfirmasi Aksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menonaktifkan <strong>semua pegawai</strong>?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat diurungkan secara massal. Akun Anda dan akun admin lain tidak akan terpengaruh.</p>
            </div>
            <div class="modal-footer">
                <form action="/admin/proses/nonaktifkan-semua" method="POST">
                    <?php csrf_input_field(); ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Nonaktifkan Semua</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>