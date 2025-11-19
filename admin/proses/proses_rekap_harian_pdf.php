<?php
// admin/proses/proses_rekap_harian_pdf.php

// 1. Load Konfigurasi & Library
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

// 2. Validasi Input dari Form
if (!isset($_POST['tanggal_awal']) || !isset($_POST['tanggal_akhir']) || !isset($_POST['pegawai'])) {
    die("Data filter tidak lengkap. Silakan kembali dan pilih Tanggal Awal, Tanggal Akhir, dan Pegawai.");
}

$tanggal_awal = $_POST['tanggal_awal'];
$tanggal_akhir = $_POST['tanggal_akhir'];
$selected_pegawai = $_POST['pegawai'];

if (empty($selected_pegawai)) {
    die("Belum ada pegawai yang dipilih.");
}

// Validasi format tanggal
$tgl_awal_obj = date_create($tanggal_awal);
$tgl_akhir_obj = date_create($tanggal_akhir);

if (!$tgl_awal_obj || !$tgl_akhir_obj || $tgl_akhir_obj < $tgl_awal_obj) {
    die("Format tanggal tidak valid atau tanggal akhir lebih kecil dari tanggal awal.");
}

// 3. Siapkan Data Referensi (Hari Libur)
$data_libur = [];
$query_libur = "SELECT tanggal, keterangan FROM tabel_hari_libur 
                WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
$res_libur = mysqli_query($koneksi, $query_libur);
while ($row = mysqli_fetch_assoc($res_libur)) {
    $data_libur[$row['tanggal']] = $row['keterangan'];
}

// Konversi ID pegawai array ke string untuk query SQL
$ids_string = implode(',', array_map('intval', $selected_pegawai));

// 4. Ambil Data Pegawai yang Dipilih
$data_pegawai = [];
$query_user = "SELECT id_pegawai, nama_lengkap, nip FROM tabel_pegawai 
               WHERE id_pegawai IN ($ids_string) 
               ORDER BY nama_lengkap ASC";
$res_user = mysqli_query($koneksi, $query_user);
while ($row = mysqli_fetch_assoc($res_user)) {
    $data_pegawai[$row['id_pegawai']] = $row;
}

// 5. Ambil Data Absensi (Hadir & Dinas Luar)
$data_absensi = [];
$query_absen = "SELECT id_pegawai, DATE(waktu_absensi) as tgl, tipe_absensi 
                FROM tabel_absensi 
                WHERE id_pegawai IN ($ids_string) 
                AND DATE(waktu_absensi) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
                AND tipe_absensi IN ('Masuk', 'Dinas Luar')";

$res_absen = mysqli_query($koneksi, $query_absen);
while ($row = mysqli_fetch_assoc($res_absen)) {
    // Prioritaskan Dinas Luar jika ada absensi Masuk dan DL di hari yang sama
    if (!isset($data_absensi[$row['id_pegawai']][$row['tgl']])) {
        $data_absensi[$row['id_pegawai']][$row['tgl']] = ($row['tipe_absensi'] == 'Dinas Luar') ? 'DL' : 'H';
    } else {
        if ($row['tipe_absensi'] == 'Dinas Luar') {
            $data_absensi[$row['id_pegawai']][$row['tgl']] = 'DL';
        }
    }
}

// =================================================================================
// MULAI GENERATE PDF
// =================================================================================

$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Absensi');
$periode_laporan = date_format($tgl_awal_obj, 'd M Y') . ' - ' . date_format($tgl_akhir_obj, 'd M Y');
$pdf->SetTitle('Rekap Absensi Harian - ' . $periode_laporan);

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'REKAPITULASI ABSENSI HARIAN PEGAWAI', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 6, 'PERIODE: ' . $periode_laporan, 0, 1, 'C');
$pdf->Ln(5);

// Buat periode tanggal untuk iterasi
$period = new DatePeriod(
    $tgl_awal_obj,
    new DateInterval('P1D'),
    (clone $tgl_akhir_obj)->modify('+1 day') // Sertakan tanggal akhir dalam iterasi
);
$jumlah_hari = iterator_count($period);

// Sesuaikan lebar kolom berdasarkan jumlah hari
$w_no = 10;
$w_nama = 50;
$w_total = 10;
$available_width = 277 - $w_no - $w_nama - (3 * $w_total); // 297mm (A4 Landscape) - 20mm margin
$w_tgl = $available_width / $jumlah_hari;
if ($w_tgl > 8) $w_tgl = 8; // Batas maks lebar tgl
if ($w_tgl < 5) $w_tgl = 5; // Batas min lebar tgl

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetFillColor(200, 200, 200);

$pdf->Cell($w_no, 10, 'NO', 1, 0, 'C', 1);
$pdf->Cell($w_nama, 10, 'NAMA PEGAWAI', 1, 0, 'C', 1);

// Header Tanggal
foreach ($period as $date) {
    $pdf->Cell($w_tgl, 10, $date->format('d'), 1, 0, 'C', 1);
}

$pdf->Cell($w_total, 10, 'H', 1, 0, 'C', 1);
$pdf->Cell($w_total, 10, 'DL', 1, 0, 'C', 1);
$pdf->Cell($w_total, 10, 'A', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 7);
$no = 1;

foreach ($data_pegawai as $id_pgw => $pgw) {
    $pdf->Cell($w_no, 6, $no++, 1, 0, 'C');
    $nama_display = substr($pgw['nama_lengkap'], 0, 25);
    $pdf->Cell($w_nama, 6, $nama_display, 1, 0, 'L');

    $total_hadir = 0;
    $total_dl = 0;
    $total_alpha = 0;

    foreach ($period as $date) {
        $current_date_str = $date->format('Y-m-d');
        $nama_hari = $date->format('N'); // 1 (Mon) - 7 (Sun)
        $is_weekend = ($nama_hari >= 6); // Sabtu atau Minggu
        $is_holiday = isset($data_libur[$current_date_str]);
        
        $status = '';
        $fill = false;

        if (isset($data_absensi[$id_pgw][$current_date_str])) {
            $status = $data_absensi[$id_pgw][$current_date_str];
            if ($status == 'H') $total_hadir++;
            if ($status == 'DL') $total_dl++;
        } else {
            if ($is_holiday || $is_weekend) {
                $status = 'L';
                $pdf->SetFillColor(255, 200, 200); // Merah muda untuk libur
                $fill = true;
            } else {
                $status = '-'; // Alpha
                $total_alpha++;
            }
        }
        $pdf->Cell($w_tgl, 6, $status, 1, 0, 'C', $fill);
    }

    $pdf->Cell($w_total, 6, $total_hadir, 1, 0, 'C');
    $pdf->Cell($w_total, 6, $total_dl, 1, 0, 'C');
    $pdf->Cell($w_total, 6, $total_alpha, 1, 1, 'C');
}

// --- Keterangan Kaki ---
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 4, 'Keterangan: H=Hadir, DL=Dinas Luar, L=Libur/Minggu, - = Tanpa Keterangan (Alpha)', 0, 1, 'L');
$pdf->Cell(0, 4, 'Dicetak pada: ' . date('d-m-Y H:i:s'), 0, 1, 'L');

// Output PDF
$pdf_filename = 'Rekap_Absensi_' . $tgl_awal_obj->format('Ymd') . '_' . $tgl_akhir_obj->format('Ymd') . '.pdf';
$pdf->Output($pdf_filename, 'I');
?>