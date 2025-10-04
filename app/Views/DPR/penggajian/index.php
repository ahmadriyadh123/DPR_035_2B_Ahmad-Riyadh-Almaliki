<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menampilkan loading screen aplikasi mata kuliah mahasiswa -->
<div class="container">
    <div id="app-content">
        <!-- Elemen loading dengan spinner Bootstrap dan pesan teks -->
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status"></div>
            <h4 class="ms-3">Memuat Data Penggajian...</h4>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<!-- Bagian script khusus halaman untuk memuat file JavaScript aplikasi mata kuliah mahasiswa -->
<?= $this->section('pageScripts') ?>
<script src="<?= base_url('js/DPR/penggajian-app.js') ?>"></script>
<?= $this->endSection() ?>