// --- Elemen DOM ---
const videoElement = document.getElementById("videoElement");
const previewCanvas = document.getElementById("previewCanvas");
const previewImage = document.getElementById("previewImage");
const noFotoText = document.getElementById("noFotoText");
const btnSwitchCamera = document.getElementById("btnSwitchCamera");
const btnTakePhoto = document.getElementById("btnTakePhoto");
const btnSubmitAbsen = document.getElementById("btnSubmitAbsen");
const fotoBase64Input = document.getElementById("fotoBase64");
const modalAbsen = document.getElementById("modalAbsen");
// Elemen baru untuk alamat
const alamatTerdeteksiP = document.getElementById("alamatTerdeteksi");
// ======================= ELEMEN BARU =======================
const btnRefreshLokasi = document.getElementById("btnRefreshLokasi");
// ==========================================================

let currentStream;
let facingMode = "user"; // 'user' untuk kamera depan, 'environment' untuk belakang
// Variabel global untuk menyimpan alamat yang sudah dideteksi
let alamatUntukWatermark = "Lokasi tidak terdeteksi";

// --- Fungsi Geolocation & Reverse Geocoding ---
// --- Fungsi Geolocation & Reverse Geocoding (Dengan Teks Baru) ---
function getAlamatFromCoords(lat, lon) {
  const apiUrl = `https://geocode.maps.co/reverse?lat=${lat}&lon=${lon}`;

  // Teks diubah menjadi lebih ramah
  alamatTerdeteksiP.innerHTML =
    "<i>Sedang menerjemahkan koordinat ke alamat...</i>";

  fetch(apiUrl)
    .then((response) => response.json())
    .then((data) => {
      if (data && data.display_name) {
        alamatUntukWatermark = data.display_name;
        alamatTerdeteksiP.textContent = alamatUntukWatermark;
      } else {
        // Teks diubah menjadi lebih ramah
        alamatTerdeteksiP.textContent =
          "Alamat detail tidak ditemukan, namun koordinat Anda berhasil direkam.";
        alamatUntukWatermark = `Lat: ${lat}, Lon: ${lon}`;
      }
    })
    .catch((error) => {
      console.error("Error fetching reverse geocoding:", error);
      // Teks diubah menjadi lebih ramah
      alamatTerdeteksiP.textContent =
        "Gagal mendapatkan nama alamat. Periksa koneksi internet Anda dan coba lagi.";
      alamatUntukWatermark = `Lat: ${lat}, Lon: ${lon}`;
    });
}

// --- Fungsi untuk mendapatkan lokasi (Dengan Logika Refresh) ---
// Ganti fungsi getLokasiPengguna yang lama dengan yang ini

// Ganti fungsi getLokasiPengguna yang lama dengan versi baru ini

// Ganti fungsi getLokasiPengguna yang lama dengan versi baru ini

function getLokasiPengguna() {
  // Langkah 1: Beri umpan balik visual INSTAN
  alamatTerdeteksiP.innerHTML = "<i>Loading....</i>";
  btnRefreshLokasi.disabled = true;
  btnRefreshLokasi.innerHTML = '<i class="bi bi-arrow-repeat"></i> Mencari...';

  // Langkah 2: Gunakan setTimeout untuk membuat jeda yang terasa
  setTimeout(() => {
    if (navigator.geolocation) {
      // Langkah 3: Mulai proses pencarian lokasi setelah jeda
      navigator.geolocation.getCurrentPosition(
        (posisi) => {
          // Jika berhasil, panggil fungsi untuk dapatkan nama alamat
          getAlamatFromCoords(posisi.coords.latitude, posisi.coords.longitude);

          // Aktifkan kembali tombol
          btnRefreshLokasi.disabled = false;
          btnRefreshLokasi.innerHTML =
            '<i class="bi bi-arrow-repeat"></i> Refresh';
        },
        () => {
          // Jika gagal, tampilkan pesan error
          alamatTerdeteksiP.textContent =
            "Lokasi gagal dideteksi. Pastikan izin lokasi sudah diberikan dan coba lagi.";
          alert(
            "Gagal mengambil lokasi. Pastikan izin lokasi pada browser Anda sudah diaktifkan untuk situs ini."
          );

          // Aktifkan kembali tombol
          btnRefreshLokasi.disabled = false;
          btnRefreshLokasi.innerHTML =
            '<i class="bi bi-arrow-repeat"></i> Refresh';
        }
      );
    } else {
      alamatTerdeteksiP.textContent =
        "Fitur Geolokasi tidak didukung oleh browser ini.";

      // Aktifkan kembali tombol
      btnRefreshLokasi.disabled = false;
      btnRefreshLokasi.innerHTML = '<i class="bi bi-arrow-repeat"></i> Refresh';
    }
  }, 500); // Jeda 500 milidetik (setengah detik)
}

// --- Fungsi Kamera ---
function startCamera() {
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    const constraints = { video: { facingMode: facingMode } };
    navigator.mediaDevices
      .getUserMedia(constraints)
      .then((stream) => {
        currentStream = stream;
        videoElement.srcObject = stream;
      })
      .catch((error) => {
        console.error("Error accessing camera:", error);
        alert("Gagal mengakses kamera. Pastikan izin kamera diberikan.");
      });
  }
}

function stopCamera() {
  if (currentStream) {
    currentStream.getTracks().forEach((track) => track.stop());
  }
}

function switchCamera() {
  stopCamera();
  facingMode = facingMode === "user" ? "environment" : "user";
  startCamera();
}

/**
 * ==================================================================
 * === FUNGSI takePhoto() DENGAN LOGIKA TEXT WRAPPING (PERUBAHAN) ===
 * ==================================================================
 */
function takePhoto() {
  const canvasContext = previewCanvas.getContext("2d");
  previewCanvas.width = videoElement.videoWidth;
  previewCanvas.height = videoElement.videoHeight;
  canvasContext.drawImage(
    videoElement,
    0,
    0,
    previewCanvas.width,
    previewCanvas.height
  );

  // --- Data untuk Watermark ---
  const now = new Date();
  const waktu = now.toLocaleTimeString("id-ID", { hour12: false }) + " WIB";
  const tanggal = now.toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
  const lokasi = alamatUntukWatermark;

  // --- Pengaturan Watermark ---
  const padding = 15;
  const fontSize = previewCanvas.width / 45; // Ukuran font dibuat sedikit lebih kecil
  const lineHeight = fontSize * 1.5;
  const maxWidth = previewCanvas.width - padding * 2;

  // --- Helper function untuk membungkus teks ---
  const wrapText = (text) => {
    let lines = [];
    let currentLine = "";
    const words = text.split(" ");

    for (const word of words) {
      const testLine = currentLine + word + " ";
      const metrics = canvasContext.measureText(testLine);
      if (metrics.width > maxWidth && currentLine.length > 0) {
        lines.push(currentLine.trim());
        currentLine = word + " ";
      } else {
        currentLine = testLine;
      }
    }
    lines.push(currentLine.trim());
    return lines;
  };

  // --- Logika Watermark ---
  canvasContext.font = `bold ${fontSize}px Arial`;
  const alamatLines = wrapText(lokasi); // Pecah alamat menjadi beberapa baris

  // Hitung total tinggi background yang dibutuhkan
  const totalLines = 2 + alamatLines.length; // 2 untuk waktu & tanggal
  const boxHeight = lineHeight * totalLines + padding * 1.5;
  const boxY = previewCanvas.height - boxHeight;

  // Gambar latar belakang watermark
  canvasContext.fillStyle = "rgba(0, 0, 0, 0.6)";
  canvasContext.fillRect(0, boxY, previewCanvas.width, boxHeight);

  // Tulis teks watermark baris per baris
  canvasContext.fillStyle = "white";
  let currentY = boxY + padding + fontSize / 2; // Posisi Y awal

  canvasContext.fillText(waktu, padding, currentY);
  currentY += lineHeight; // Pindah ke baris berikutnya

  canvasContext.fillText(tanggal, padding, currentY);
  currentY += lineHeight; // Pindah ke baris berikutnya

  // Tulis setiap baris alamat
  alamatLines.forEach((line) => {
    canvasContext.fillText(line, padding, currentY);
    currentY += lineHeight;
  });

  // --- Akhir Logika Watermark ---

  const base64Image = previewCanvas.toDataURL("image/jpeg");
  previewImage.src = base64Image;
  fotoBase64Input.value = base64Image;

  previewImage.style.display = "block";
  noFotoText.style.display = "none";
  btnSubmitAbsen.disabled = false;
}

// --- Event Listeners & Fungsi Modal ---
modalAbsen.addEventListener("shown.bs.modal", () => {
  startCamera();
  getLokasiPengguna();
});
modalAbsen.addEventListener("hidden.bs.modal", stopCamera);
btnSwitchCamera.addEventListener("click", switchCamera);
btnTakePhoto.addEventListener("click", takePhoto);

function bukaModalAbsen(tipe) {
  document.getElementById("modalAbsenLabel").textContent =
    "Konfirmasi Absen " + tipe;
  document.getElementById("absenTipe").value = tipe;
  document.getElementById("catatan").value = "";
  previewImage.style.display = "none";
  noFotoText.style.display = "block";
  fotoBase64Input.value = "";
  btnSubmitAbsen.disabled = true;
  alamatTerdeteksiP.innerHTML = "<i>Menunggu data lokasi...</i>";
}

function kirimAbsensi(event) {
  event.preventDefault();
  const notifikasi = document.getElementById("notifikasi");
  const form = document.getElementById("formAbsen");
  const catatan = document.getElementById("catatan").value;
  const fotoBase64 = fotoBase64Input.value;

  if (catatan.trim() === "") {
    alert("Catatan wajib diisi.");
    return;
  }
  if (!fotoBase64) {
    alert("Anda harus mengambil foto.");
    return;
  }

  notifikasi.style.display = "block";
  notifikasi.className = "alert alert-info";
  notifikasi.textContent = "Sedang memproses absensi...";

  const modal = bootstrap.Modal.getInstance(modalAbsen);
  modal.hide();

  // Kita tetap perlu mengambil koordinat untuk dikirim ke backend
  navigator.geolocation.getCurrentPosition(
    (posisi) => {
      const data = {
        tipe: document.getElementById("absenTipe").value,
        latitude: posisi.coords.latitude,
        longitude: posisi.coords.longitude,
        // TIDAK ADA "alamat" di sini
        catatan: catatan,
        foto: fotoBase64,
      };

      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

      fetch("public/proses_absensi.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(data),
      })
        .then((response) => response.json())
        .then((hasil) => {
          notifikasi.className = hasil.sukses
            ? "alert alert-success"
            : "alert alert-danger";
          notifikasi.textContent = hasil.pesan;
          if (hasil.sukses) {
            setTimeout(() => window.location.reload(), 1500);
          }
        });
    },
    () => {
      notifikasi.className = "alert alert-danger";
      notifikasi.textContent =
        "Gagal mengambil lokasi untuk pengiriman. Pastikan izin lokasi diberikan.";
    }
  );
}

// Fungsi kirimDinasLuar tidak diubah
function kirimDinasLuar(event) {
  event.preventDefault();
  const notifikasi = document.getElementById("notifikasi");
  const form = document.getElementById("formDinasLuar");
  const suratInput = document.getElementById("surat_tugas");
  const file = suratInput.files[0];

  if (!file) {
    alert("Anda harus memilih file surat tugas.");
    return;
  }
  notifikasi.style.display = "block";
  notifikasi.className = "alert alert-info";
  notifikasi.textContent = "Sedang mengambil lokasi dan mengunggah file...";
  navigator.geolocation.getCurrentPosition(
    (posisi) => {
      const formData = new FormData(form);
      formData.append("latitude", posisi.coords.latitude);
      formData.append("longitude", posisi.coords.longitude);
      // Ambil CSRF token dari meta tag
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

      fetch("/proses-dinas-luar", {
        method: "POST",
        headers: {
          // TAMBAHKAN HEADER BARU (tidak perlu Content-Type untuk FormData)
          "X-CSRF-TOKEN": csrfToken,
        },
        body: formData,
      })
        .then((response) => response.json())
        .then((hasil) => {
          if (hasil.sukses) {
            notifikasi.className = "alert alert-success";
            const modal = bootstrap.Modal.getInstance(
              document.getElementById("modalDinasLuar")
            );
            modal.hide();
            setTimeout(() => window.location.reload(), 1500);
          } else {
            notifikasi.className = "alert alert-danger";
          }
          notifikasi.textContent = hasil.pesan;
        });
    },
    () => {
      notifikasi.className = "alert alert-danger";
      notifikasi.textContent =
        "Gagal mengambil lokasi. Pastikan Anda mengizinkan akses lokasi.";
    }
  );
}
