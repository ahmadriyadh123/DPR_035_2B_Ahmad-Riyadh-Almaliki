<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menampilkan detail mahasiswa -->
<div class="container">
    <h2>Detail Mahasiswa: <?= esc($student->full_name) ?></h2>
    
    <hr>

    <!-- Card untuk informasi akun mahasiswa -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Informasi Akun</h5>
            <p><strong>Username:</strong> <?= esc($student->username) ?></p>
            <p><strong>Role:</strong> <?= esc($student->role) ?></p>
        </div>
    </div>

    <!-- Card untuk daftar mata kuliah yang diambil oleh mahasiswa -->
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Mata Kuliah yang Diambil</h5>
            <?php if (!empty($courses)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach($courses as $course): ?>
                        <li class="list-group-item">
                            <?= esc($course->course_name) ?> (<?= esc($course->credits) ?> SKS)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Mahasiswa ini belum mengambil mata kuliah apapun.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="/admin/students" class="btn btn-secondary mt-3">Kembali ke Daftar Mahasiswa</a>
</div>
<?= $this->endSection() ?>