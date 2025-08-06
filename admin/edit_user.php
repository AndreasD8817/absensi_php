<?php 
$page_title = 'Edit User';
require_once 'partials/header.php'; 

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak");
    exit();
}

// Validasi ID user dari URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: /admin/manajemen-user?error=ID User tidak valid.");
    exit();
}

$id_user = (int)$_GET['id'];

// Ambil data user dari database, TERMASUK radius_absensi
$sql = "SELECT nama_lengkap, username, jabatan, role, radius_absensi FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika user tidak ditemukan, redirect
if (!$user) {
    header("Location: /admin/manajemen-user?error=User dengan ID tersebut tidak ditemukan.");
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-pencil-square"></i> Form Edit User: <?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
    </div>
    <div class="card-body">
        <form action="/admin/proses/proses-edit-user" method="POST">
            <input type="hidden" name="id_pegawai" value="<?php echo $id_user; ?>">
            
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="form-text"><i class="bi bi-info-circle"></i> Kosongkan jika tidak ingin mengubah password.</div>
            </div>
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($user['jabatan']); ?>">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="pegawai" <?php if($user['role'] == 'pegawai') echo 'selected'; ?>>Pegawai</option>
                    <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="superadmin" <?php if($user['role'] == 'superadmin') echo 'selected'; ?>>Super Admin</option>
                </select>
            </div>

            <!-- ====================================================== -->
            <!-- ================ INPUT BARU DITAMBAHKAN ================ -->
            <!-- ====================================================== -->
            <div class="mb-3">
                <label for="radius_absensi" class="form-label">Radius Absensi Khusus (meter)</label>
                <input type="number" class="form-control" id="radius_absensi" name="radius_absensi" value="<?php echo htmlspecialchars($user['radius_absensi'] ?? ''); ?>" placeholder="Contoh: 50">
                <div class="form-text">
                    <i class="bi bi-info-circle"></i> Kosongkan untuk menggunakan radius global dari Pengaturan Umum.
                </div>
            </div>
            <!-- ====================================================== -->

            <hr>
            <a href="/admin/manajemen-user" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
