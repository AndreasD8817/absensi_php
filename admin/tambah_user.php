<?php 
$page_title = 'Tambah User Baru';
require_once 'partials/header.php'; 

// Keamanan tambahan, pastikan hanya superadmin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: index.php?error=Akses ditolak.");
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title">Form Tambah User</h4>
    </div>
    <div class="card-body">
        <form action="proses/proses_tambah_user.php" method="POST">
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="pegawai">Pegawai</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>
            <a href="manajemen_user.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan User</button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
