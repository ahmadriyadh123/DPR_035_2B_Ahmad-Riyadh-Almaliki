<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menambah mahasiswa baru -->
<div class="container">
    <h2>Tambah Mahasiswa Baru</h2>
    
    <!-- Form untuk input data mahasiswa baru, dengan validasi client-side -->
    <form id="student-form" action="<?= site_url('admin/students/store') ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="entry_year" class="form-label">Tahun Angkatan</label>
            <input type="number" id="entry_year" name="entry_year" class="form-control" placeholder="Contoh: 2023">
            <div class="error-message"></div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="/admin/students" class="btn btn-secondary">Batal</a>
    </form>
</div>

<!-- Gaya CSS khusus untuk menampilkan pesan error validasi -->
<style>
    .error-message { color: #dc3545; font-size: 0.875em; margin-top: 5px; display: none; }
    .is-invalid { border-color: #dc3545 !important; }
    .is-invalid + .error-message { display: block; }
</style>

<!-- Script JavaScript untuk validasi form dan penanganan submit dengan loading indicator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentForm = document.getElementById('student-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const entryYearInput = document.getElementById('entry_year');

    studentForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        resetValidation();
        let isValid = true;

        if (usernameInput.value.trim() === '') {
            showError(usernameInput, 'Username tidak boleh kosong.');
            isValid = false;
        }
        if (passwordInput.value.trim() === '') {
            showError(passwordInput, 'Password tidak boleh kosong.');
            isValid = false;
        }
        if (entryYearInput.value.trim() === '') {
            showError(entryYearInput, 'Tahun angkatan tidak boleh kosong.');
            isValid = false;
        }

        if (isValid) {
            Swal.fire({
                title: 'Menyimpan Data',
                text: 'Mohon tunggu, data mahasiswa sedang disimpan.',
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            await new Promise(resolve => setTimeout(resolve, 1500));
            studentForm.submit();
        }
    });

    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorContainer = inputElement.nextElementSibling;
        errorContainer.textContent = message;
    }

    function resetValidation() {
        studentForm.querySelectorAll('.is-invalid').forEach(f => f.classList.remove('is-invalid'));
        studentForm.querySelectorAll('.error-message').forEach(m => m.textContent = '');
    }
});
</script>
<?= $this->endSection() ?>