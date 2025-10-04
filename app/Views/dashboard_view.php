<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman dashboard dengan pesan selamat datang -->
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Jumbotron untuk menampilkan pesan selamat datang berdasarkan data session -->
            <div class="jumbotron">
                <h1 class="display-4">Selamat Datang, <?= session()->get('user_name') ?>!</h1>
                <p class="lead">Ini adalah halaman dashboard Anda. Anda login sebagai <?= session()->get('user_role') ?>.</p>
                <hr class="my-4">
                <p>Anda dapat mulai mengelola aktivitas Anda melalui menu navigasi di atas.</p>
                
                <!-- Quick access buttons based on role -->
                <?php if (session()->get('user_role') === 'Admin' || session()->get('user_role') === 'admin'): ?>
                <div class="mt-4">
                    <h5>Akses Cepat Admin:</h5>
                    <a href="/dpr/anggota" class="btn btn-primary me-2">Kelola Anggota</a>
                    <a href="/dpr/komponengaji" class="btn btn-success me-2">Komponen Gaji</a>
                    <a href="/dpr/penggajian" class="btn btn-info">Penggajian</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>