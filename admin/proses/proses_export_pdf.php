<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Keamanan: Hanya Super Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    die("Akses ditolak.");
}

// Instal library FPDF terlebih dahulu: composer require setasign/fpdf
require_once __DIR__ . '/../../vendor/autoload.php';

// --- PENGATURAN & FILTER ---
// Ambil pengaturan gaji dari database
$pengaturan_gaji = [];
$sql_pengaturan = "SELECT nama_pengaturan, nilai_pengaturan FROM tabel_pengaturan WHERE nama_pengaturan IN ('gaji_harian', 'potongan_tetap')";
$result_pengaturan = mysqli_query($koneksi, $sql_pengaturan);
while ($row = mysqli_fetch_assoc($result_pengaturan)) {
    $pengaturan_gaji[$row['nama_pengaturan']] = $row['nilai_pengaturan'];
}
$gaji_harian_default = $pengaturan_gaji['gaji_harian'] ?? 160700;
$potongan_tetap_default = $pengaturan_gaji['potongan_tetap'] ?? 41300;

// Mengambil parameter tanggal dari URL
$tanggal_awal = isset($_GET['awal']) && !empty($_GET['awal']) ? $_GET['awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) && !empty($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-t');

// Ambil semua pegawai untuk diproses
$sql_pegawai = "SELECT id_pegawai, nama_lengkap FROM tabel_pegawai WHERE role = 'pegawai' ORDER BY nama_lengkap ASC";
$result_pegawai = mysqli_query($koneksi, $sql_pegawai);

$laporan_penggajian = [];

// --- PROSES PERHITUNGAN GAJI (Sama seperti di halaman laporan) ---
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
    $total_potongan_keterlambatan = 0;

    $period = new DatePeriod(new DateTime($tanggal_awal), new DateInterval('P1D'), (new DateTime($tanggal_akhir))->modify('+1 day'));

    foreach ($period as $date) {
        $tanggal_loop = $date->format('Y-m-d');
        $hari_angka = $date->format('w');

        $is_hari_kerja = ($hari_angka != 0 && !in_array($tanggal_loop, $daftar_libur));

        if ($is_hari_kerja) {
            $hadir = isset($rekap_harian[$tanggal_loop]['Masuk']) || isset($rekap_harian[$tanggal_loop]['Dinas Luar']);
            if ($hadir) {
                $jumlah_hari_masuk++;
                if (isset($rekap_harian[$tanggal_loop]['Masuk'])) {
                    $batas_masuk_str = ($hari_angka == 6) ? '08:00:00' : '07:30:00';
                    $absen_masuk_dt = new DateTime($rekap_harian[$tanggal_loop]['Masuk']);
                    $batas_masuk_dt = new DateTime($tanggal_loop . ' ' . $batas_masuk_str);

                    if ($absen_masuk_dt > $batas_masuk_dt) {
                        $diff = $absen_masuk_dt->diff($batas_masuk_dt);
                        $menit_telat = ($diff->h * 60) + $diff->i;
                        $persen_potongan = 0;
                        if ($menit_telat >= 1 && $menit_telat <= 15) $persen_potongan = 0.25;
                        elseif ($menit_telat >= 16 && $menit_telat <= 60) $persen_potongan = 0.5;
                        elseif ($menit_telat > 60 && $menit_telat <= 120) $persen_potongan = 1.0;
                        elseif ($menit_telat > 120) $persen_potongan = 1.5;
                        $total_potongan_keterlambatan += ($gaji_harian_default * $persen_potongan) / 100;
                    }
                }
            }
        }
    }

    $gaji_kotor = $jumlah_hari_masuk * $gaji_harian_default;
    $gaji_bersih = $gaji_kotor - $potongan_tetap_default - $total_potongan_keterlambatan;

    $laporan_penggajian[] = [
        'nama_lengkap' => $pegawai['nama_lengkap'],
        'jumlah_hari_masuk' => $jumlah_hari_masuk,
        'gaji_kotor' => $gaji_kotor,
        'potongan_keterlambatan' => $total_potongan_keterlambatan,
        'potongan_tetap' => $potongan_tetap_default,
        'gaji_bersih' => $gaji_bersih
    ];
}

// --- PEMBUATAN PDF ---
class PDF extends FPDF
{
    // Header Halaman
    function Header()
    {
        global $tanggal_awal, $tanggal_akhir;
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Penggajian Pegawai',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,8,'Periode: ' . date('d M Y', strtotime($tanggal_awal)) . ' s/d ' . date('d M Y', strtotime($tanggal_akhir)),0,1,'C');
        $this->Ln(5);
    }

    // Footer Halaman
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',8);

// Header Tabel
$pdf->Cell(10, 7, 'No', 1, 0, 'C');
$pdf->Cell(60, 7, 'Nama Pegawai', 1, 0, 'C');
$pdf->Cell(25, 7, 'Hari Masuk', 1, 0, 'C');
$pdf->Cell(35, 7, 'Gaji Kotor', 1, 0, 'C');
$pdf->Cell(40, 7, 'Potongan Terlambat', 1, 0, 'C');
$pdf->Cell(45, 7, 'Potongan Tetap (IURAN JKK JK)', 1, 0, 'C');
$pdf->Cell(40, 7, 'Gaji Bersih', 1, 0, 'C');
$pdf->Ln();

$pdf->SetFont('Arial','',8);
$no = 1;
foreach ($laporan_penggajian as $laporan) {
    $pdf->Cell(10, 6, $no++, 1);
    $pdf->Cell(60, 6, $laporan['nama_lengkap'], 1);
    $pdf->Cell(25, 6, $laporan['jumlah_hari_masuk'], 1, 0, 'C');
    $pdf->Cell(35, 6, 'Rp ' . number_format($laporan['gaji_kotor'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(40, 6, 'Rp ' . number_format($laporan['potongan_keterlambatan'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(45, 6, 'Rp ' . number_format($laporan['potongan_tetap'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(40, 6, 'Rp ' . number_format($laporan['gaji_bersih'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Ln();
}

$pdf->Output('D', 'Laporan_Gaji_Periode_' . $tanggal_awal . '_sd_' . $tanggal_akhir . '.pdf');
