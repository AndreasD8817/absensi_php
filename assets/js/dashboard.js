// assets/js/dashboard.js

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
const alamatTerdeteksiP = document.getElementById("alamatTerdeteksi");
const btnDapatkanLokasi = document.getElementById("btnDapatkanLokasi");

let currentStream;
let facingMode = "user";
let alamatUntukWatermark = "Lokasi tidak terdeteksi";
let lokasiSudahDidapat = false;

// --- Fungsi Geolocation & Reverse Geocoding ---
function getAlamatFromCoords(lat, lon) {
  const apiUrl = `https://geocode.maps.co/reverse?lat=${lat}&lon=${lon}`;
  alamatTerdeteksiP.innerHTML =
    "<i>Sedang menerjemahkan koordinat ke alamat...</i>";

  fetch(apiUrl)
    .then((response) => response.json())
    .then((data) => {
      if (data && data.display_name) {
        alamatUntukWatermark = data.display_name;
        alamatTerdeteksiP.textContent = alamatUntukWatermark;
      } else {
        alamatTerdeteksiP.textContent =
          "Alamat detail tidak ditemukan, namun koordinat Anda berhasil direkam.";
        alamatUntukWatermark = `Lat: ${lat}, Lon: ${lon}`;
      }
    })
    // ================== PERUBAHAN UTAMA DI SINI ==================
    .catch((error) => {
      // Jika fetch gagal (misal: tidak ada internet), jangan tampilkan error.
      // Langsung tampilkan koordinat saja.
      console.error("Error fetching reverse geocoding:", error);
      const koordinatText = `Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}`;

      // Langsung tampilkan koordinat jika gagal mendapatkan nama alamat
      alamatTerdeteksiP.textContent = koordinatText;
      alamatUntukWatermark = koordinatText; // Pastikan watermark juga berisi koordinat
    });
  // =============================================================
}

// --- Fungsi untuk mendapatkan lokasi DENGAN TIMEOUT ---
function getLokasiPengguna() {
  alamatTerdeteksiP.innerHTML =
    "<i><i class='bi bi-arrow-repeat'></i> Mencari lokasi Anda, mohon tunggu...</i>";
  btnDapatkanLokasi.disabled = true;
  btnDapatkanLokasi.innerHTML =
    '<i class="bi bi-hourglass-split"></i> Mencari...';
  btnTakePhoto.disabled = true;

  if (navigator.geolocation) {
    const options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0,
    };

    navigator.geolocation.getCurrentPosition(
      (posisi) => {
        getAlamatFromCoords(posisi.coords.latitude, posisi.coords.longitude);
        lokasiSudahDidapat = true;
        btnDapatkanLokasi.disabled = false;
        btnDapatkanLokasi.innerHTML =
          '<i class="bi bi-check-circle-fill"></i> Lokasi Ditemukan';
        btnDapatkanLokasi.classList.remove("btn-outline-primary");
        btnDapatkanLokasi.classList.add("btn-success");
        btnTakePhoto.disabled = false;
      },
      (error) => {
        lokasiSudahDidapat = false;
        let pesanError = "";
        if (error.code === error.PERMISSION_DENIED) {
          pesanError =
            "Anda menolak izin lokasi. Silakan izinkan akses lokasi di pengaturan browser Anda.";
        } else if (error.code === error.POSITION_UNAVAILABLE) {
          pesanError =
            "Informasi lokasi tidak tersedia saat ini. Coba lagi nanti.";
        } else if (error.code === error.TIMEOUT) {
          pesanError =
            "Tidak berhasil mendapatkan lokasi dalam 10 detik. Pastikan Anda berada di area dengan sinyal GPS yang baik dan coba lagi.";
        } else {
          pesanError =
            "Terjadi kesalahan yang tidak diketahui saat mengambil lokasi.";
        }

        alamatTerdeteksiP.textContent = pesanError;
        alert(pesanError);

        btnDapatkanLokasi.disabled = false;
        btnDapatkanLokasi.innerHTML =
          '<i class="bi bi-geo-alt-fill"></i> Dapatkan Lokasi';
        btnDapatkanLokasi.classList.remove("btn-success");
        btnDapatkanLokasi.classList.add("btn-outline-primary");
      },
      options
    );
  } else {
    alamatTerdeteksiP.textContent =
      "Fitur Geolokasi tidak didukung oleh browser ini.";
    lokasiSudahDidapat = false;
    btnDapatkanLokasi.disabled = false;
    btnDapatkanLokasi.innerHTML =
      '<i class="bi bi-geo-alt-fill"></i> Dapatkan Lokasi';
  }
}

// --- Fungsi Kamera (Tidak ada perubahan) ---
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

  const now = new Date();
  const waktu = now.toLocaleTimeString("id-ID", { hour12: false }) + " WIB";
  const tanggal = now.toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
  const lokasi = alamatUntukWatermark;

  const padding = 15;
  const fontSize = previewCanvas.width / 45;
  const lineHeight = fontSize * 1.5;
  const maxWidth = previewCanvas.width - padding * 2;

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

  canvasContext.font = `bold ${fontSize}px Arial`;
  const alamatLines = wrapText(lokasi);
  const totalLines = 2 + alamatLines.length;
  const boxHeight = lineHeight * totalLines + padding * 1.5;
  const boxY = previewCanvas.height - boxHeight;

  canvasContext.fillStyle = "rgba(0, 0, 0, 0.6)";
  canvasContext.fillRect(0, boxY, previewCanvas.width, boxHeight);

  canvasContext.fillStyle = "white";
  let currentY = boxY + padding + fontSize / 2;

  canvasContext.fillText(waktu, padding, currentY);
  currentY += lineHeight;
  canvasContext.fillText(tanggal, padding, currentY);
  currentY += lineHeight;

  alamatLines.forEach((line) => {
    canvasContext.fillText(line, padding, currentY);
    currentY += lineHeight;
  });

  const base64Image = previewCanvas.toDataURL("image/jpeg");
  previewImage.src = base64Image;
  fotoBase64Input.value = base64Image;

  previewImage.style.display = "block";
  noFotoText.style.display = "none";
  btnSubmitAbsen.disabled = false;
}

// --- Event Listeners & Fungsi Modal ---
modalAbsen.addEventListener("shown.bs.modal", startCamera);
modalAbsen.addEventListener("hidden.bs.modal", stopCamera);
btnSwitchCamera.addEventListener("click", switchCamera);
btnTakePhoto.addEventListener("click", takePhoto);
btnDapatkanLokasi.addEventListener("click", getLokasiPengguna);

function bukaModalAbsen(tipe) {
  document.getElementById("modalAbsenLabel").textContent =
    "Konfirmasi Absen " + tipe;
  document.getElementById("absenTipe").value = tipe;
  document.getElementById("catatan").value = "";
  previewImage.style.display = "none";
  noFotoText.style.display = "block";
  fotoBase64Input.value = "";

  lokasiSudahDidapat = false;
  alamatTerdeteksiP.innerHTML =
    "<i>Klik 'Dapatkan Lokasi' untuk memulai...</i>";

  btnDapatkanLokasi.disabled = false;
  btnDapatkanLokasi.innerHTML =
    '<i class="bi bi-geo-alt-fill"></i> Dapatkan Lokasi';
  btnDapatkanLokasi.classList.remove("btn-success");
  btnDapatkanLokasi.classList.add("btn-outline-primary");

  btnTakePhoto.disabled = true;
  btnSubmitAbsen.disabled = true;
}

function kirimAbsensi(event) {
  event.preventDefault();
  const notifikasi = document.getElementById("notifikasi");
  const form = document.getElementById("formAbsen");
  const catatan = document.getElementById("catatan").value;
  const fotoBase64 = fotoBase64Input.value;

  if (!lokasiSudahDidapat) {
    alert("Anda harus mendapatkan lokasi terlebih dahulu.");
    return;
  }
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

  navigator.geolocation.getCurrentPosition(
    (posisi) => {
      const data = {
        tipe: document.getElementById("absenTipe").value,
        latitude: posisi.coords.latitude,
        longitude: posisi.coords.longitude,
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
      const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

      fetch("/proses-dinas-luar", {
        method: "POST",
        headers: {
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
