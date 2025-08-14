<footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">

            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold text-primary">RekAbsen</h5>
                <p>
                    Sistem absensi modern berbasis lokasi untuk meningkatkan kedisiplinan dan efisiensi pegawai.
                </p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Navigasi</h5>
                <p><a href="/dashboard" class="text-white" style="text-decoration: none;">Dashboard</a></p>
                <p><a href="/riwayat-absensi" class="text-white" style="text-decoration: none;">Riwayat Absensi</a></p>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin')): ?>
                    <p><a href="/admin" class="text-white" style="text-decoration: none;">Panel Admin</a></p>
                <?php endif; ?>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Kontak</h5>
                
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-geo-alt-fill me-3 mt-1 flex-shrink-0"></i>
                    <span>Yos Sudarso 18-22, Surabaya, Jawa Timur, ID</span>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="bi bi-envelope-fill me-3 mt-1 flex-shrink-0"></i>
                    <a href="mailto:support.rekabsen@dprdsby.id" class="text-white" style="text-decoration: none;">support.rekabsen@dprdsby.id</a>
                </div>
                 <div class="d-flex align-items-start">
                    <i class="bi bi-telephone-fill me-3 mt-1 flex-shrink-0"></i>
                    <a href="https://wa.me/6289667009776" target="_blank" class="text-white" style="text-decoration: none;">+62 812 3456 7890</a>
                </div>
                </div>
        </div>

        <hr class="mb-4">

        <div class="text-center">
             <p>
                Copyright &copy;<?php echo date('Y'); ?>
                <a href="#" style="text-decoration: none;">
                    <strong class="text-primary">RekAbsen</strong>
                </a>. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>