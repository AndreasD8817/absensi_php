<?php
// c:\laragon\www\absensi_php\admin\jam_kerja.php
$page_title = 'Kelola Jam Kerja Khusus';
require_once 'partials/header.php';

// Keamanan: Hanya Admin/Superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    echo "<script>window.location='/admin';</script>";
    exit();
}

// --- PROSES TAMBAH / EDIT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    
    // Jam Kerja Senin - Kamis
    $jam_masuk_wd = $_POST['jam_masuk_wd'];
    $jam_pulang_wd = $_POST['jam_pulang_wd'];
    
    // Jam Kerja Jumat
    $jam_masuk_fri = $_POST['jam_masuk_fri'];
    $jam_pulang_fri = $_POST['jam_pulang_fri'];
    
    $keterangan = $_POST['keterangan'];

    // Validasi tanggal
    if ($tanggal_akhir < $tanggal_awal) {
        echo "<div class='alert alert-danger'>Tanggal akhir tidak boleh lebih kecil dari tanggal awal.</div>";
    } else {
        // Loop dari tanggal awal sampai akhir
        $begin = new DateTime($tanggal_awal);
        $end = new DateTime($tanggal_akhir);
        $end->modify('+1 day'); // Agar tanggal akhir ikut terhitung

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);
        
        $sukses_count = 0;

        foreach ($period as $dt) {
            $tgl_loop = $dt->format('Y-m-d');
            $hari_angka = $dt->format('N'); // 1 (Senin) - 7 (Minggu)

            // Skip Sabtu (6) dan Minggu (7)
            if ($hari_angka == 6 || $hari_angka == 7) {
                continue;
            }

            // Tentukan jam berdasarkan hari
            $jam_masuk_fix = ($hari_angka == 5) ? $jam_masuk_fri : $jam_masuk_wd;
            $jam_pulang_fix = ($hari_angka == 5) ? $jam_pulang_fri : $jam_pulang_wd;

            // Gunakan INSERT ... ON DUPLICATE KEY UPDATE
            $sql = "INSERT INTO tabel_jam_kerja (tanggal, jam_masuk, jam_pulang, keterangan) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE jam_masuk = VALUES(jam_masuk), jam_pulang = VALUES(jam_pulang), keterangan = VALUES(keterangan)";
            
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $tgl_loop, $jam_masuk_fix, $jam_pulang_fix, $keterangan);
            
            if (mysqli_stmt_execute($stmt)) {
                $sukses_count++;
            }
        }

        echo "<div class='alert alert-success'>Berhasil menyimpan jam kerja khusus untuk $sukses_count hari kerja (Senin-Jumat). Sabtu & Minggu dilewati.</div>";
    }
}

// --- PROSES HAPUS ---
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $sql_hapus = "DELETE FROM tabel_jam_kerja WHERE id = ?";
    $stmt_hapus = mysqli_prepare($koneksi, $sql_hapus);
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_hapus);
    if (mysqli_stmt_execute($stmt_hapus)) {
        echo "<script>window.location='/admin/jam-kerja';</script>"; // Sesuaikan routing jika perlu
    }
}

// --- AMBIL DATA ---
$bulan_ini = date('m');
$tahun_ini = date('Y');
if (isset($_GET['bulan']) && isset($_GET['tahun'])) {
    $bulan_ini = $_GET['bulan'];
    $tahun_ini = $_GET['tahun'];
}

$sql_data = "SELECT * FROM tabel_jam_kerja WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC";
$stmt_data = mysqli_prepare($koneksi, $sql_data);
mysqli_stmt_bind_param($stmt_data, "ss", $bulan_ini, $tahun_ini);
mysqli_stmt_execute($stmt_data);
$result_data = mysqli_stmt_get_result($stmt_data);
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-clock-history"></i> Atur Jam Kerja Khusus (Ramadhan/Event)</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Form Input -->
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Input Jam Kerja (Rentang)</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="tanggal_awal" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="tanggal_akhir" class="form-control" required>
                            </div>
                            
                            <hr>
                            <h6 class="text-primary">Senin - Kamis</h6>
                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label small">Masuk</label>
                                    <input type="time" name="jam_masuk_wd" class="form-control" value="08:00" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Pulang</label>
                                    <input type="time" name="jam_pulang_wd" class="form-control" value="15:00" required>
                                </div>
                            </div>

                            <h6 class="text-success">Jumat</h6>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label small">Masuk</label>
                                    <input type="time" name="jam_masuk_fri" class="form-control" value="08:00" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Pulang</label>
                                    <input type="time" name="jam_pulang_fri" class="form-control" value="15:30" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Puasa Ramadhan">
                            </div>
                            <button type="submit" name="simpan" class="btn btn-primary w-100"><i class="bi bi-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="col-md-8">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-auto">
                        <select name="bulan" class="form-select">
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php if($m==$bulan_ini) echo 'selected'; ?>><?php echo date('F', mktime(0,0,0,$m, 1, date('Y'))); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="tahun" class="form-select">
                            <?php for($y=date('Y')-1; $y<=date('Y')+1; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php if($y==$tahun_ini) echo 'selected'; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result_data)): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo date('H:i', strtotime($row['jam_masuk'])); ?></td>
                                <td><?php echo date('H:i', strtotime($row['jam_pulang'])); ?></td>
                                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                <td>
                                    <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengaturan tanggal ini?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
