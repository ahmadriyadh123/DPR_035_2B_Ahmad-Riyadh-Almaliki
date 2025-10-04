<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <h2>Edit Mata Kuliah</h2>
    
    <form id="course-form" action="/admin/courses/update/<?= $course->id ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="course_name" class="form-label">Nama Mata Kuliah</label>
            <input type="text" id="course_name" name="course_name" class="form-control" value="<?= esc($course->course_name) ?>">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="credits" class="form-label">Jumlah Kredit</label>
            <input type="number" id="credits" name="credits" class="form-control" value="<?= esc($course->credits) ?>">
            <div class="error-message"></div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="/admin/courses" class="btn btn-secondary">Batal</a>
    </form>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseForm = document.getElementById('course-form');
    const courseNameInput = document.getElementById('course_name');
    const creditsInput = document.getElementById('credits');

    courseForm.addEventListener('submit', function(event) {
        // Hapus status error sebelumnya
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

        // Jika salah satu tidak valid, hentikan pengiriman form
        if (!isValid) {
            event.preventDefault();
        }
    });

    /**
     * Menampilkan pesan error dan menambahkan kelas invalid.
     */
    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorContainer = inputElement.nextElementSibling;
        errorContainer.textContent = message;
    }

    /**
     * Menghapus semua status validasi dari form.
     */
    function resetValidation() {
        const invalidFields = courseForm.querySelectorAll('.is-invalid');
        invalidFields.forEach(function(field) {
            field.classList.remove('is-invalid');
        });

        const errorMessages = courseForm.querySelectorAll('.error-message');
        errorMessages.forEach(function(message) {
            message.textContent = '';
        });
    }
});
</script>
<?= $this->endSection() ?>