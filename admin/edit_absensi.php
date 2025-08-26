<?php 
$page_title = 'Kelola Absensi Pegawai';
require_once 'partials/header.php';

// Keamanan ekstra, hanya untuk superadmin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /admin?error=Akses ditolak.");
    exit();
}

// "Penjaga Gerbang" - Hanya admin & superadmin yang bisa akses
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /login?error=Akses ditolak");
    exit();
}

// --- AMBIL SEMUA DATA PEGAWAI UNTUK DROPDOWN ---
$pegawai_list = [];
// --- PERUBAHAN DI SINI: Menghapus filter status = 'aktif' dari query ---
$sql_pegawai = "SELECT id_pegawai, nama_lengkap FROM tabel_pegawai ORDER BY nama_lengkap ASC";
$result_pegawai_list = mysqli_query($koneksi, $sql_pegawai);
while($row = mysqli_fetch_assoc($result_pegawai_list)) {
    $pegawai_list[] = $row;
}

// --- LOGIKA UNTUK MENGAMBIL DATA ABSENSI JIKA FILTER DI-SUBMIT ---
$data_absensi = null;
$id_pegawai_terpilih = null;
$tanggal_terpilih = null;

if (isset($_GET['id_pegawai']) && isset($_GET['tanggal'])) {
    $id_pegawai_terpilih = (int)$_GET['id_pegawai'];
    $tanggal_terpilih = $_GET['tanggal'];

    if ($id_pegawai_terpilih > 0 && !empty($tanggal_terpilih)) {
        $sql_absensi = "
            SELECT 
                a.id_absensi, a.tipe_absensi, a.waktu_absensi, a.catatan,
                dl.id_dinas, dl.file_surat_tugas, dl.keterangan as keterangan_dl
            FROM 
                tabel_absensi a
            LEFT JOIN 
                tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi
            WHERE 
                a.id_pegawai = ? AND DATE(a.waktu_absensi) = ?
        ";
        $stmt_absensi = mysqli_prepare($koneksi, $sql_absensi);
        mysqli_stmt_bind_param($stmt_absensi, "is", $id_pegawai_terpilih, $tanggal_terpilih);
        mysqli_stmt_execute($stmt_absensi);
        $result_absensi = mysqli_stmt_get_result($stmt_absensi);
        
        // Inisialisasi array untuk menampung data
        $data_absensi = [
            'masuk' => null,
            'pulang' => null,
            'dinas_luar' => null,
            'is_alpha' => true // Asumsikan Alpha sampai ditemukan data
        ];

        while($row_absensi = mysqli_fetch_assoc($result_absensi)) {
            $data_absensi['is_alpha'] = false; // Ada data absensi, jadi bukan Alpha
            if ($row_absensi['tipe_absensi'] == 'Masuk') {
                $data_absensi['masuk'] = $row_absensi;
            } elseif ($row_absensi['tipe_absensi'] == 'Pulang') {
                $data_absensi['pulang'] = $row_absensi;
            } elseif ($row_absensi['tipe_absensi'] == 'Dinas Luar') {
                $data_absensi['dinas_luar'] = $row_absensi;
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0"><i class="bi bi-pencil-square"></i> Kelola & Edit Absensi Pegawai</h4>
    </div>
    <div class="card-body">
        <?php if(isset($_GET['sukses'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['sukses']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto px-2 h6">Pilih Pegawai dan Tanggal</legend>
            <form method="GET" action="" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="id_pegawai" class="form-label">Nama Pegawai</label>
                        <select class="form-select" id="id_pegawai" name="id_pegawai" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach ($pegawai_list as $pegawai): ?>
                                <option value="<?php echo $pegawai['id_pegawai']; ?>" <?php if($id_pegawai_terpilih == $pegawai['id_pegawai']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="tanggal" class="form-label">Tanggal Absensi</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($tanggal_terpilih ?? date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                    </div>
                </div>
            </form>
        </fieldset>
        
        <?php if ($data_absensi): ?>
        <fieldset class="border p-3">
             <legend class="w-auto px-2 h6">Form Edit</legend>
            <form action="/admin/proses/proses-edit-absensi" method="POST" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <input type="hidden" name="id_pegawai" value="<?php echo $id_pegawai_terpilih; ?>">
                <input type="hidden" name="tanggal" value="<?php echo $tanggal_terpilih; ?>">

                <div class="mb-3">
                    <label class="form-label">Status Absensi</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_absensi" id="statusHadir" value="hadir" <?php echo !$data_absensi['dinas_luar'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="statusHadir">Hadir</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_absensi" id="statusDL" value="dinas_luar" <?php echo $data_absensi['dinas_luar'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="statusDL">Dinas Luar</label>
                    </div>
                     <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_absensi" id="statusAlpha" value="alpha" <?php echo $data_absensi['is_alpha'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="statusAlpha">Tidak Hadir (Alpha)</label>
                    </div>
                </div>

                <div id="form-hadir">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="jam_masuk" class="form-label">Jam Masuk</label>
                            <input type="time" class="form-control" name="jam_masuk" id="jam_masuk" value="<?php echo $data_absensi['masuk'] ? date('H:i', strtotime($data_absensi['masuk']['waktu_absensi'])) : ''; ?>" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="catatan_masuk" class="form-label">Catatan Masuk</label>
                            <input type="text" class="form-control" name="catatan_masuk" id="catatan_masuk" value="<?php echo htmlspecialchars($data_absensi['masuk']['catatan'] ?? 'Absen Masuk by Admin'); ?>">
                        </div>
                    </div>
                     <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="jam_pulang" class="form-label">Jam Pulang</label>
                            <input type="time" class="form-control" name="jam_pulang" id="jam_pulang" value="<?php echo $data_absensi['pulang'] ? date('H:i', strtotime($data_absensi['pulang']['waktu_absensi'])) : ''; ?>" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="catatan_pulang" class="form-label">Catatan Pulang</label>
                            <input type="text" class="form-control" name="catatan_pulang" id="catatan_pulang" value="<?php echo htmlspecialchars($data_absensi['pulang']['catatan'] ?? 'Absen Pulang by Admin'); ?>">
                        </div>
                    </div>
                </div>

                <div id="form-dinas-luar" class="mt-3" style="display:none;">
                    <div class="mb-3">
                        <label for="keterangan_dl" class="form-label">Keterangan Dinas Luar</label>
                        <input type="text" class="form-control" name="keterangan_dl" id="keterangan_dl" value="<?php echo htmlspecialchars($data_absensi['dinas_luar']['keterangan_dl'] ?? 'Dinas Luar by Admin'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="file_dl" class="form-label">Upload Bukti Baru (PDF/JPG, maks 2MB)</label>
                        <input type="file" class="form-control" name="file_dl" id="file_dl" accept=".pdf,.jpg,.jpeg">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah file bukti yang sudah ada.</div>
                        <?php if ($data_absensi['dinas_luar'] && $data_absensi['dinas_luar']['file_surat_tugas']): ?>
                            <div class="mt-2">
                                Bukti saat ini: 
                                <a href="/public/uploads/foto_dinas_luar/<?php echo htmlspecialchars($data_absensi['dinas_luar']['file_surat_tugas']); ?>" target="_blank">
                                    <i class="bi bi-file-earmark-text"></i> Lihat File
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
                </div>
            </form>
        </fieldset>
        <?php endif; ?>
    </div>
</div>

<script>
// Script untuk menampilkan/menyembunyikan form berdasarkan status
document.addEventListener('DOMContentLoaded', function() {
    const radioHadir = document.getElementById('statusHadir');
    const radioDL = document.getElementById('statusDL');
    const radioAlpha = document.getElementById('statusAlpha');
    const formHadir = document.getElementById('form-hadir');
    const formDinasLuar = document.getElementById('form-dinas-luar');

    function toggleForms() {
        if (radioDL.checked) {
            formHadir.style.display = 'none';
            formDinasLuar.style.display = 'block';
        } else if(radioHadir.checked){
            formHadir.style.display = 'block';
            formDinasLuar.style.display = 'none';
        } else { // Jika Alpha
            formHadir.style.display = 'none';
            formDinasLuar.style.display = 'none';
        }
    }

    // Panggil sekali saat halaman load untuk set state awal
    toggleForms();

    // Tambah event listener untuk setiap radio button
    radioHadir.addEventListener('change', toggleForms);
    radioDL.addEventListener('change', toggleForms);
    radioAlpha.addEventListener('change', toggleForms);
});
</script>


<?php require_once 'partials/footer.php'; ?>