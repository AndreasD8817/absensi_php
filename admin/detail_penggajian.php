<?php
$page_title = 'Detail Absensi Pegawai';
require_once 'partials/header.php';

// Keamanan: Sekarang bisa diakses oleh Admin dan Super Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    echo "<div class='alert alert-danger'>Akses ditolak.</div>";
    require_once 'partials/footer.php';
    exit();
}

// Validasi input dari URL
if (!isset($_GET['id_pegawai']) || !isset($_GET['awal']) || !isset($_GET['akhir'])) {
    echo "<div class='alert alert-danger'>Parameter tidak lengkap.</div>";
    require_once 'partials/footer.php';
    exit();
}

$id_pegawai = (int)$_GET['id_pegawai'];
$tanggal_awal = $_GET['awal'];
$tanggal_akhir = $_GET['akhir'];

// Ambil nama pegawai
$sql_nama = "SELECT nama_lengkap FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_nama = mysqli_prepare($koneksi, $sql_nama);
mysqli_stmt_bind_param($stmt_nama, "i", $id_pegawai);
mysqli_stmt_execute($stmt_nama);
$result_nama = mysqli_stmt_get_result($stmt_nama);
$pegawai = mysqli_fetch_assoc($result_nama);

if (!$pegawai) {
    echo "<div class='alert alert-danger'>Pegawai tidak ditemukan.</div>";
    require_once 'partials/footer.php';
    exit();
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="card-title mb-0">
                <i class="bi bi-person-lines-fill"></i> 
                Detail Absensi: <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
            </h4>
            <p class="mb-0 text-muted">Periode: <?php echo date('d M Y', strtotime($tanggal_awal)) . ' s/d ' . date('d M Y', strtotime($tanggal_akhir)); ?></p>
        </div>
        <a href="/admin/proses/proses-export-detail-pdf?id_pegawai=<?php echo $id_pegawai; ?>&awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill"></i> Cetak PDF
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Hari, Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Potongan (%)</th>
                        <th class="text-start">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_hadir = 0;
                    $total_persen = 0;
                    $period = new DatePeriod(new DateTime($tanggal_awal), new DateInterval('P1D'), (new DateTime($tanggal_akhir))->modify('+1 day'));
                    $nama_hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

                    foreach ($period as $date) {
                        $tanggal_loop = $date->format('Y-m-d');
                        $hari_angka = $date->format('w');
                        
                        $sql_harian = "SELECT tipe_absensi, waktu_absensi, catatan FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ?";
                        $stmt_harian = mysqli_prepare($koneksi, $sql_harian);
                        mysqli_stmt_bind_param($stmt_harian, "is", $id_pegawai, $tanggal_loop);
                        mysqli_stmt_execute($stmt_harian);
                        $result_harian = mysqli_stmt_get_result($stmt_harian);
                        
                        $data_hari_ini = [];
                        while($row = mysqli_fetch_assoc($result_harian)) {
                            $data_hari_ini[$row['tipe_absensi']] = $row;
                        }

                        $jam_masuk = $data_hari_ini['Masuk']['waktu_absensi'] ?? null;
                        $jam_pulang = $data_hari_ini['Pulang']['waktu_absensi'] ?? null;
                        $keterangan = $data_hari_ini['Masuk']['catatan'] ?? '';
                        if(isset($data_hari_ini['Pulang']['catatan'])) {
                            $keterangan .= ($keterangan ? ' | ' : '') . $data_hari_ini['Pulang']['catatan'];
                        }
                        
                        $status = 'M';
                        $persen_harian = 0;

                        $batas_masuk_str = ''; $batas_pulang_str = '';
                        if ($hari_angka >= 1 && $hari_angka <= 5) { $batas_masuk_str = '07:30:00'; $batas_pulang_str = '16:00:00'; }
                        elseif ($hari_angka == 6) { $batas_masuk_str = '08:00:00'; $batas_pulang_str = '14:00:00'; }

                        if(isset($data_hari_ini['Dinas Luar'])) {
                            $status = 'DL';
                            $keterangan = 'Dinas Luar';
                            $total_hadir++;
                        } elseif (isset($data_hari_ini['Masuk']) || isset($data_hari_ini['Pulang'])) {
                            $status = 'H';
                            $total_hadir++;
                            
                            // --- LOGIKA PERHITUNGAN POTONGAN YANG DIPERBARUI ---
                            if (!empty($batas_masuk_str)) { // Hanya hitung jika ini hari kerja
                                $batas_masuk_dt = new DateTime($tanggal_loop . ' ' . $batas_masuk_str);
                                $batas_pulang_dt = new DateTime($tanggal_loop . ' ' . $batas_pulang_str);
                                
                                // Cek potongan karena tidak absen masuk
                                if (!$jam_masuk) {
                                    $persen_harian += 1.5;
                                } else {
                                    // Cek potongan karena terlambat
                                    $absen_masuk_dt = new DateTime($jam_masuk);
                                    if ($absen_masuk_dt > $batas_masuk_dt) {
                                        $diff = $absen_masuk_dt->diff($batas_masuk_dt);
                                        $menit_telat = ($diff->h * 60) + $diff->i;
                                        if ($menit_telat >= 1 && $menit_telat <= 15) $persen_harian += 0.25;
                                        elseif ($menit_telat >= 16 && $menit_telat <= 60) $persen_harian += 0.5;
                                        elseif ($menit_telat > 60 && $menit_telat <= 120) $persen_harian += 1.0;
                                        elseif ($menit_telat > 120) $persen_harian += 1.5;
                                    }
                                }

                                // Cek potongan karena tidak absen pulang
                                if (!$jam_pulang) {
                                    $persen_harian += 1.5;
                                } else {
                                    // Cek potongan karena pulang cepat
                                    $absen_pulang_dt = new DateTime($jam_pulang);
                                    if ($absen_pulang_dt < $batas_pulang_dt) {
                                        $persen_harian += 1.5;
                                    }
                                }
                            }
                            // --- AKHIR LOGIKA PERHITUNGAN ---
                        }
                        
                        if ($hari_angka == 0) { $status = 'Libur'; }
                        
                        $total_persen += $persen_harian;
                    ?>
                    <tr>
                        <td><?php echo $nama_hari[$hari_angka] . ", " . $date->format('d-m-Y'); ?></td>
                        <td><?php echo $jam_masuk ? date('H:i:s', strtotime($jam_masuk)) : '-'; ?></td>
                        <td><?php echo $jam_pulang ? date('H:i:s', strtotime($jam_pulang)) : '-'; ?></td>
                        <td>
                            <span class="badge <?php if($status=='H') echo 'bg-success'; elseif($status=='DL') echo 'bg-warning text-dark'; elseif($status=='M') echo 'bg-danger'; else echo 'bg-info'; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td><?php echo $persen_harian > 0 ? number_format($persen_harian, 2, ',', '.') . '%' : '-'; ?></td>
                        <td class="text-start"><?php echo htmlspecialchars($keterangan); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot class="table-group-divider fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Total Kehadiran:</td>
                        <td colspan="2"><?php echo $total_hadir; ?> Hari</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end">Total Akumulasi Persentase Potongan:</td>
                        <td colspan="2"><?php echo number_format($total_persen, 2, ',', '.'); ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-secondary" onclick="window.close()">Tutup Tab</button>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
