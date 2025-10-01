<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menampilkan loading screen aplikasi mahasiswa -->
<div class="container">
    <div id="app-content">
        <!-- Elemen loading dengan spinner Bootstrap yang lebih besar dan pesan teks -->
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="ms-3">Memuat Aplikasi Mahasiswa...</h4>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<!-- Bagian script khusus halaman untuk memuat file JavaScript aplikasi mahasiswa -->
<?= $this->section('pageScripts') ?>
<script src="<?= base_url('js/student-app.js') ?>"></script>
<?= $this->endSection() ?>