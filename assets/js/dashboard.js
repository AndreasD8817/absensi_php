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

let currentStream;
let facingMode = "user"; // 'user' untuk kamera depan, 'environment' untuk belakang

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
        alert(
          "Gagal mengakses kamera. Pastikan izin kamera diberikan dan tidak ada aplikasi lain yang menggunakan kamera."
        );
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

  // --- Logika Watermark Baru ---
  const now = new Date();
  const waktu = now.toLocaleTimeString("id-ID", { hour12: false }) + " WIB";
  const tanggal = now.toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
  const lokasi = "Surabaya, Jawa Timur";

  const padding = 10;
  const lineHeight = 25;
  const boxY = previewCanvas.height - lineHeight * 3 - padding * 2;

  // Latar Belakang
  canvasContext.fillStyle = "rgba(0, 0, 0, 0.5)";
  canvasContext.fillRect(
    0,
    boxY,
    previewCanvas.width,
    lineHeight * 3 + padding * 2
  );

  // Teks
  canvasContext.font = `bold ${lineHeight - 5}px Arial`;
  canvasContext.fillStyle = "white";
  canvasContext.fillText(waktu, padding, boxY + padding + lineHeight * 0.8);
  canvasContext.fillText(tanggal, padding, boxY + padding + lineHeight * 1.8);
  canvasContext.fillText(lokasi, padding, boxY + padding + lineHeight * 2.8);

  // --- Akhir Logika Watermark ---

  const base64Image = previewCanvas.toDataURL("image/jpeg");
  previewImage.src = base64Image;
  fotoBase64Input.value = base64Image;

  previewImage.style.display = "block"; // Tampilkan hasil foto
  noFotoText.style.display = "none";
  btnSubmitAbsen.disabled = false; // Aktifkan tombol kirim
}

// --- Event Listeners & Fungsi Modal ---
modalAbsen.addEventListener("shown.bs.modal", startCamera);
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

  navigator.geolocation.getCurrentPosition(
    (posisi) => {
      const data = {
        tipe: document.getElementById("absenTipe").value,
        latitude: posisi.coords.latitude,
        longitude: posisi.coords.longitude,
        catatan: catatan,
        foto: fotoBase64,
      };

      fetch("public/proses_absensi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
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
        "Gagal mengambil lokasi. Pastikan izin lokasi diberikan.";
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
      fetch("proses_dinas_luar.php", {
        method: "POST",
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
