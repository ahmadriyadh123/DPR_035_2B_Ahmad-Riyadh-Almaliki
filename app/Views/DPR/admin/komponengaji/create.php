<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<!-- Bagian utama konten halaman untuk menambah komponen gaji baru -->
<div class="container">
    <h2>Tambah Komponen Gaji Baru</h2>

    <!-- Form untuk input data komponen gaji baru, dengan validasi client-side -->
    <form id="member-form" action="/admin/komponen_gaji/store" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="nama_komponen" class="form-label">Nama Komponen</label>
            <input type="text" id="nama_komponen" name="nama-komponen" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <input type="text" id="kategori" name="kategori" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="jabatan" class="form-label">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="nominal" class="form-label">Nominal</label>
            <input type="text" id="nominal" name="nominal" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="form-group mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" id="satuan" name="satuan" class="form-control">
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
    const NamaKomponenInput = document.getElementById('nama_komponen');
    const KategoriInput = document.getElementById('kategori');
    const JabatanInput = document.getElementById('jabatan');
    const NominalInput = document.getElementById('nominal');
    const SatuanInput = document.getElementById('satuan');

    // Tambahkan 'async' di depan fungsi event listener
    courseForm.addEventListener('submit', async function(event) {
        // Selalu cegah pengiriman form di awal untuk mengambil alih kontrol
        event.preventDefault(); 
        
        resetValidation();
        let isValid = true;

        // Validasi Nama Komponen
        if (NamaKomponenInput.value.trim() === '') {
            showError(NamaKomponenInput, 'Nama komponen tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Kategori
        if (KategoriInput.value.trim() === '') {
            showError(KategoriInput, 'Kategori tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Jabatan
        if (JabatanInput.value.trim() === '') {
            showError(JabatanInput, 'Jabatan tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Nominal
        if (NominalInput.value.trim() === '') {
            showError(NominalInput, 'Nominal tidak boleh kosong.');
            isValid = false;
        }
        // Validasi Satuan
        if (SatuanInput.value.trim() === '') {
            showError(SatuanInput, 'Satuan tidak boleh kosong.');
            isValid = false;
        }

        // Jika semua field sudah valid
        if (isValid) {
            // Tampilkan pesan "Menyimpan" dengan SweetAlert2
            Swal.fire({
                title: 'Menyimpan Komponen Gaji',
                text: 'Mohon tunggu, data komponen gaji sedang disimpan.',
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