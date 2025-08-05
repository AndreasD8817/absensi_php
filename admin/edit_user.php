<?php 
$page_title = 'Edit User';
require_once 'partials/header.php'; 

// Keamanan tambahan
if ($_SESSION['role'] != 'superadmin') {
    header("Location: index.php?error=Akses ditolak.");
    exit();
}

// Ambil ID dari URL dan dapatkan data user
if (!isset($_GET['id'])) {
    header("Location: manajemen_user.php?error=User tidak ditemukan.");
    exit();
}

$id_user = (int)$_GET['id'];
$sql = "SELECT nama_lengkap, username, jabatan, role FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: manajemen_user.php?error=User tidak ditemukan.");
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Form Edit User: <?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
    </div>
    <div class="card-body">
        <form action="proses/proses_edit_user.php" method="POST">
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
                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
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
            <a href="manajemen_user.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
