<?php
session_start();
// Jika sudah ada sesi, redirect ke dashboard
if (isset($_SESSION['id_pegawai'])) {
    header("Location: /dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi Pegawai</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons for the 'eye' icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/login.css">
    
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="/assets/img/logo/logo.png" alt="Logo" width="100">
    </div>
    <h4 class="login-title">LOGIN ABSENSI PEGAWAI</h4>
    
    <?php
    // Tampilkan pesan error jika ada
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    ?>
    
    <form action="/auth/proses-login" method="POST">
        <div class="mb-3">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
        </div>
        
        <div class="mb-3 password-container">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <span class="input-group-text" id="togglePassword">
                <i class="bi bi-eye-slash" id="toggleIcon"></i>
            </span>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-login">LOGIN</button>
        </div>
    </form>
</div>

<!-- JavaScript for password toggle -->
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the icon
        if (type === 'password') {
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    });
</script>

</body>
</html>