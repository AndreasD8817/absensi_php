<?php
$page_title = 'Laporan Penggajian Pegawai';
require_once 'partials/header.php';

// Keamanan: Sekarang bisa diakses oleh Admin dan Super Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /admin?error=Akses ditolak");
    exit();
}

// --- PENGATURAN & FILTER ---
$pengaturan_gaji = [];
$sql_pengaturan = "SELECT nama_pengaturan, nilai_pengaturan FROM tabel_pengaturan WHERE nama_pengaturan IN ('gaji_harian', 'potongan_tetap')";
$result_pengaturan = mysqli_query($koneksi, $sql_pengaturan);
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    $pengaturan_gaji[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}
$gaji_harian_default = $pengaturan_gaji['gaji_harian'] ?? 160700;
$potongan_tetap_default = $pengaturan_gaji['potongan_tetap'] ?? 41300;

$tanggal_awal = isset($_GET['awal']) && !empty($_GET['awal']) ? $_GET['awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) && !empty($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-t');

$sql_pegawai = "SELECT id_pegawai, nama_lengkap FROM tabel_pegawai WHERE role = 'pegawai' ORDER BY nama_lengkap ASC";
$result_pegawai = mysqli_query($koneksi, $sql_pegawai);

$laporan_penggajian = [];

// --- PROSES PERHITUNGAN GAJI ---
while ($pegawai = mysqli_fetch_assoc($result_pegawai)) {
    $id_pegawai = $pegawai['id_pegawai'];

    $sql_absensi = "SELECT waktu_absensi, tipe_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) BETWEEN ? AND ?";
    $stmt_absensi = mysqli_prepare($koneksi, $sql_absensi);
    mysqli_stmt_bind_param($stmt_absensi, "iss", $id_pegawai, $tanggal_awal, $tanggal_akhir);
    mysqli_stmt_execute($stmt_absensi);
    $result_absensi = mysqli_stmt_get_result($stmt_absensi);

    $rekap_harian = [];
    while ($absen = mysqli_fetch_assoc($result_absensi)) {
        $tanggal = date('Y-m-d', strtotime($absen['waktu_absensi']));
        $rekap_harian[$tanggal][$absen['tipe_absensi']] = $absen['waktu_absensi'];
    }

    $daftar_libur = [];
    $sql_libur = "SELECT tanggal FROM tabel_hari_libur WHERE tanggal BETWEEN ? AND ?";
    $stmt_libur = mysqli_prepare($koneksi, $sql_libur);
    mysqli_stmt_bind_param($stmt_libur, "ss", $tanggal_awal, $tanggal_akhir);
    mysqli_stmt_execute($stmt_libur);
    $result_libur = mysqli_stmt_get_result($stmt_libur);
    while($row_libur = mysqli_fetch_assoc($result_libur)) {
        $daftar_libur[] = $row_libur['tanggal'];
    }

    $jumlah_hari_masuk = 0;
    $total_potongan_rupiah = 0;
    $total_persen_potongan_periode = 0;

    $period = new DatePeriod(new DateTime($tanggal_awal), new DateInterval('P1D'), (new DateTime($tanggal_akhir))->modify('+1 day'));

    foreach ($period as $date) {
        $tanggal_loop = $date->format('Y-m-d');
        $hari_angka = $date->format('w');

        $is_hari_kerja = ($hari_angka != 0 && !in_array($tanggal_loop, $daftar_libur));

        if ($is_hari_kerja) {
            $hadir = isset($rekap_harian[$tanggal_loop]['Masuk']) || isset($rekap_harian[$tanggal_loop]['Dinas Luar']) || isset($rekap_harian[$tanggal_loop]['Pulang']);
            if ($hadir) {
                $jumlah_hari_masuk++;
                
                $persen_harian = 0;
                $jam_masuk = $rekap_harian[$tanggal_loop]['Masuk'] ?? null;
                $jam_pulang = $rekap_harian[$tanggal_loop]['Pulang'] ?? null;

                // Hanya hitung potongan jika bukan dinas luar
                if (!isset($rekap_harian[$tanggal_loop]['Dinas Luar'])) {
                    $batas_masuk_str = ($hari_angka >= 1 && $hari_angka <= 5) ? '07:30:00' : '08:00:00';
                    $batas_pulang_str = ($hari_angka >= 1 && $hari_angka <= 5) ? '16:00:00' : '14:00:00';

                    $batas_masuk_dt = new DateTime($tanggal_loop . ' ' . $batas_masuk_str);
                    $batas_pulang_dt = new DateTime($tanggal_loop . ' ' . $batas_pulang_str);
                    
                    if (!$jam_masuk) {
                        $persen_harian += 1.5;
                    } else {
                        // =======================================================
                        // ========= PERUBAHAN LOGIKA TERLAMBAT DIMULAI ==========
                        // =======================================================
                        $absen_masuk_dt = new DateTime($jam_masuk);

                        // Ambil waktu absen dan batas masuk, lalu set detiknya menjadi 00
                        $absen_masuk_menit = clone $absen_masuk_dt;
                        $absen_masuk_menit->setTime($absen_masuk_dt->format('H'), $absen_masuk_dt->format('i'), 0);
                        
                        $batas_masuk_menit = clone $batas_masuk_dt;
                        $batas_masuk_menit->setTime($batas_masuk_dt->format('H'), $batas_masuk_dt->format('i'), 0);

                        // Bandingkan waktu yang sudah dibulatkan ke menit
                        if ($absen_masuk_menit > $batas_masuk_menit) {
                            $diff = $absen_masuk_dt->diff($batas_masuk_dt);
                            $menit_telat = ($diff->h * 60) + $diff->i;
                            if ($menit_telat >= 1 && $menit_telat <= 15) $persen_harian += 0.25;
                            elseif ($menit_telat >= 16 && $menit_telat <= 60) $persen_harian += 0.5;
                            elseif ($menit_telat > 60 && $menit_telat <= 120) $persen_harian += 1.0;
                            elseif ($menit_telat > 120) $persen_harian += 1.5;
                        }
                        // =======================================================
                        // ========= PERUBAHAN LOGIKA TERLAMBAT SELESAI ==========
                        // =======================================================
                    }

                    if (!$jam_pulang) {
                        $persen_harian += 1.5;
                    } else {
                        // =========================================================
                        // ========= PERUBAHAN LOGIKA PULANG CEPAT DIMULAI =========
                        // =========================================================
                        $absen_pulang_dt = new DateTime($jam_pulang);

                        // Ambil waktu absen dan batas pulang, lalu set detiknya menjadi 00
                        $absen_pulang_menit = clone $absen_pulang_dt;
                        $absen_pulang_menit->setTime($absen_pulang_dt->format('H'), $absen_pulang_dt->format('i'), 0);

                        $batas_pulang_menit = clone $batas_pulang_dt;
                        $batas_pulang_menit->setTime($batas_pulang_dt->format('H'), $batas_pulang_dt->format('i'), 0);

                        // Bandingkan waktu yang sudah dibulatkan ke menit
                        if ($absen_pulang_menit < $batas_pulang_menit) {
                            $persen_harian += 1.5;
                        }
                        // =========================================================
                        // ========= PERUBAHAN LOGIKA PULANG CEPAT SELESAI =========
                        // =========================================================
                    }
                }
                
                $total_persen_potongan_periode += $persen_harian;
                $total_potongan_rupiah += ($gaji_harian_default * $persen_harian) / 100;
            }
        }
    }

    $gaji_kotor = $jumlah_hari_masuk * $gaji_harian_default;
    $gaji_bersih = $gaji_kotor - $potongan_tetap_default - $total_potongan_rupiah;

    $laporan_penggajian[] = [
        'id_pegawai' => $id_pegawai,
        'nama_lengkap' => $pegawai['nama_lengkap'],
        'jumlah_hari_masuk' => $jumlah_hari_masuk,
        'gaji_kotor' => $gaji_kotor,
        'potongan_keterlambatan' => $total_potongan_rupiah,
        'total_persen_potongan' => $total_persen_potongan_periode,
        'potongan_tetap' => $potongan_tetap_default,
        'gaji_bersih' => $gaji_bersih
    ];
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-cash-stack"></i> Laporan Penggajian</h4>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="awal" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="awal" name="awal" value="<?php echo htmlspecialchars($tanggal_awal); ?>">
                </div>
                <div class="col-md-4">
                    <label for="akhir" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Tampilkan</button>
                    <a href="/admin/proses/proses-export-pdf?awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>" class="btn btn-danger w-100" target="_blank">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Cetak PDF
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>Hari Masuk</th>
                        <th>Gaji Kotor</th>
                        <th>Total Persen Potongan</th>
                        <th>Potongan (Rp)</th>
                        <th>Potongan Tetap (IURAN JKK JK)</th>
                        <th>Gaji Bersih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_penggajian)): ?>
                        <tr><td colspan="9">Tidak ada data untuk ditampilkan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($laporan_penggajian as $laporan): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="text-start"><?php echo htmlspecialchars($laporan['nama_lengkap']); ?></td>
                            <td><?php echo $laporan['jumlah_hari_masuk']; ?></td>
                            <td>Rp <?php echo number_format($laporan['gaji_kotor'], 0, ',', '.'); ?></td>
                            <td><?php echo number_format($laporan['total_persen_potongan'], 2, ',', '.'); ?>%</td>
                            <td>Rp <?php echo number_format($laporan['potongan_keterlambatan'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($laporan['potongan_tetap'], 0, ',', '.'); ?></td>
                            <td class="fw-bold">Rp <?php echo number_format($laporan['gaji_bersih'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="/admin/detail-penggajian?id_pegawai=<?php echo $laporan['id_pegawai']; ?>&awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>" class="btn btn-info btn-sm" target="_blank" title="Lihat Detail Absensi">
                                    <i class="bi bi-eye-fill"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>