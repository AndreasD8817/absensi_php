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

// Buat periode tanggal untuk iterasi lebih awal agar bisa diakses di Header
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

// Buat class PDF khusus untuk header berulang
class MYPDF extends TCPDF {
    // Properti untuk menyimpan variabel yang dibutuhkan di header
    public $periode_laporan;
    public $period;
    public $w_no, $w_nama, $w_tgl, $w_total;

    // Override method Header
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'REKAPITULASI ABSENSI HARIAN PEGAWAI', 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 6, 'PERIODE: ' . $this->periode_laporan, 0, 1, 'C');
        $this->Ln(5);

        // Header Tabel
        $this->SetFont('helvetica', 'B', 7);
        $this->SetFillColor(200, 200, 200);
        $this->Cell($this->w_no, 10, 'NO', 1, 0, 'C', 1);
        $this->Cell($this->w_nama, 10, 'NAMA PEGAWAI', 1, 0, 'C', 1);
        foreach ($this->period as $date) {
            $this->Cell($this->w_tgl, 10, $date->format('d'), 1, 0, 'C', 1);
        }
        $this->Cell($this->w_total, 10, 'H', 1, 0, 'C', 1);
        $this->Cell($this->w_total, 10, 'DL', 1, 0, 'C', 1);
        $this->Cell($this->w_total, 10, 'M', 1, 1, 'C', 1);
    }
}

// Gunakan class MYPDF yang sudah kita buat
$pdf = new MYPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Absensi');
$periode_laporan = date_format($tgl_awal_obj, 'd M Y') . ' - ' . date_format($tgl_akhir_obj, 'd M Y');
$pdf->SetTitle('Rekap Absensi Harian - ' . $periode_laporan);

// Set properti yang dibutuhkan oleh Header
$pdf->periode_laporan = $periode_laporan;
$pdf->period = $period;
$pdf->w_no = $w_no;
$pdf->w_nama = $w_nama;
$pdf->w_tgl = $w_tgl;
$pdf->w_total = $w_total;

// Set margin. Margin atas (35) harus cukup besar untuk header kustom kita.
// Margin bawah (15) untuk footer.
$pdf->SetMargins(10, 35, 10);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->setPrintHeader(true); // Aktifkan header
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 7);
$no = 1;

foreach ($data_pegawai as $id_pgw => $pgw) {
    $pdf->Cell($w_no, 6, $no++, 1, 0, 'C');
    $nama_display = substr($pgw['nama_lengkap'], 0, 25);
    $pdf->Cell($w_nama, 6, $nama_display, 1, 0, 'L');

    $total_hadir = 0;
    $total_dl = 0;
    $total_mangkir = 0;

    foreach ($period as $date) {
        $current_date_str = $date->format('Y-m-d');
        $nama_hari = $date->format('N'); // 1 (Mon) - 7 (Sun)
        $is_weekend = ($nama_hari == 7); // Minggu
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
                $status = 'M'; // Mangkir
                $total_mangkir++;
            }
        }
        $pdf->Cell($w_tgl, 6, $status, 1, 0, 'C', $fill);
    }

    $pdf->Cell($w_total, 6, $total_hadir, 1, 0, 'C');
    $pdf->Cell($w_total, 6, $total_dl, 1, 0, 'C');
    $pdf->Cell($w_total, 6, $total_mangkir, 1, 1, 'C');
}

// --- Keterangan Kaki ---
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 4, 'Keterangan: H=Hadir, DL=Dinas Luar, L=Libur/Minggu, M = Tanpa Keterangan (Mangkir)', 0, 1, 'L');
$pdf->Cell(0, 4, 'Dicetak pada: ' . date('d-m-Y H:i:s'), 0, 1, 'L');

// Output PDF
$pdf_filename = 'Rekap_Absensi_' . $tgl_awal_obj->format('Ymd') . '_' . $tgl_akhir_obj->format('Ymd') . '.pdf';
$pdf->Output($pdf_filename, 'I');
?>