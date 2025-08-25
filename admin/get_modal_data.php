<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Keamanan dasar
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>Akses ditolak.</div>";
    exit();
}

$type = $_GET['type'] ?? '';
$hari_ini = date('Y-m-d');
$output = "<div class='alert alert-warning'>Tipe data tidak valid.</div>";

// Logika untuk mengambil data berdasarkan tipe yang diminta
switch ($type) {
    // --- KASUS UNTUK STATISTIK KEPEGAWAIAN ---
    case 'total_pegawai':
        $sql = "SELECT nip, nama_lengkap, jabatan FROM tabel_pegawai WHERE role = 'pegawai' ORDER BY nama_lengkap ASC";
        $result = mysqli_query($koneksi, $sql);
        $output = generate_table(['NIP', 'Nama Lengkap', 'Jabatan'], $result);
        break;

    case 'non_aktif':
        $sql = "SELECT nip, nama_lengkap, jabatan FROM tabel_pegawai WHERE role = 'pegawai' AND status = 'non-aktif' ORDER BY nama_lengkap ASC";
        $result = mysqli_query($koneksi, $sql);
        $output = generate_table(['NIP', 'Nama Lengkap', 'Jabatan'], $result);
        break;

    case 'total_admin':
        $sql = "SELECT nip, nama_lengkap, jabatan, role FROM tabel_pegawai WHERE role IN ('admin', 'superadmin') ORDER BY role, nama_lengkap ASC";
        $result = mysqli_query($koneksi, $sql);
        $output = generate_table(['NIP', 'Nama Lengkap', 'Jabatan', 'Role'], $result);
        break;

    case 'radius_khusus':
        $sql = "SELECT nama_lengkap, jabatan, radius_absensi FROM tabel_pegawai WHERE radius_absensi IS NOT NULL ORDER BY nama_lengkap ASC";
        $result = mysqli_query($koneksi, $sql);
        $output = generate_table(['Nama Lengkap', 'Jabatan', 'Radius (meter)'], $result);
        break;

    // --- KASUS UNTUK AKTIVITAS HARI INI ---
    case 'hadir':
        $sql = "SELECT p.nama_lengkap, p.jabatan, TIME(a.waktu_absensi) as jam_masuk FROM tabel_absensi a JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai WHERE a.tipe_absensi = 'Masuk' AND DATE(a.waktu_absensi) = ? ORDER BY a.waktu_absensi ASC";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $hari_ini);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $output = generate_table(['Nama Lengkap', 'Jabatan', 'Jam Masuk'], $result);
        break;

    case 'terlambat':
        $batas_masuk_str = (date('w') == 6) ? '08:00:00' : '07:30:00';
        $sql = "SELECT p.nama_lengkap, p.jabatan, TIME(a.waktu_absensi) as jam_masuk FROM tabel_absensi a JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai WHERE a.tipe_absensi = 'Masuk' AND DATE(a.waktu_absensi) = ? AND TIME(a.waktu_absensi) > ? ORDER BY a.waktu_absensi ASC";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $hari_ini, $batas_masuk_str);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $output = generate_table(['Nama Lengkap', 'Jabatan', 'Jam Masuk'], $result);
        break;
        
    case 'pulang_cepat':
        $batas_pulang_str = (date('w') == 6) ? '14:00:00' : '16:00:00';
        $sql = "SELECT p.nama_lengkap, p.jabatan, TIME(a.waktu_absensi) as jam_pulang FROM tabel_absensi a JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai WHERE a.tipe_absensi = 'Pulang' AND DATE(a.waktu_absensi) = ? AND TIME(a.waktu_absensi) < ? ORDER BY a.waktu_absensi ASC";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $hari_ini, $batas_pulang_str);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $output = generate_table(['Nama Lengkap', 'Jabatan', 'Jam Pulang'], $result);
        break;

    case 'dinas_luar':
        $sql = "SELECT p.nama_lengkap, p.jabatan, a.catatan FROM tabel_absensi a JOIN tabel_pegawai p ON a.id_pegawai = p.id_pegawai WHERE a.tipe_absensi = 'Dinas Luar' AND DATE(a.waktu_absensi) = ? ORDER BY a.waktu_absensi ASC";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $hari_ini);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $output = generate_table(['Nama Lengkap', 'Jabatan', 'Catatan'], $result);
        break;

    case 'tidak_hadir':
        $sql = "SELECT nip, nama_lengkap, jabatan FROM tabel_pegawai WHERE status = 'aktif' AND role = 'pegawai' AND id_pegawai NOT IN (SELECT DISTINCT id_pegawai FROM tabel_absensi WHERE DATE(waktu_absensi) = ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $hari_ini);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $output = generate_table(['NIP', 'Nama Lengkap', 'Jabatan'], $result);
        break;
}

// Fungsi helper untuk membuat tabel HTML
function generate_table($headers, $result) {
    if (mysqli_num_rows($result) == 0) {
        return "<div class='text-center p-4'><i class='bi bi-inbox fs-2'></i><p class='mt-2'>Tidak ada data untuk ditampilkan.</p></div>";
    }
    $html = "<div class='table-responsive'><table class='table table-striped table-hover'>";
    $html .= "<thead><tr>";
    foreach ($headers as $header) {
        $html .= "<th>$header</th>";
    }
    $html .= "</tr></thead><tbody>";
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            $html .= "<td>" . htmlspecialchars($cell) . "</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody></table></div>";
    return $html;
}

echo $output;
?>