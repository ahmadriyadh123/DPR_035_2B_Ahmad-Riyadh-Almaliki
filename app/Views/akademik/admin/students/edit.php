<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk mengedit data mahasiswa -->
<div class="container">
    <h2>Edit Mahasiswa: <?= esc($user->username) ?></h2>
    
    <!-- Form untuk mengupdate data mahasiswa, dengan validasi client-side -->
    <form id="student-form" action="<?= site_url('admin/students/update/' . $user->id) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" value="<?= esc($user->username) ?>">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password Baru (Opsional)</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="entry_year" class="form-label">Tahun Angkatan</label>
            <input type="number" id="entry_year" name="entry_year" class="form-control" value="<?= esc($student->entry_year) ?>">
            <div class="error-message"></div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
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
    const entryYearInput = document.getElementById('entry_year');

    studentForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        resetValidation();
        let isValid = true;

        if (usernameInput.value.trim() === '') {
            showError(usernameInput, 'Username tidak boleh kosong.');
            isValid = false;
        }
        if (entryYearInput.value.trim() === '') {
            showError(entryYearInput, 'Tahun angkatan tidak boleh kosong.');
            isValid = false;
        }

        if (isValid) {
            Swal.fire({
                title: 'Memperbarui Data',
                text: 'Mohon tunggu, data mahasiswa sedang diperbarui.',
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