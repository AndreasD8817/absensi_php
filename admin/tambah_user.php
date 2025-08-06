<?php 
$page_title = 'Tambah User Baru';
require_once 'partials/header.php'; 

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak.");
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-person-plus-fill"></i> Form Tambah User</h4>
    </div>
    <div class="card-body">
        <form action="/admin/proses/proses-tambah-user" method="POST">
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
                    <option value="pegawai" selected>Pegawai</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>

            <!-- ====================================================== -->
            <!-- ================ INPUT BARU DITAMBAHKAN ================ -->
            <!-- ====================================================== -->
            <div class="mb-3">
                <label for="radius_absensi" class="form-label">Radius Absensi Khusus (meter)</label>
                <input type="number" class="form-control" id="radius_absensi" name="radius_absensi" placeholder="Contoh: 50">
                <div class="form-text">
                    <i class="bi bi-info-circle"></i> Kosongkan field ini untuk menggunakan radius global dari Pengaturan Umum.
                </div>
            </div>
            <!-- ====================================================== -->
            
            <hr>
            <a href="/admin/manajemen-user" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan User</button>
        </form>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
