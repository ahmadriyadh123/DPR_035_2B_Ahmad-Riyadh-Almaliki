<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menambah mata kuliah baru -->
<div class="container">
    <h2>Tambah Mata Kuliah Baru</h2>
    
    <!-- Form untuk input data mata kuliah baru, dengan validasi client-side -->
    <form id="course-form" action="/admin/courses/store" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="course_name" class="form-label">Nama Mata Kuliah</label>
            <input type="text" id="course_name" name="course_name" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="credits" class="form-label">Jumlah Kredit</label>
            <input type="number" id="credits" name="credits" class="form-control">
            <div class="error-message"></div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="/admin/courses" class="btn btn-secondary">Batal</a>
    </form>
</div>

<!-- Gaya CSS khusus untuk menampilkan pesan error validasi -->
<style>
    .error-message {
        color: #dc3545; /* Warna merah Bootstrap */
        font-size: 0.875em;
        margin-top: 5px;
        display: none; /* Sembunyikan secara default */
    }
    .is-invalid {
        border-color: #dc3545 !important; /* Tambahkan border merah */
    }
    .is-invalid + .error-message {
        display: block; /* Tampilkan pesan error jika input invalid */
    }
</style>

<!-- Script JavaScript untuk validasi form dan penanganan submit dengan loading indicator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseForm = document.getElementById('course-form');
    const courseNameInput = document.getElementById('course_name');
    const creditsInput = document.getElementById('credits');

    // Tambahkan 'async' di depan fungsi event listener
    courseForm.addEventListener('submit', async function(event) {
        // Selalu cegah pengiriman form di awal untuk mengambil alih kontrol
        event.preventDefault(); 
        
        resetValidation();
        let isValid = true;

        // Validasi Nama Mata Kuliah
        if (courseNameInput.value.trim() === '') {
            showError(courseNameInput, 'Nama mata kuliah tidak boleh kosong.');
            isValid = false;
        }

        // Validasi Kredit
        if (creditsInput.value.trim() === '') {
            showError(creditsInput, 'Jumlah kredit tidak boleh kosong.');
            isValid = false;
        }

        // Jika semua field sudah valid
        if (isValid) {
            // Tampilkan pesan "Menyimpan" dengan SweetAlert2
            Swal.fire({
                title: 'Menyimpan Data',
                text: 'Mohon tunggu, data mata kuliah sedang disimpan.',
                icon: 'info',
                allowOutsideClick: false, // Mencegah pengguna menutup dialog
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Tunggu selama 1.5 detik untuk simulasi
            await new Promise(resolve => setTimeout(resolve, 1500));

            // Setelah menunggu, kirimkan form secara manual
            courseForm.submit();
        }
    });

    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorContainer = inputElement.nextElementSibling;
        errorContainer.textContent = message;
    }

    function resetValidation() {
        const invalidFields = courseForm.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => field.classList.remove('is-invalid'));
        const errorMessages = courseForm.querySelectorAll('.error-message');
        errorMessages.forEach(message => message.textContent = '');
    }
});
</script>
<?= $this->endSection() ?>