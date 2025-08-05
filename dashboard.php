<?php
session_start();
require_once 'config/database.php';

// Redirect jika belum login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: index.php");
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];
$hari_ini = date('Y-m-d');

$sql_cek = "SELECT tipe_absensi FROM tabel_absensi 
            WHERE id_pegawai = ? AND DATE(waktu_absensi) = ? 
            ORDER BY waktu_absensi DESC LIMIT 1";

$stmt_cek = mysqli_prepare($koneksi, $sql_cek);
mysqli_stmt_bind_param($stmt_cek, "is", $id_pegawai, $hari_ini);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

$status_terakhir = null;
if ($row = mysqli_fetch_assoc($result_cek)) {
    $status_terakhir = $row['tipe_absensi'];
}

$bisa_absen_masuk = ($status_terakhir === null);
$bisa_absen_pulang = ($status_terakhir === 'Masuk');
$sudah_selesai = ($status_terakhir === 'Pulang' || $status_terakhir === 'Dinas Luar');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-menu {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .disabled-card {
            pointer-events: none;
            opacity: 0.6;
        }
        .icon-lg {
            font-size: 3rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">Aplikasi Absensi</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a class="nav-link" href="auth/logout.php">Logout</a>
        </li>
    </ul>
  </div>
</nav>

<div class="container mt-4">
    <div class="alert alert-success">
        <h4 class="alert-heading">Selamat Datang!</h4>
        <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Silakan lakukan absensi.</p>
    </div>

    <div id="notifikasi" class="alert" style="display:none;"></div>
    
    <?php if ($sudah_selesai): ?>
        <div class="alert alert-info text-center">
            <h5>Anda sudah menyelesaikan absensi hari ini. Terima kasih.</h5>
        </div>
    <?php else: ?>
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_masuk) echo 'disabled-card'; ?>" onclick="bukaModalAbsen('Masuk')" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-in-right icon-lg text-success"></i>
                            <h5 class="card-title mt-3">Absen Masuk</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_pulang) echo 'disabled-card'; ?>" onclick="bukaModalAbsen('Pulang')" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-right icon-lg text-danger"></i>
                            <h5 class="card-title mt-3">Absen Pulang</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="#" class="card-menu <?php if(!$bisa_absen_masuk) echo 'disabled-card'; ?>" data-bs-toggle="modal" data-bs-target="#modalDinasLuar">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-briefcase-fill icon-lg text-warning"></i>
                            <h5 class="card-title mt-3">Dinas Luar Kota</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    <?php endif; ?>
    <hr class="my-4">

    <h4 class="mb-3">Laporan</h4>
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <a href="riwayat_absensi.php" class="card-menu">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-text-fill icon-lg text-primary"></i>
                        <h5 class="card-title mt-3">Data Absensi</h5>
                    </div>
                </div>
            </a>
        </div>
        <!-- === PERUBAHAN DI SINI === -->
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
        <div class="col-md-4 mb-3">
            <a href="admin/index.php" class="card-menu">
                <div class="card shadow-sm border-danger">
                    <div class="card-body">
                        <i class="bi bi-shield-lock-fill icon-lg text-danger"></i>
                        <h5 class="card-title mt-3">Panel Admin</h5>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <!-- === AKHIR PERUBAHAN === -->
    </div>
</div>

<div class="modal fade" id="modalAbsen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAbsenLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formAbsen" onsubmit="kirimAbsensi(event)">
            <input type="hidden" id="absenTipe" name="tipe">
            <input type="hidden" id="fotoBase64" name="foto">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Preview Kamera</label>
                    <video id="videoElement" width="100%" height="auto" autoplay playsinline></video>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-info" id="btnSwitchCamera">Ganti Kamera</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hasil Foto</label>
                    <canvas id="previewCanvas" style="display:none;"></canvas> 
                    <img id="previewImage" src="#" alt="Hasil Foto" style="display:none; width: 100%; height: auto;">
                    <p id="noFotoText" class="text-muted">Belum ada foto diambil.</p>
                </div>
            </div>
            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Wajib Diisi)</label>
                <textarea class="form-control" id="catatan" name="catatan" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnTakePhoto">Ambil Foto</button>
                <button type="submit" class="btn btn-success" id="btnSubmitAbsen" disabled>Kirim Absensi</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDinasLuar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Absensi Dinas Luar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formDinasLuar" onsubmit="kirimDinasLuar(event)">
            <div class="mb-3">
                <label for="surat_tugas" class="form-label">Upload Surat Tugas (PDF/JPG, maks 2MB)</label>
                <input class="form-control" type="file" id="surat_tugas" name="surat_tugas" accept=".pdf,.jpg,.jpeg" required>
            </div>
            <div class="mb-3">
                <label for="keterangan_dinas" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan_dinas" name="keterangan" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Elemen DOM ---
    const videoElement = document.getElementById('videoElement');
    const previewCanvas = document.getElementById('previewCanvas');
    const previewImage = document.getElementById('previewImage');
    const noFotoText = document.getElementById('noFotoText');
    const btnSwitchCamera = document.getElementById('btnSwitchCamera');
    const btnTakePhoto = document.getElementById('btnTakePhoto');
    const btnSubmitAbsen = document.getElementById('btnSubmitAbsen');
    const fotoBase64Input = document.getElementById('fotoBase64');
    const modalAbsen = document.getElementById('modalAbsen');

    let currentStream;
    let facingMode = 'user'; // 'user' untuk kamera depan, 'environment' untuk belakang

    // --- Fungsi Kamera ---
    function startCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            const constraints = { video: { facingMode: facingMode } };
            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    currentStream = stream;
                    videoElement.srcObject = stream;
                })
                .catch(error => {
                    console.error('Error accessing camera:', error);
                    alert('Gagal mengakses kamera. Pastikan izin kamera diberikan dan tidak ada aplikasi lain yang menggunakan kamera.');
                });
        }
    }

    function stopCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
    }

    function switchCamera() {
        stopCamera();
        facingMode = facingMode === 'user' ? 'environment' : 'user';
        startCamera();
    }

    function takePhoto() {
        const canvasContext = previewCanvas.getContext('2d');
        previewCanvas.width = videoElement.videoWidth;
        previewCanvas.height = videoElement.videoHeight;
        canvasContext.drawImage(videoElement, 0, 0, previewCanvas.width, previewCanvas.height);

        // --- Logika Watermark Baru ---
        const now = new Date();
        const waktu = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        const tanggal = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' });
        const lokasi = 'Surabaya, Jawa Timur';
        
        const padding = 10;
        const lineHeight = 25;
        const boxY = previewCanvas.height - (lineHeight * 3) - (padding * 2);

        // Latar Belakang
        canvasContext.fillStyle = 'rgba(0, 0, 0, 0.5)';
        canvasContext.fillRect(0, boxY, previewCanvas.width, (lineHeight * 3) + (padding * 2));
        
        // Teks
        canvasContext.font = `bold ${lineHeight - 5}px Arial`;
        canvasContext.fillStyle = 'white';
        canvasContext.fillText(waktu, padding, boxY + padding + lineHeight * 0.8);
        canvasContext.fillText(tanggal, padding, boxY + padding + lineHeight * 1.8);
        canvasContext.fillText(lokasi, padding, boxY + padding + lineHeight * 2.8);

        // --- Akhir Logika Watermark ---

        const base64Image = previewCanvas.toDataURL('image/jpeg');
        previewImage.src = base64Image;
        fotoBase64Input.value = base64Image;

        previewImage.style.display = 'block'; // Tampilkan hasil foto
        noFotoText.style.display = 'none';
        btnSubmitAbsen.disabled = false; // Aktifkan tombol kirim
    }

    // --- Event Listeners & Fungsi Modal ---
    modalAbsen.addEventListener('shown.bs.modal', startCamera);
    modalAbsen.addEventListener('hidden.bs.modal', stopCamera);
    btnSwitchCamera.addEventListener('click', switchCamera);
    btnTakePhoto.addEventListener('click', takePhoto);

    function bukaModalAbsen(tipe) {
        document.getElementById('modalAbsenLabel').textContent = 'Konfirmasi Absen ' + tipe;
        document.getElementById('absenTipe').value = tipe;
        document.getElementById('catatan').value = '';
        previewImage.style.display = 'none';
        noFotoText.style.display = 'block';
        fotoBase64Input.value = '';
        btnSubmitAbsen.disabled = true;
    }

    function kirimAbsensi(event) {
        event.preventDefault();
        const notifikasi = document.getElementById('notifikasi');
        const form = document.getElementById('formAbsen');
        const catatan = document.getElementById('catatan').value;
        const fotoBase64 = fotoBase64Input.value;

        if (catatan.trim() === '') {
            alert('Catatan wajib diisi.');
            return;
        }
        if (!fotoBase64) {
            alert('Anda harus mengambil foto.');
            return;
        }

        notifikasi.style.display = 'block';
        notifikasi.className = 'alert alert-info';
        notifikasi.textContent = 'Sedang memproses absensi...';
        
        const modal = bootstrap.Modal.getInstance(modalAbsen);
        modal.hide();

        navigator.geolocation.getCurrentPosition(posisi => {
            const data = {
                tipe: document.getElementById('absenTipe').value,
                latitude: posisi.coords.latitude,
                longitude: posisi.coords.longitude,
                catatan: catatan,
                foto: fotoBase64
            };

            fetch('proses_absensi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(hasil => {
                notifikasi.className = hasil.sukses ? 'alert alert-success' : 'alert alert-danger';
                notifikasi.textContent = hasil.pesan;
                if (hasil.sukses) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            });
        }, () => {
            notifikasi.className = 'alert alert-danger';
            notifikasi.textContent = 'Gagal mengambil lokasi. Pastikan izin lokasi diberikan.';
        });
    }

    // Fungsi kirimDinasLuar tidak diubah
    function kirimDinasLuar(event) {
        event.preventDefault(); 
        const notifikasi = document.getElementById('notifikasi');
        const form = document.getElementById('formDinasLuar');
        const suratInput = document.getElementById('surat_tugas');
        const file = suratInput.files[0];

        if (!file) {
            alert('Anda harus memilih file surat tugas.');
            return;
        }
        notifikasi.style.display = 'block';
        notifikasi.className = 'alert alert-info';
        notifikasi.textContent = 'Sedang mengambil lokasi dan mengunggah file...';
        navigator.geolocation.getCurrentPosition(posisi => {
            const formData = new FormData(form);
            formData.append('latitude', posisi.coords.latitude);
            formData.append('longitude', posisi.coords.longitude);
            fetch('proses_dinas_luar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(hasil => {
                if (hasil.sukses) {
                    notifikasi.className = 'alert alert-success';
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDinasLuar'));
                    modal.hide();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    notifikasi.className = 'alert alert-danger';
                }
                notifikasi.textContent = hasil.pesan;
            });
        }, () => {
            notifikasi.className = 'alert alert-danger';
            notifikasi.textContent = 'Gagal mengambil lokasi. Pastikan Anda mengizinkan akses lokasi.';
        });
    }
</script>

</body>
</html>