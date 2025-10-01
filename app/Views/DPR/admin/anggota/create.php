<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menambah anggota baru -->
<div class="container">
    <h2>Tambah Anggota Baru</h2>

    <!-- Form untuk input data anggota baru, dengan validasi client-side -->
    <form id="member-form" action="/admin/members/store" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="member_first_name" class="form-label">Nama Depan</label>
            <input type="text" id="member_first_name" name="member_first_name" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="member_last_name" class="form-label">Nama Belakang</label>
            <input type="text" id="member_last_name" name="member_last_name" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="gelar_first_name" class="form-label">Gelar Depan</label>
            <input type="text" id="gelar_first_name" name="gelar_first_name" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="gelar_last_name" class="form-label">Gelar Belakang</label>
            <input type="text" id="gelar_last_name" name="gelar_last_name" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="status_pernikahan" class="form-label">Status Pernikahan</label>
            <input type="text" id="status_pernikahan" name="status_pernikahan" class="form-control">
            <div class="error-message"></div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="/admin/members" class="btn btn-secondary">Batal</a>
    </form>
</div>

<!-- Gaya CSS khusus untuk menampilkan pesan error validasi -->
<style>
    .error-message {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
        display: none; 
    }
    .is-invalid {
        border-color: #dc3545 !important; 
    }
    .is-invalid + .error-message {
        display: block;
    }
</style>

<!-- Script JavaScript untuk validasi form dan penanganan submit dengan loading indicator -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseForm = document.getElementById('course-form');
    const FirstNameInput = document.getElementById('member_first_name');
    const LastNameInput = document.getElementById('member_last_name');
    const GelarDepanNameInput = document.getElementById('gelar_first_name');
    const GelarBelakangNameInput = document.getElementById('gelar_last_name');
    const JabatanNameInput = document.getElementById('jabatan');
    const StatusPernikahanNameInput = document.getElementById('status_pernikahan');

    // Tambahkan 'async' di depan fungsi event listener
    courseForm.addEventListener('submit', async function(event) {
        // Selalu cegah pengiriman form di awal untuk mengambil alih kontrol
        event.preventDefault(); 
        
        resetValidation();
        let isValid = true;

        // Validasi Nama Depan
        if (FirstNameInput.value.trim() === '') {
            showError(FirstNameInput, 'Nama depan tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Nama Belakang
        if (LastNameInput.value.trim() === '') {
            showError(LastNameInput, 'Nama belakang tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Gelar Depan
        if (GelarDepanNameInput.value.trim() === '') {
            showError(GelarDepanNameInput, 'Gelar depan tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Gelar Belakang
        if (GelarBelakangNameInput.value.trim() === '') {
            showError(GelarBelakangNameInput, 'Gelar belakang tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Jabatan
        if (JabatanNameInput.value.trim() === '') {
            showError(JabatanNameInput, 'Jabatan tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Status Pernikahan
        if (StatusPernikahanNameInput.value.trim() === '') {
            showError(StatusPernikahanNameInput, 'Status pernikahan tidak boleh kosong.');
            isValid = false;
        }


        // Jika semua field sudah valid
        if (isValid) {
            // Tampilkan pesan "Menyimpan" dengan SweetAlert2
            Swal.fire({
                title: 'Menyimpan Data',
                text: 'Mohon tunggu, data anggota sedang disimpan.',
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