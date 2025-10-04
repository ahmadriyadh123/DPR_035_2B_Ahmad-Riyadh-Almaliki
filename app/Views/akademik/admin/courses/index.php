<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menampilkan loading screen aplikasi mata kuliah -->
<div class="container">
    <div id="app-content">
        <!-- Elemen loading dengan spinner Bootstrap dan pesan teks -->
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="ms-3">Memuat Aplikasi Mata Kuliah...</h4>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<!-- Bagian script khusus halaman untuk memuat file JavaScript aplikasi mata kuliah -->
<?= $this->section('pageScripts') ?>
<script src="<?= base_url('js/course-app.js') ?>"></script>
<?= $this->endSection() ?>