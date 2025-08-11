<?php
// Mengirim header 503 Service Unavailable, ini baik untuk SEO
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situs dalam Perbaikan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .maintenance-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .maintenance-card {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .maintenance-icon {
            font-size: 5rem;
            color: #ffc107; /* Warna kuning warning */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="maintenance-container">
            <div class="maintenance-card">
                <img src="/assets/img/farid.png" alt="Logo Perbaikan" class="maintenance-image">
                
                <h1 class="display-5 mt-3">Situs Sedang dalam Perbaikan</h1>
                <p class="lead text-muted">
                    Mohon maaf atas ketidaknyamanannya. Kami sedang melakukan beberapa pembaruan pada sistem.
                </p>
                <p>Situs akan segera kembali normal. Terima kasih atas pengertian Anda.</p>
            </div>
        </div>
    </div>
</body>
</html>