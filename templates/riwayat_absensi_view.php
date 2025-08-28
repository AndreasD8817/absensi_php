<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/assets/img/logo/favicon.png" type="image/png">
    <title>Riwayat Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/user_dashboard.css">
    <style>
        /* CSS tambahan khusus untuk halaman riwayat */
        .history-card {
            background-color: #ffffff;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            border: none;
        }
        .history-card .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .history-value {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="main-content">
    
    <header class="profile-header">
        <div class="d-flex align-items-center">
            <img src="/assets/img/logo/icon.png" alt="Avatar" class="logo">
            <div class="user-info">
                <h5><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h5>
                <p><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></p>
            </div>
        </div>
        <div class="header-actions">
            <a href="/auth/logout" class="btn btn-danger btn-sm" title="Logout">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </a>
        </div>
    </header>

    <div class="container-fluid">
        <form method="GET" action="" class="mb-4 card p-3 shadow-sm">
            <div class="row g-2 align-items-end">
                <div class="col-6">
                    <label for="awal" class="form-label fw-bold">Dari</label>
                    <input type="date" class="form-control" id="awal" name="awal" value="<?php echo htmlspecialchars($tanggal_awal); ?>">
                </div>
                <div class="col-6">
                    <label for="akhir" class="form-label fw-bold">Sampai</label>
                    <input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                </div>
            </div>
        </form>

        <?php if (empty($dates_on_page)): ?>
            <div class="alert alert-info text-center">Tidak ada data pada rentang tanggal ini.</div>
        <?php else: ?>
            <?php 
            $nama_hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            foreach ($dates_on_page as $date): 
                // ... (Logika PHP untuk mengambil data harian tetap sama) ...
                $tanggal_loop = $date->format('Y-m-d');
                $hari_angka = $date->format('w');

                $is_libur_nasional = isset($daftar_libur[$tanggal_loop]);
                $keterangan_libur = $is_libur_nasional ? $daftar_libur[$tanggal_loop] : '';

                // ... (Logika PHP untuk mengambil data absensi harian) ...
                $sql = "SELECT a.tipe_absensi, a.waktu_absensi, a.catatan, dl.keterangan AS keterangan_dl FROM tabel_absensi a LEFT JOIN tabel_dinas_luar dl ON a.id_absensi = dl.id_absensi WHERE a.id_pegawai = ? AND DATE(a.waktu_absensi) = ? ORDER BY a.waktu_absensi ASC";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "is", $id_pegawai, $tanggal_loop);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                $absen_masuk = null;
                $absen_pulang = null;
                $dinas_luar = false;

                while($row = mysqli_fetch_assoc($result)) {
                    if ($row['tipe_absensi'] == 'Masuk') $absen_masuk = new DateTime($row['waktu_absensi']);
                    elseif ($row['tipe_absensi'] == 'Pulang') $absen_pulang = new DateTime($row['waktu_absensi']);
                    elseif ($row['tipe_absensi'] == 'Dinas Luar') $dinas_luar = true;
                }

                 $status = '';
                 if ($dinas_luar) { $status = 'DL'; }
                 elseif ($absen_masuk || $absen_pulang) { $status = 'H'; }
                 else { $status = ($hari_angka == 0 || $is_libur_nasional) ? 'Libur' : 'M'; }

            ?>
            <div class="card history-card">
                <div class="card-header">
                    <?php echo $nama_hari[$hari_angka] . ", " . $date->format('d F Y'); ?>
                </div>
                <div class="card-body p-3">
                    <div class="history-item">
                        <span class="history-label">Status</span>
                        <span class="history-value">
                             <span class="badge <?php if($status == 'H') echo 'bg-success'; elseif($status == 'DL') echo 'bg-warning text-dark'; elseif($status == 'M') echo 'bg-danger'; else echo 'bg-info'; ?>"><?php echo $status; ?></span>
                        </span>
                    </div>
                    <div class="history-item">
                        <span class="history-label">Jam Masuk</span>
                        <span class="history-value"><?php echo $absen_masuk ? $absen_masuk->format('H:i:s') : '-'; ?></span>
                    </div>
                    <div class="history-item">
                        <span class="history-label">Jam Pulang</span>
                        <span class="history-value"><?php echo $absen_pulang ? $absen_pulang->format('H:i:s') : '-'; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <nav class="mt-4">
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

<div class="bottom-nav">
    <a href="/dashboard" class="nav-item-bottom">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Home</span>
    </a>
    <a href="/riwayat-absensi" class="nav-item-bottom active">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>Riwayat</span>
    </a>

    <div class="fab-absen" data-bs-toggle="modal" data-bs-target="#actionModal">
        <i class="bi bi-fingerprint"></i>
    </div>

    <a href="#" class="nav-item-bottom" data-bs-toggle="modal" data-bs-target="#modalPanduan">
        <i class="bi bi-book-half"></i>
        <span>Panduan</span>
    </a>
    <a href="#" class="nav-item-bottom" data-bs-toggle="modal" data-bs-target="#modalPengaturan">
        <i class="bi bi-gear-fill"></i>
        <span>Akun</span>
    </a>
</div>

<?php
require_once 'partials/modal_absen.php';
require_once 'partials/modal_dinas_luar.php';
require_once 'partials/modal_pengaturan.php';
require_once 'partials/modal_panduan.php';
?>
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="actionModalLabel">Pilih Aksi Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body action-buttons">
            <button class="btn btn-masuk <?php if(!$bisa_absen_masuk) echo 'disabled'; ?>" <?php if(!$bisa_absen_masuk) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalAbsen" onclick="bukaModalAbsen('Masuk')">
                <i class="bi bi-box-arrow-in-right"></i> Absen Masuk
            </button>
            <button class="btn btn-pulang <?php if(!$bisa_absen_pulang) echo 'disabled'; ?>" <?php if(!$bisa_absen_pulang) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalAbsen" onclick="bukaModalAbsen('Pulang')">
                <i class="bi bi-box-arrow-right"></i> Absen Pulang
            </button>
            <button class="btn btn-dl <?php if(!$bisa_dinas_luar) echo 'disabled'; ?>" <?php if(!$bisa_dinas_luar) echo 'disabled'; ?> data-bs-toggle="modal" data-bs-target="#modalDinasLuar">
                <i class="bi bi-briefcase-fill"></i> Dinas Luar
            </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js"></script>
</body>
</html>