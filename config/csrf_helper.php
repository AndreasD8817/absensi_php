<?php
// config/csrf_helper.php

/**
 * Memulai sesi jika belum ada.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menghasilkan token CSRF baru jika belum ada di sesi.
 * Token ini akan digunakan untuk semua form selama sesi pengguna aktif.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        // Membuat token acak yang aman secara kriptografis
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Memvalidasi token CSRF yang dikirimkan.
 * @param string $token Token yang diterima dari form atau URL.
 * @return bool True jika valid, false jika tidak.
 */
function validate_csrf_token($token) {
    // Pastikan token yang diterima tidak kosong dan cocok dengan yang ada di sesi
    if (!empty($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Menghasilkan input hidden field untuk form HTML.
 * Cukup panggil fungsi ini di dalam tag <form>.
 */
function csrf_input_field() {
    generate_csrf_token(); // Pastikan token sudah ada
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Menghasilkan query string untuk URL.
 * Berguna untuk link aksi seperti hapus data.
 */
function csrf_query_string() {
    generate_csrf_token(); // Pastikan token sudah ada
    return 'csrf_token=' . urlencode($_SESSION['csrf_token']);
}