<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf_helper.php';

// --- KEAMANAN & VALIDASI AWAL ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin");
    exit();
}

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: /login?error=Akses ditolak");
    exit();
}

if (!validate_csrf_token($_POST['csrf_token'])) {
    // Siapkan URL redirect dengan parameter
    $redirect_url = "/admin/edit-absensi?error=Sesi tidak valid, coba lagi." 
                    . "&id_pegawai=" . urlencode($_POST['id_pegawai']) 
                    . "&tanggal=" . urlencode($_POST['tanggal']);
    header("Location: " . $redirect_url);
    exit();
}

// --- AMBIL & SANITASI DATA DARI FORM ---
$id_pegawai = (int)$_POST['id_pegawai'];
$tanggal = $_POST['tanggal'];
$status_absensi = $_POST['status_absensi'];

// URL untuk redirect jika terjadi error atau sukses
$redirect_url = "/admin/edit-absensi?id_pegawai=$id_pegawai&tanggal=$tanggal";

// === BARU: Ambil nama pegawai untuk log yang lebih deskriptif ===
$sql_get_nama = "SELECT nama_lengkap FROM tabel_pegawai WHERE id_pegawai = ?";
$stmt_get_nama = mysqli_prepare($koneksi, $sql_get_nama);
mysqli_stmt_bind_param($stmt_get_nama, "i", $id_pegawai);
mysqli_stmt_execute($stmt_get_nama);
$nama_pegawai = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get_nama))['nama_lengkap'] ?? 'N/A';
mysqli_stmt_close($stmt_get_nama);
// ===============================================================

// --- MULAI TRANSAKSI DATABASE ---
// Ini penting agar semua query berhasil, atau tidak sama sekali.
mysqli_begin_transaction($koneksi);

try {
    // Variabel untuk menyimpan pesan log
    $aktivitas_log = '';

    // 1. JIKA STATUS "TIDAK HADIR (ALPHA)"
    // Hapus semua record absensi (masuk, pulang, DL) pada hari itu.
    if ($status_absensi === 'alpha') {
        $sql_delete_all = "DELETE FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ?";
        $stmt_delete_all = mysqli_prepare($koneksi, $sql_delete_all);
        mysqli_stmt_bind_param($stmt_delete_all, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_delete_all);

        // Siapkan pesan log untuk aksi ini
        $aktivitas_log = "Mengubah status absensi '$nama_pegawai' pada tanggal $tanggal menjadi Alpha (Tidak Hadir).";
    }

    // 2. JIKA STATUS "HADIR" (JAM MASUK/PULANG)
    if ($status_absensi === 'hadir') {
        // Hapus data Dinas Luar beserta filenya jika ada.
        $sql_get_dl = "SELECT a.id_absensi, dl.file_surat_tugas 
                       FROM tabel_absensi a
                       JOIN tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi
                       WHERE a.id_pegawai = ? AND DATE(a.waktu_absensi) = ? AND a.tipe_absensi = 'Dinas Luar'";
        $stmt_get_dl = mysqli_prepare($koneksi, $sql_get_dl);
        mysqli_stmt_bind_param($stmt_get_dl, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_get_dl);
        $result_dl = mysqli_stmt_get_result($stmt_get_dl);

        if ($dl_row = mysqli_fetch_assoc($result_dl)) {
            $id_absensi_dl = $dl_row['id_absensi'];
            $nama_file_untuk_dihapus = $dl_row['file_surat_tugas'];
            $folder_upload = __DIR__ . '/../../public/uploads/foto_dinas_luar/';
            $path_file = $folder_upload . $nama_file_untuk_dihapus;

            if (!empty($nama_file_untuk_dihapus) && file_exists($path_file)) {
                unlink($path_file);
            }

            $sql_delete_dl_ref = "DELETE FROM tabel_dinas_luar WHERE id_absensi = ?";
            $stmt_delete_dl_ref = mysqli_prepare($koneksi, $sql_delete_dl_ref);
            mysqli_stmt_bind_param($stmt_delete_dl_ref, "i", $id_absensi_dl);
            mysqli_stmt_execute($stmt_delete_dl_ref);
        }
        
        $sql_delete_dl = "DELETE FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Dinas Luar'";
        $stmt_delete_dl = mysqli_prepare($koneksi, $sql_delete_dl);
        mysqli_stmt_bind_param($stmt_delete_dl, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_delete_dl);

        // --- Proses Absen Masuk & Pulang ---
        // (Kode proses absen masuk dan pulang tidak diubah, hanya ditambahkan log di akhir)
        $jam_masuk = !empty($_POST['jam_masuk']) ? $_POST['jam_masuk'] : null;
        $catatan_masuk = trim($_POST['catatan_masuk']);
        
        $sql_cek_masuk = "SELECT id_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Masuk'";
        $stmt_cek_masuk = mysqli_prepare($koneksi, $sql_cek_masuk);
        mysqli_stmt_bind_param($stmt_cek_masuk, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_cek_masuk);
        $id_absen_masuk = mysqli_stmt_get_result($stmt_cek_masuk)->fetch_assoc()['id_absensi'] ?? null;

        if ($jam_masuk) {
            $waktu_masuk = $tanggal . ' ' . $jam_masuk;
            if ($id_absen_masuk) { // Ada -> UPDATE
                $sql = "UPDATE tabel_absensi SET waktu_absensi = ?, catatan = ? WHERE id_absensi = ?";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $waktu_masuk, $catatan_masuk, $id_absen_masuk);
            } else { // Tidak ada -> INSERT
                $sql = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, catatan) VALUES (?, 'Masuk', ?, ?)";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "iss", $id_pegawai, $waktu_masuk, $catatan_masuk);
            }
            mysqli_stmt_execute($stmt);
        } elseif ($id_absen_masuk) { // Jam dikosongkan, hapus data lama
            $sql_delete = "DELETE FROM tabel_absensi WHERE id_absensi = ?";
            $stmt_delete = mysqli_prepare($koneksi, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "i", $id_absen_masuk);
            mysqli_stmt_execute($stmt_delete);
        }
        
        $jam_pulang = !empty($_POST['jam_pulang']) ? $_POST['jam_pulang'] : null;
        $catatan_pulang = trim($_POST['catatan_pulang']);

        $sql_cek_pulang = "SELECT id_absensi FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi = 'Pulang'";
        $stmt_cek_pulang = mysqli_prepare($koneksi, $sql_cek_pulang);
        mysqli_stmt_bind_param($stmt_cek_pulang, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_cek_pulang);
        $id_absen_pulang = mysqli_stmt_get_result($stmt_cek_pulang)->fetch_assoc()['id_absensi'] ?? null;

        if ($jam_pulang) {
            $waktu_pulang = $tanggal . ' ' . $jam_pulang;
            if ($id_absen_pulang) { // Ada -> UPDATE
                $sql = "UPDATE tabel_absensi SET waktu_absensi = ?, catatan = ? WHERE id_absensi = ?";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $waktu_pulang, $catatan_pulang, $id_absen_pulang);
            } else { // Tidak ada -> INSERT
                $sql = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi, catatan) VALUES (?, 'Pulang', ?, ?)";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "iss", $id_pegawai, $waktu_pulang, $catatan_pulang);
            }
            mysqli_stmt_execute($stmt);
        } elseif ($id_absen_pulang) { // Jam dikosongkan, hapus data lama
            $sql_delete = "DELETE FROM tabel_absensi WHERE id_absensi = ?";
            $stmt_delete = mysqli_prepare($koneksi, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "i", $id_absen_pulang);
            mysqli_stmt_execute($stmt_delete);
        }
        
        $aktivitas_log = "Mengedit data absensi (Hadir) untuk '$nama_pegawai' pada tanggal $tanggal.";
    }

    // 3. JIKA STATUS "DINAS LUAR"
    if ($status_absensi === 'dinas_luar') {
        // (Kode proses dinas luar tidak diubah, hanya ditambahkan log di akhir)
        $sql_delete_reguler = "DELETE FROM tabel_absensi WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? AND tipe_absensi IN ('Masuk', 'Pulang')";
        $stmt_delete_reguler = mysqli_prepare($koneksi, $sql_delete_reguler);
        mysqli_stmt_bind_param($stmt_delete_reguler, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_delete_reguler);

        $keterangan_dl = trim($_POST['keterangan_dl']);
        $file_dl = $_FILES['file_dl'];
        $nama_file_unik = null;

        if (isset($file_dl) && $file_dl['error'] === UPLOAD_ERR_OK) {
            if ($file_dl['size'] > 2 * 1024 * 1024) throw new Exception("Ukuran file terlalu besar. Maksimal 2 MB.");
            $allowed_types = ['application/pdf', 'image/jpeg'];
            if (!in_array($file_dl['type'], $allowed_types)) throw new Exception("Tipe file tidak diizinkan. Hanya PDF atau JPG.");
            
            $folder_upload = __DIR__ . '/../../public/uploads/foto_dinas_luar/';
            $nama_file_unik = $id_pegawai . '_' . time() . '_' . basename($file_dl['name']);
            $path_tujuan = $folder_upload . $nama_file_unik;

            if (!move_uploaded_file($file_dl['tmp_name'], $path_tujuan)) {
                throw new Exception("Gagal memindahkan file yang diunggah.");
            }
        }

        $sql_cek_dl = "SELECT a.id_absensi, dl.file_surat_tugas FROM tabel_absensi a JOIN tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi WHERE a.id_pegawai = ? AND DATE(a.waktu_absensi) = ?";
        $stmt_cek_dl = mysqli_prepare($koneksi, $sql_cek_dl);
        mysqli_stmt_bind_param($stmt_cek_dl, "is", $id_pegawai, $tanggal);
        mysqli_stmt_execute($stmt_cek_dl);
        $dl_data = mysqli_stmt_get_result($stmt_cek_dl)->fetch_assoc();

        if ($dl_data) { // Ada -> UPDATE
            $id_absensi_dl = $dl_data['id_absensi'];
            $file_lama = $dl_data['file_surat_tugas'];

            $sql_update_dl = "UPDATE tabel_dinas_luar SET keterangan = ?" . ($nama_file_unik ? ", file_surat_tugas = ?" : "") . " WHERE id_absensi = ?";
            $stmt_update_dl = mysqli_prepare($koneksi, $sql_update_dl);

            if ($nama_file_unik) {
                mysqli_stmt_bind_param($stmt_update_dl, "ssi", $keterangan_dl, $nama_file_unik, $id_absensi_dl);
                if ($file_lama && file_exists($folder_upload . $file_lama)) {
                    unlink($folder_upload . $file_lama);
                }
            } else {
                mysqli_stmt_bind_param($stmt_update_dl, "si", $keterangan_dl, $id_absensi_dl);
            }
            mysqli_stmt_execute($stmt_update_dl);

        } else { // Tidak ada -> INSERT
            $waktu_dl = $tanggal . ' 08:00:00'; 
            $sql_ins_abs = "INSERT INTO tabel_absensi (id_pegawai, tipe_absensi, waktu_absensi) VALUES (?, 'Dinas Luar', ?)";
            $stmt_ins_abs = mysqli_prepare($koneksi, $sql_ins_abs);
            mysqli_stmt_bind_param($stmt_ins_abs, "is", $id_pegawai, $waktu_dl);
            mysqli_stmt_execute($stmt_ins_abs);
            $id_absensi_baru = mysqli_insert_id($koneksi);
            if ($id_absensi_baru == 0) throw new Exception("Gagal insert absensi untuk Dinas Luar.");

            $sql_ins_dl = "INSERT INTO tabel_dinas_luar (id_absensi, file_surat_tugas, keterangan) VALUES (?, ?, ?)";
            $stmt_ins_dl = mysqli_prepare($koneksi, $sql_ins_dl);
            mysqli_stmt_bind_param($stmt_ins_dl, "iss", $id_absensi_baru, $nama_file_unik, $keterangan_dl);
            mysqli_stmt_execute($stmt_ins_dl);
        }

        $aktivitas_log = "Mengedit data absensi (Dinas Luar) untuk '$nama_pegawai' pada tanggal $tanggal.";
    }

    // === CATAT LOG AKTIVITAS SETELAH SEMUA PROSES SELESAI ===
    if (!empty($aktivitas_log)) {
        catat_log($koneksi, $_SESSION['id_pegawai'], $_SESSION['role'], $aktivitas_log);
    }
    // ========================================================

    // Jika semua query berhasil, commit transaksi
    mysqli_commit($koneksi);
    header("Location: $redirect_url&sukses=Data absensi berhasil diperbarui.");

} catch (Exception $e) {
    // Jika ada error di tengah jalan, batalkan semua perubahan
    mysqli_rollback($koneksi);
    // Hapus file yang mungkin sudah terlanjur diupload
    if (!empty($path_tujuan) && file_exists($path_tujuan)) {
        unlink($path_tujuan);
    }
    header("Location: $redirect_url&error=" . urlencode($e->getMessage()));
}

exit();
?>