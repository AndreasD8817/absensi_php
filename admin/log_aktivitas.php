<?php
$page_title = 'Log Aktivitas Sistem';
require_once 'partials/header.php';

// "Penjaga Gerbang" Super Admin
if ($_SESSION['role'] != 'superadmin') {
    header("Location: /login?error=Akses ditolak.");
    exit();
}

// --- LOGIKA PENGAMBILAN DATA & FILTER ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$id_pegawai_filter = isset($_GET['id_pegawai']) ? (int)$_GET['id_pegawai'] : 0;
$tanggal_awal = isset($_GET['tanggal_awal']) && !empty($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

$pegawai_list = [];
$sql_pegawai = "SELECT id_pegawai, nama_lengkap FROM tabel_pegawai ORDER BY nama_lengkap ASC";
$result_pegawai = mysqli_query($koneksi, $sql_pegawai);
while($row = mysqli_fetch_assoc($result_pegawai)) {
    $pegawai_list[] = $row;
}

// Logika Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// --- BANGUN QUERY DINAMIS BERDASARKAN FILTER ---
$params = [];
$types = '';
$where_clauses = [];
$sql_base = "FROM log_aktivitas l JOIN tabel_pegawai p ON l.id_pegawai = p.id_pegawai";

if (!empty($keyword)) {
    $where_clauses[] = "l.aktivitas LIKE ?";
    $params[] = "%" . $keyword . "%";
    $types .= 's';
}
if ($id_pegawai_filter > 0) {
    $where_clauses[] = "l.id_pegawai = ?";
    $params[] = $id_pegawai_filter;
    $types .= 'i';
}
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $where_clauses[] = "DATE(l.waktu_log) BETWEEN ? AND ?";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
    $types .= 'ss';
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

$sql_total = "SELECT COUNT(*) " . $sql_base . $where_sql;
$stmt_total = mysqli_prepare($koneksi, $sql_total);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_total, $types, ...$params);
}
mysqli_stmt_execute($stmt_total);
$total_records = mysqli_stmt_get_result($stmt_total)->fetch_row()[0];
$total_pages = ceil($total_records / $limit);

$sql_data = "SELECT l.*, p.nama_lengkap " . $sql_base . $where_sql . " ORDER BY l.waktu_log DESC LIMIT ? OFFSET ?";
$params_data = $params;
$params_data[] = $limit;
$params_data[] = $offset;
$types_data = $types . 'ii';

$stmt_data = mysqli_prepare($koneksi, $sql_data);
mysqli_stmt_bind_param($stmt_data, $types_data, ...$params_data);
mysqli_stmt_execute($stmt_data);
$result = mysqli_stmt_get_result($stmt_data);
?>

<style>
    .pagination .page-link {
        transition: all 0.3s;
    }
    .pagination .page-item.active .page-link {
        transform: scale(1.1);
        z-index: 2;
    }
    
    /* --- PERBAIKAN CSS DI SINI --- */

    /* Aturan HANYA untuk mencetak (print) */
    @media print {
        .no-print { display: none !important; }
        .card { border: none; box-shadow: none; }
        .table { font-size: 11px; }
    }

    /* Aturan HANYA untuk layar kecil (mobile) */
    @media (max-width: 767px) {
        .form-action-buttons {
            flex-direction: column;
        }
        .form-action-buttons .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .form-action-buttons .btn:last-child {
            margin-bottom: 0;
        }
        .table {
            font-size: 14px;
        }
    }
</style>

<div class="card">
    <div class="card-header no-print">
        <h4 class="card-title"><i class="bi bi-shield-check"></i> Audit & Log Aktivitas</h4>
    </div>
    <div class="card-body">
        
        <form action="" method="GET" class="mb-4 p-3 border rounded bg-light no-print">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <label for="keyword" class="form-label">Cari Aktivitas</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" placeholder="Cth: login, menghapus..." value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
                <div class="col-12 col-lg-4">
                    <label for="id_pegawai" class="form-label">Filter Pengguna</label>
                    <select id="id_pegawai" name="id_pegawai" class="form-select">
                        <option value="0">-- Semua Pengguna --</option>
                        <?php foreach ($pegawai_list as $pegawai): ?>
                            <option value="<?php echo $pegawai['id_pegawai']; ?>" <?php if($id_pegawai_filter == $pegawai['id_pegawai']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="tanggal_awal" class="form-label">Dari Tanggal</label>
                    <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" value="<?php echo htmlspecialchars($tanggal_awal); ?>">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <label for="tanggal_akhir" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" value="<?php echo htmlspecialchars($tanggal_akhir); ?>">
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2 form-action-buttons">
                <a href="/admin/log-aktivitas" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset Filter</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
                
                <?php
                    $export_query = http_build_query(array_filter([
                        'keyword' => $keyword,
                        'id_pegawai' => $id_pegawai_filter,
                        'tanggal_awal' => $tanggal_awal,
                        'tanggal_akhir' => $tanggal_akhir
                    ]));
                ?>
                <a href="/admin/export-log-csv?<?php echo $export_query; ?>" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Ekspor CSV</a>
                <button type="button" class="btn btn-info" onclick="window.print();"><i class="bi bi-printer"></i> Cetak</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Nama Pengguna</th>
                        <th>Level</th>
                        <th>Aktivitas yang Dilakukan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $nomor = $offset + 1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-center"><?php echo $nomor++; ?></td>
                            <td><?php echo date('d M Y, H:i:s', strtotime($row['waktu_log'])); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td class="text-center"><span class="badge bg-dark"><?php echo ucfirst($row['level_akses']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['aktivitas']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Data log tidak ditemukan dengan filter yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
        <nav class="no-print mt-4">
            <ul class="pagination justify-content-center flex-wrap">
                <?php
                    $pagination_query = http_build_query(array_filter([
                        'keyword' => $keyword,
                        'id_pegawai' => $id_pegawai_filter,
                        'tanggal_awal' => $tanggal_awal,
                        'tanggal_akhir' => $tanggal_akhir
                    ]));
                    
                    if($page > 1){
                        echo "<li class='page-item'><a class='page-link' href='?page=".($page - 1)."&{$pagination_query}'>&laquo;</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><span class='page-link'>&laquo;</span></li>";
                    }

                    $links_limit = 5;
                    $start = max(1, $page - floor($links_limit / 2));
                    $end = min($total_pages, $start + $links_limit - 1);

                    if ($end - $start < $links_limit - 1) {
                        $start = max(1, $end - $links_limit + 1);
                    }

                    if ($start > 1) {
                        echo "<li class='page-item'><a class='page-link' href='?page=1&{$pagination_query}'>1</a></li>";
                        if ($start > 2) {
                            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        }
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $active_class = ($page == $i) ? 'active' : '';
                        echo "<li class='page-item {$active_class}'><a class='page-link' href='?page={$i}&{$pagination_query}'>{$i}</a></li>";
                    }

                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) {
                            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                        }
                        echo "<li class='page-item'><a class='page-link' href='?page={$total_pages}&{$pagination_query}'>{$total_pages}</a></li>";
                    }

                    if($page < $total_pages){
                        echo "<li class='page-item'><a class='page-link' href='?page=".($page + 1)."&{$pagination_query}'>&raquo;</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><span class='page-link'>&raquo;</span></li>";
                    }
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>