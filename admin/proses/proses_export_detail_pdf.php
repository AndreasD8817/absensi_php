<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Keamanan: Admin & Super Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    die("Akses ditolak.");
}

// Validasi input
if (!isset($_GET['id_pegawai']) || !isset($_GET['awal']) || !isset($_GET['akhir'])) {
    die("Parameter tidak lengkap.");
}

$id_pegawai = (int)$_GET['id_pegawai'];
$tanggal_awal = $_GET['awal'];
$tanggal_akhir = $_GET['akhir'];

// Ambil data pegawai
$sql_pegawai = "SELECT nama_lengkap FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_pegawai = mysqli_prepare($koneksi, $sql_pegawai);
mysqli_stmt_bind_param($stmt_pegawai, "i", $id_pegawai);
mysqli_stmt_execute($stmt_pegawai);
$pegawai = mysqli_stmt_get_result($stmt_pegawai)->fetch_assoc();

if (!$pegawai) {
    die("Pegawai tidak ditemukan.");
}

class PDF extends FPDF
{
    function Header()
    {
        global $pegawai, $tanggal_awal, $tanggal_akhir;
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Detail Laporan Absensi',0,1,'C');
        $this->SetFont('Arial','B',10);
        $this->Cell(0,8, $pegawai['nama_lengkap'],0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Periode: ' . date('d M Y', strtotime($tanggal_awal)) . ' s/d ' . date('d M Y', strtotime($tanggal_akhir)),0,1,'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',8);

// Header Tabel
$pdf->Cell(40, 7, 'Hari, Tanggal', 1, 0, 'C');
$pdf->Cell(25, 7, 'Jam Masuk', 1, 0, 'C');
$pdf->Cell(25, 7, 'Jam Pulang', 1, 0, 'C');
$pdf->Cell(15, 7, 'Status', 1, 0, 'C');
$pdf->Cell(25, 7, 'Potongan (%)', 1, 0, 'C');
$pdf->Cell(60, 7, 'Keterangan', 1, 0, 'C');
$pdf->Ln();

$pdf->SetFont('Arial','',8);

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

    $jam_masuk = $data_hari_ini['Masuk']['waktu_absensi'] ?? '-';
    $jam_pulang = $data_hari_ini['Pulang']['waktu_absensi'] ?? '-';
    $keterangan = $data_hari_ini['Masuk']['catatan'] ?? '';
    if(isset($data_hari_ini['Pulang']['catatan'])) {
        $keterangan .= ' | ' . $data_hari_ini['Pulang']['catatan'];
    }
    
    $status = 'M';
    $persen_harian = 0;

    if(isset($data_hari_ini['Dinas Luar'])) {
        $status = 'DL';
        $keterangan = 'Dinas Luar';
        $total_hadir++;
    } elseif (isset($data_hari_ini['Masuk'])) {
        $status = 'H';
        $total_hadir++;
        $batas_masuk_str = ($hari_angka == 6) ? '08:00:00' : '07:30:00';
        $absen_masuk_dt = new DateTime($jam_masuk);
        $batas_masuk_dt = new DateTime($tanggal_loop . ' ' . $batas_masuk_str);
        if ($absen_masuk_dt > $batas_masuk_dt) {
            $diff = $absen_masuk_dt->diff($batas_masuk_dt);
            $menit_telat = ($diff->h * 60) + $diff->i;
            if ($menit_telat >= 1 && $menit_telat <= 15) $persen_harian = 0.25;
            elseif ($menit_telat >= 16 && $menit_telat <= 60) $persen_harian = 0.5;
            elseif ($menit_telat > 60 && $menit_telat <= 120) $persen_harian = 1.0;
            elseif ($menit_telat > 120) $persen_harian = 1.5;
        }
    }
    
    if ($hari_angka == 0) { $status = 'Libur'; }
    
    $total_persen += $persen_harian;
    
    $pdf->Cell(40, 6, $nama_hari[$hari_angka] . ", " . $date->format('d-m-Y'), 1);
    $pdf->Cell(25, 6, $jam_masuk !== '-' ? date('H:i:s', strtotime($jam_masuk)) : '-', 1, 0, 'C');
    $pdf->Cell(25, 6, $jam_pulang !== '-' ? date('H:i:s', strtotime($jam_pulang)) : '-', 1, 0, 'C');
    $pdf->Cell(15, 6, $status, 1, 0, 'C');
    $pdf->Cell(25, 6, $persen_harian > 0 ? number_format($persen_harian, 2, ',', '.') . '%' : '-', 1, 0, 'C');
    $pdf->Cell(60, 6, substr($keterangan, 0, 40), 1);
    $pdf->Ln();
}

// Total
$pdf->SetFont('Arial','B',8);
$pdf->Cell(130, 7, 'Total Kehadiran:', 1, 0, 'R');
$pdf->Cell(60, 7, $total_hadir . ' Hari', 1, 1, 'C');
$pdf->Cell(130, 7, 'Total Akumulasi Persentase Potongan:', 1, 0, 'R');
$pdf->Cell(60, 7, number_format($total_persen, 2, ',', '.') . '%', 1, 1, 'C');


$pdf->Output('D', 'Detail_Absensi_' . str_replace(' ', '_', $pegawai['nama_lengkap']) . '.pdf');
