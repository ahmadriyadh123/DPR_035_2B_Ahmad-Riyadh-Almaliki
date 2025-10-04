<?= $this->extend('public/template') ?>

<?= $this->section('content') ?>

<div class="hero-section bg-primary text-white rounded p-5 mb-5">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-4 mb-3">
                <i class="fas fa-building"></i> Portal Data DPR
            </h1>
            <p class="lead mb-4">
                Akses publik untuk melihat informasi data anggota DPR dan penggajian dalam format yang transparan dan mudah diakses.
            </p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="<?= site_url('public/anggota') ?>" class="btn btn-light btn-lg">
                    <i class="fas fa-users"></i> Data Anggota DPR
                </a>
                <a href="<?= site_url('public/penggajian') ?>" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-money-bill-wave"></i> Data Penggajian
                </a>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <i class="fas fa-chart-bar display-1 opacity-75"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-users fa-3x text-primary"></i>
                </div>
                <h4 class="card-title">Data Anggota DPR</h4>
                <p class="card-text text-muted">
                    Lihat informasi lengkap anggota DPR termasuk nama, jabatan, dan data personal lainnya.
                </p>
                <a href="<?= site_url('public/anggota') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Akses Data Anggota
                </a>
                
                <div class="mt-3">
                    <span class="badge bg-success">Read Only</span>
                    <span class="badge bg-info">No Login Required</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-money-bill-wave fa-3x text-success"></i>
                </div>
                <h4 class="card-title">Data Penggajian</h4>
                <p class="card-text text-muted">
                    Transparansi penggajian anggota DPR dengan detail komponen gaji dan take home pay.
                </p>
                <a href="<?= site_url('public/penggajian') ?>" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> Akses Data Penggajian
                </a>
                
                <div class="mt-3">
                    <span class="badge bg-success">Read Only</span>
                    <span class="badge bg-info">No Login Required</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-primary"></i> Informasi Akses
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-unlock text-success"></i> Akses Bebas</h6>
                        <p class="text-muted small">
                            Data dapat diakses tanpa perlu login atau registrasi.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-eye text-info"></i> Read Only</h6>
                        <p class="text-muted small">
                            Hanya dapat melihat data, tidak dapat melakukan perubahan.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-shield-alt text-warning"></i> Untuk Admin</h6>
                        <p class="text-muted small">
                            <a href="<?= site_url('login') ?>" class="text-decoration-none">Login sebagai admin</a> untuk mengelola data.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 Public Homepage loaded');
    
    // Add some interactive effects
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
<?= $this->endSection() ?>