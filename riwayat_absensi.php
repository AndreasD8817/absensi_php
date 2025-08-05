<?php
session_start();
require_once __DIR__ . '/config/database.php';


// Redirect jika belum login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: /absensi_php/login");
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];

$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');

// --- AMBIL DAFTAR HARI LIBUR ---
$daftar_libur = [];
$sql_libur = "SELECT tanggal, keterangan FROM tabel_hari_libur WHERE tanggal BETWEEN ? AND ?";
$stmt_libur = mysqli_prepare($koneksi, $sql_libur);
mysqli_stmt_bind_param($stmt_libur, "ss", $tanggal_awal, $tanggal_akhir);
mysqli_stmt_execute($stmt_libur);
$result_libur = mysqli_stmt_get_result($stmt_libur);
while($row_libur = mysqli_fetch_assoc($result_libur)) {
    $daftar_libur[$row_libur['tanggal']] = $row_libur['keterangan'];
}

// --- LOGIKA PAGINATION ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$period = new DatePeriod(
    new DateTime($tanggal_awal),
    new DateInterval('P1D'),
    (new DateTime($tanggal_akhir))->modify('+1 day')
);

$dates = iterator_to_array($period);
$total_records = count($dates);
$total_pages = ceil($total_records / $limit);
$dates_on_page = array_slice($dates, $offset, $limit);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="/absensi_php/dashboard">Aplikasi Absensi</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a class="nav-link" href="/absensi_php/auth/logout">Logout</a>
        </li>
    </ul>
  </div>
</nav>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Riwayat Absensi Anda</h3>
        </div>
        <div class="card-body">
            <!-- Form Filter tidak berubah -->
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
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-dark">
                        <!-- Header tabel tidak berubah -->
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Hari, Tanggal</th>
                            <th rowspan="2" class="align-middle">Jam Kerja</th>
                            <th rowspan="2" class="align-middle">Jam Masuk</th>
                            <th colspan="2">Terlambat</th>
                            <th rowspan="2" class="align-middle">Jam Pulang</th>
                            <th colspan="2">Cepat Pulang</th>
                            <th rowspan="2" class="align-middle">Status</th>
                            <th rowspan="2" class="align-middle">Keterangan Kegiatan</th>
                        </tr>
                        <tr>
                            <th>Jam</th>
                            <th>Menit</th>
                            <th>Jam</th>
                            <th>Menit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dates_on_page)): ?>
                            <tr><td colspan="11">Tidak ada data pada rentang tanggal ini.</td></tr>
                        <?php else: ?>
                            <?php 
                            $nomor = $offset + 1;
                            $nama_hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                            foreach ($dates_on_page as $date): 
                                $tanggal_loop = $date->format('Y-m-d');
                                $hari_angka = $date->format('w');

                                $is_libur_nasional = isset($daftar_libur[$tanggal_loop]);
                                $keterangan_libur = $is_libur_nasional ? $daftar_libur[$tanggal_loop] : '';

                                $jam_kerja_mulai = '';
                                $jam_kerja_selesai = '';
                                if ($is_libur_nasional) {
                                    $jam_kerja_mulai = $keterangan_libur;
                                } else {
                                    switch ($hari_angka) {
                                        case 1: case 2: case 3: case 4:
                                            $jam_kerja_mulai = '07:30:00'; $jam_kerja_selesai = '16:00:00';
                                            break;
                                        case 5:
                                            $jam_kerja_mulai = '07:30:00'; $jam_kerja_selesai = '16:00:00';
                                            break;
                                        case 6:
                                            $jam_kerja_mulai = '09:00:00'; $jam_kerja_selesai = '14:00:00';
                                            break;
                                        default:
                                            $jam_kerja_mulai = 'Libur';
                                    }
                                }

                                // --- PERBAIKAN 1: Query SQL diubah menggunakan LEFT JOIN ---
                                $sql = "SELECT
                                            a.tipe_absensi,
                                            a.waktu_absensi,
                                            a.catatan,
                                            dl.keterangan AS keterangan_dl
                                        FROM
                                            tabel_absensi a
                                        LEFT JOIN
                                            tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi
                                        WHERE
                                            a.id_pegawai = ? AND DATE(a.waktu_absensi) = ?
                                        ORDER BY
                                            a.waktu_absensi ASC";
                                $stmt = mysqli_prepare($koneksi, $sql);
                                mysqli_stmt_bind_param($stmt, "is", $id_pegawai, $tanggal_loop);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);

                                $absen_masuk = null; $catatan_masuk = '';
                                $absen_pulang = null; $catatan_pulang = '';
                                $dinas_luar = false; $catatan_dinas_luar = ''; // Variabel baru

                                while($row = mysqli_fetch_assoc($result)) {
                                    if ($row['tipe_absensi'] == 'Masuk') {
                                        $absen_masuk = new DateTime($row['waktu_absensi']);
                                        $catatan_masuk = $row['catatan'];
                                    } elseif ($row['tipe_absensi'] == 'Pulang') {
                                        $absen_pulang = new DateTime($row['waktu_absensi']);
                                        $catatan_pulang = $row['catatan'];
                                    } elseif ($row['tipe_absensi'] == 'Dinas Luar') {
                                        $dinas_luar = true;
                                        // PERBAIKAN 2: Simpan keterangan DL ke variabel baru
                                        $catatan_dinas_luar = $row['keterangan_dl'];
                                    }
                                }

                                $status = ''; $terlambat_jam = 0; $terlambat_menit = 0; $cepat_pulang_jam = 0; $cepat_pulang_menit = 0;

                                if ($dinas_luar) {
                                    $status = 'DL';
                                } elseif ($absen_masuk || $absen_pulang) {
                                    $status = 'H';
                                    if ($absen_masuk && $jam_kerja_selesai != '') {
                                        $batas_masuk = new DateTime($tanggal_loop . ' ' . $jam_kerja_mulai);
                                        if ($absen_masuk > $batas_masuk) { $diff = $absen_masuk->diff($batas_masuk); $terlambat_jam = $diff->h; $terlambat_menit = $diff->i; }
                                    }
                                    if ($absen_pulang && $jam_kerja_selesai != '') {
                                        $batas_pulang = new DateTime($tanggal_loop . ' ' . $jam_kerja_selesai);
                                        if ($absen_pulang < $batas_pulang) { $diff = $absen_pulang->diff($batas_pulang); $cepat_pulang_jam = $diff->h; $cepat_pulang_menit = $diff->i; }
                                    }
                                } else {
                                    $status = ($jam_kerja_mulai == 'Libur' || $is_libur_nasional) ? 'Libur' : 'M';
                                }
                            ?>
                            <tr>
                                <td><?php echo $nomor++; ?></td>
                                <td><?php echo $nama_hari[$hari_angka] . ",<br>" . $date->format('d-m-Y'); ?></td>
                                <td class="<?php if($is_libur_nasional || $hari_angka == 0) echo 'text-danger'; ?>">
                                    <?php echo $jam_kerja_selesai != '' ? $jam_kerja_mulai . ' - ' . $jam_kerja_selesai : $jam_kerja_mulai; ?>
                                </td>
                                <td><?php echo $dinas_luar ? '-' : ($absen_masuk ? $absen_masuk->format('H:i:s') : '-'); ?></td>
                                <td><?php echo $dinas_luar ? '-' : $terlambat_jam; ?></td>
                                <td><?php echo $dinas_luar ? '-' : $terlambat_menit; ?></td>
                                <td><?php echo $dinas_luar ? '-' : ($absen_pulang ? $absen_pulang->format('H:i:s') : '-'); ?></td>
                                <td><?php echo $dinas_luar ? '-' : $cepat_pulang_jam; ?></td>
                                <td><?php echo $dinas_luar ? '-' : $cepat_pulang_menit; ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                            if($status == 'H') echo 'bg-success';
                                            elseif($status == 'DL') echo 'bg-warning text-dark';
                                            elseif($status == 'M') echo 'bg-danger';
                                            else echo 'bg-info';
                                        ?>"><?php echo $status; ?></span>
                                </td>
                                <!-- PERBAIKAN 3: Logika tampilan keterangan -->
                                <td>
                                    <?php
                                    if ($dinas_luar) {
                                        echo htmlspecialchars($catatan_dinas_luar);
                                    } else {
                                        echo htmlspecialchars($catatan_masuk . ($catatan_pulang ? ' - ' . $catatan_pulang : ''));
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination tidak berubah -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="?awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>