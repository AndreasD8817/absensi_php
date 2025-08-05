<?php
session_start();
// Jika sudah ada sesi, redirect ke dashboard
if (isset($_SESSION['id_pegawai'])) {
    header("Location: /absensi_php/dashboard");
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
    
    <!-- Custom CSS for the new look -->
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background-color: #ffffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .input-group-text {
            background-color: transparent;
            border: none;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
        }
        .btn-login {
            border-radius: 5px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            background-color: #007bff;
            border: none;
        }
        .btn-login:hover {
            background-color: #0069d9;
        }
        .password-container {
            position: relative;
        }
    </style>
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
    
    <form action="/absensi_php/auth/proses-login" method="POST">
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