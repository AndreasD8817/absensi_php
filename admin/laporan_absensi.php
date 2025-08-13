<?php
$page_title = 'Laporan Absensi Seluruh Pegawai';
require_once 'partials/header.php';

// --- LOGIKA FILTER ---
$pegawai_list = [];
$sql_pegawai = "SELECT id_pegawai, nama_lengkap FROM tabel_pegawai ORDER BY nama_lengkap ASC";
$result_pegawai = mysqli_query($koneksi, $sql_pegawai);
while($row = mysqli_fetch_assoc($result_pegawai)) {
    $pegawai_list[] = $row;
}

$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$pegawai_id_filter = isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : 0;

// --- PERUBAHAN 1: Kueri SQL diperbarui dengan LEFT JOIN ke tabel_dinas_luar ---
$sql_data = "
    SELECT 
        p.nama_lengkap, 
        a.*,
        dl.file_surat_tugas  -- Ambil nama file surat tugas
    FROM tabel_absensi a 
    JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai 
    LEFT JOIN tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi -- Tambahkan LEFT JOIN
    WHERE DATE(a.waktu_absensi) BETWEEN ? AND ?";
$params = [$tanggal_awal, $tanggal_akhir];
$types = "ss";

if ($pegawai_id_filter != 0) {
    $sql_data .= " AND a.id_pegawai = ?";
    $params[] = $pegawai_id_filter;
    $types .= "i";
}
$sql_data .= " ORDER BY p.nama_lengkap ASC, a.waktu_absensi ASC";

$stmt_data = mysqli_prepare($koneksi, $sql_data);
mysqli_stmt_bind_param($stmt_data, $types, ...$params);
mysqli_stmt_execute($stmt_data);
$result_data = mysqli_stmt_get_result($stmt_data);

// Mengelompokkan data per pegawai per hari
$laporan_harian = [];
while ($row = mysqli_fetch_assoc($result_data)) {
    $tanggal = date('Y-m-d', strtotime($row['waktu_absensi']));
    $id_pegawai = $row['id_pegawai'];
    $key = $id_pegawai . '_' . $tanggal;

    if (!isset($laporan_harian[$key])) {
        $laporan_harian[$key] = [
            'nama_lengkap' => $row['nama_lengkap'],
            'tanggal' => $tanggal,
            'masuk' => null,
            'pulang' => null,
            'dinas_luar' => null
        ];
    }
    // Simpan seluruh baris data ke dalam tipe absensi yang sesuai
    if ($row['tipe_absensi'] == 'Masuk') $laporan_harian[$key]['masuk'] = $row;
    if ($row['tipe_absensi'] == 'Pulang') $laporan_harian[$key]['pulang'] = $row;
    if ($row['tipe_absensi'] == 'Dinas Luar') $laporan_harian[$key]['dinas_luar'] = $row;
}

// --- LOGIKA PAGINATION (Tidak berubah) ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 30;
$total_records = count($laporan_harian);
$total_pages = ceil($total_records / $limit);
$offset = ($page - 1) * $limit;
$laporan_harian_page = array_slice($laporan_harian, $offset, $limit);

// Inisialisasi total (Tidak berubah)
$total_h = 0; $total_m = 0; $total_dl = 0;
$total_terlambat_menit_akumulasi = 0;
$total_cepat_pulang_menit_akumulasi = 0;
$total_persen_potongan = 0;
?>

<style>
    @media print { .no-print { display: none !important; } .card { border: none; box-shadow: none; } .table { font-size: 12px; } }
</style>

<div class="card">
    <div class="card-header no-print">
        <h4 class="card-title"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Laporan Absensi Pegawai</h4>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="mb-4 no-print">
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label for="awal" class="form-label">Tanggal Awal</label><input type="date" class="form-control" id="awal" name="awal" value="<?php echo htmlspecialchars($tanggal_awal); ?>"></div>
                <div class="col-md-3"><label for="akhir" class="form-label">Tanggal Akhir</label><input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>"></div>
                <div class="col-md-4"><label for="pegawai_id" class="form-label">Nama Pegawai</label><select class="form-select" id="pegawai_id" name="pegawai_id"><option value="0">Semua Pegawai</option><?php foreach ($pegawai_list as $pegawai): ?><option value="<?php echo $pegawai['id_pegawai']; ?>" <?php if($pegawai_id_filter == $pegawai['id_pegawai']) echo 'selected'; ?>><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><div class="d-grid gap-2 d-md-flex"><button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter</button><button type="button" class="btn btn-success" onclick="window.print();"><i class="bi bi-printer-fill"></i> Cetak</button></div></div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle">No</th>
                        <th rowspan="2" class="align-middle">Hari, Tanggal</th>
                        <th rowspan="2" class="align-middle">Nama Pegawai</th>
                        <th rowspan="2" class="align-middle">Jam Masuk</th>
                        <th colspan="2">Terlambat</th>
                        <th rowspan="2" class="align-middle">Jam Pulang</th>
                        <th colspan="2">Cepat Pulang</th>
                        <th rowspan="2" class="align-middle">Status</th>
                        <th rowspan="2" class="align-middle">Potongan (%)</th>
                        <th rowspan="2" class="align-middle">Bukti</th>
                    </tr>
                    <tr><th>Jam</th><th>Menit</th><th>Jam</th><th>Menit</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_harian_page)): ?>
                        <tr><td colspan="12">Tidak ada data absensi pada rentang tanggal yang dipilih.</td></tr>
                    <?php else: ?>
                        <?php 
                        $nomor = $offset + 1;
                        $nama_hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                        foreach($laporan_harian_page as $laporan):
                            // Logika inisialisasi (tidak berubah)
                            $date_obj = new DateTime($laporan['tanggal']);
                            $hari_angka = $date_obj->format('w');
                            $absen_masuk = $laporan['masuk'] ? new DateTime($laporan['masuk']['waktu_absensi']) : null;
                            $absen_pulang = $laporan['pulang'] ? new DateTime($laporan['pulang']['waktu_absensi']) : null;
                            $dinas_luar = $laporan['dinas_luar'];
                            $status = ''; $persen = 0; $terlambat_jam = 0; $terlambat_menit = 0; $cepat_pulang_jam = 0; $cepat_pulang_menit = 0;
                            $batas_masuk_str = ''; $batas_pulang_str = '';
                            switch ($hari_angka) {
                                case 1: case 2: case 3: case 4: $batas_masuk_str = '07:30:00'; $batas_pulang_str = '16:00:00'; break;
                                case 5: $batas_masuk_str = '07:30:00'; $batas_pulang_str = '16:00:00'; break;
                                case 6: $batas_masuk_str = '09:00:00'; $batas_pulang_str = '14:00:00'; break;
                            }
                            if ($dinas_luar) {
                                $status = 'DL'; $total_dl++;
                            } elseif ($absen_masuk || $absen_pulang) {
                                $status = 'H'; $total_h++;
                            } else {
                                if (!empty($batas_masuk_str)) { $status = 'M'; $total_m++; } 
                                else { $status = 'Libur'; }
                            }
                            if ($status == 'H' && !empty($batas_masuk_str)) {
                                $batas_masuk_dt = new DateTime($laporan['tanggal'] . ' ' . $batas_masuk_str);
                                $batas_pulang_dt = new DateTime($laporan['tanggal'] . ' ' . $batas_pulang_str);
                                $durasi_kerja = $batas_masuk_dt->diff($batas_pulang_dt);
                                if ($absen_masuk) {
                                    if ($absen_masuk > $batas_masuk_dt) {
                                        $diff = $absen_masuk->diff($batas_masuk_dt);
                                        $terlambat_jam = $diff->h; $terlambat_menit = $diff->i;
                                        $menit_telat = ($terlambat_jam * 60) + $terlambat_menit;
                                        if ($menit_telat >= 1 && $menit_telat <= 15) $persen += 0.25;
                                        elseif ($menit_telat >= 16 && $menit_telat <= 60) $persen += 0.5;
                                        elseif ($menit_telat > 60 && $menit_telat <= 120) $persen += 1.0;
                                        elseif ($menit_telat > 120) $persen += 1.5;
                                    }
                                } else {
                                    $terlambat_jam = $durasi_kerja->h;
                                    $terlambat_menit = $durasi_kerja->i;
                                    $persen += 1.5;
                                }
                                if ($absen_pulang) {
                                    if ($absen_pulang < $batas_pulang_dt) {
                                        $diff = $absen_pulang->diff($batas_pulang_dt);
                                        $cepat_pulang_jam = $diff->h; $cepat_pulang_menit = $diff->i;
                                        $persen += 1.5;
                                    }
                                } else {
                                    $cepat_pulang_jam = $durasi_kerja->h;
                                    $cepat_pulang_menit = $durasi_kerja->i;
                                    $persen += 1.5;
                                }
                            }
                            $total_terlambat_menit_akumulasi += ($terlambat_jam * 60) + $terlambat_menit;
                            $total_cepat_pulang_menit_akumulasi += ($cepat_pulang_jam * 60) + $cepat_pulang_menit;
                            $total_persen_potongan += $persen;
                        ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td><?php echo $nama_hari[$hari_angka] . ", " . $date_obj->format('d-m-Y'); ?></td>
                            <td><?php echo htmlspecialchars($laporan['nama_lengkap']); ?></td>
                            <td><?php echo $absen_masuk ? $absen_masuk->format('H:i:s') : '-'; ?></td>
                            <td><?php echo $terlambat_jam ?: '-'; ?></td>
                            <td><?php echo $terlambat_menit ?: '-'; ?></td>
                            <td><?php echo $absen_pulang ? $absen_pulang->format('H:i:s') : '-'; ?></td>
                            <td><?php echo $cepat_pulang_jam ?: '-'; ?></td>
                            <td><?php echo $cepat_pulang_menit ?: '-'; ?></td>
                            <td><span class="badge <?php if($status=='H') echo 'bg-success'; elseif($status=='DL') echo 'bg-warning text-dark'; elseif($status=='M') echo 'bg-danger'; else echo 'bg-info'; ?>"><?php echo $status; ?></span></td>
                            <td><?php echo $persen > 0 ? number_format($persen, 2) . '%' : '-'; ?></td>
                            <td>
                                <?php if ($laporan['masuk'] && $laporan['masuk']['foto']): ?>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#proofModal" data-nama="<?php echo htmlspecialchars($laporan['nama_lengkap']); ?>" data-tipe="Masuk" data-jam="<?php echo $absen_masuk->format('H:i:s'); ?>" data-catatan="<?php echo htmlspecialchars($laporan['masuk']['catatan']); ?>" data-foto="../public/uploads/foto_absen/<?php echo $laporan['masuk']['foto']; ?>" data-lat="<?php echo $laporan['masuk']['latitude']; ?>" data-lon="<?php echo $laporan['masuk']['longitude']; ?>"><i class="bi bi-camera-fill"></i></button>
                                <?php endif; ?>
                                
                                <?php if ($laporan['pulang'] && $laporan['pulang']['foto']): ?>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#proofModal" data-nama="<?php echo htmlspecialchars($laporan['nama_lengkap']); ?>" data-tipe="Pulang" data-jam="<?php echo $absen_pulang->format('H:i:s'); ?>" data-catatan="<?php echo htmlspecialchars($laporan['pulang']['catatan']); ?>" data-foto="../public/uploads/foto_absen/<?php echo $laporan['pulang']['foto']; ?>" data-lat="<?php echo $laporan['pulang']['latitude']; ?>" data-lon="<?php echo $laporan['pulang']['longitude']; ?>"><i class="bi bi-camera-fill"></i></button>
                                <?php endif; ?>

                                <?php // -- BLOK BARU UNTUK BUKTI DINAS LUAR -- ?>
                                <?php if ($dinas_luar && !empty($dinas_luar['file_surat_tugas'])): ?>
                                    <a href="../public/uploads/foto_dinas_luar/<?php echo htmlspecialchars($dinas_luar['file_surat_tugas']); ?>" target="_blank" class="btn btn-warning btn-sm" title="Lihat Surat Tugas">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-group-divider fw-bold">
                    <tr>
                        <td colspan="10" class="text-end">Total</td>
                        <td><span class="badge bg-success"><?php echo $total_h; ?> H</span> <span class="badge bg-danger"><?php echo $total_m; ?> M</span> <span class="badge bg-warning text-dark"><?php echo $total_dl; ?> DL</span></td>
                        <td><?php echo number_format($total_persen_potongan, 2); ?>%</td>
                    </tr>
                    <tr>
                        <td colspan="10" class="text-end">Total Menit Terlambat</td>
                        <td colspan="2"><?php echo $total_terlambat_menit_akumulasi; ?> Menit</td>
                    </tr>
                    <tr>
                        <td colspan="10" class="text-end">Total Menit Cepat Pulang</td>
                        <td colspan="2"><?php echo $total_cepat_pulang_menit_akumulasi; ?> Menit</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <nav class="no-print">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>&pegawai_id=<?php echo $pegawai_id_filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="proofModalLabel">Bukti Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-7"><img id="proofImage" src="" class="img-fluid rounded" alt="Foto Absensi"></div>
            <div class="col-md-5">
                <h5 id="proofNama"></h5>
                <p><strong>Tipe:</strong> <span id="proofTipe" class="badge bg-primary"></span><br><strong>Jam:</strong> <span id="proofJam"></span></p>
                <p><strong>Catatan:</strong><br><span id="proofCatatan"></span></p>
                <a id="proofGmapsLink" href="#" target="_blank" class="btn btn-success w-100"><i class="bi bi-geo-alt-fill"></i> Lihat di Google Maps</a>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const proofModal = document.getElementById('proofModal');
    proofModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const nama = button.getAttribute('data-nama');
        const tipe = button.getAttribute('data-tipe');
        const jam = button.getAttribute('data-jam');
        const catatan = button.getAttribute('data-catatan');
        const foto = button.getAttribute('data-foto');
        const lat = button.getAttribute('data-lat');
        const lon = button.getAttribute('data-lon');
        const modalTitle = proofModal.querySelector('.modal-title');
        const proofImage = proofModal.querySelector('#proofImage');
        const proofNama = proofModal.querySelector('#proofNama');
        const proofTipe = proofModal.querySelector('#proofTipe');
        const proofJam = proofModal.querySelector('#proofJam');
        const proofCatatan = proofModal.querySelector('#proofCatatan');
        const gmapsLink = proofModal.querySelector('#proofGmapsLink');
        modalTitle.textContent = 'Bukti Absensi ' + tipe + ' - ' + nama;
        proofImage.src = foto;
        proofNama.textContent = nama;
        proofTipe.textContent = tipe;
        proofJam.textContent = jam;
        proofCatatan.textContent = catatan;
        if (lat && lon && lat != 0 && lon != 0) {
            gmapsLink.href = `http://maps.google.com/maps?q=${lat},${lon}`;
            gmapsLink.style.display = 'block';
        } else {
            gmapsLink.style.display = 'none';
        }
    });
});
</script>