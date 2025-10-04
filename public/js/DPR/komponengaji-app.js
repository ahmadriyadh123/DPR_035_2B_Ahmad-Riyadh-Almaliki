// File: public/js/KomponenGaji-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');
    const modalPlaceholder = document.getElementById('modal-placeholder');

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    // Fungsi untuk merender daftar komponen gaji beserta pagination
    function renderKomponenList(data) {
        const { komponen_gaji, pager } = data;
        let komponenGajiRows = '';
        if (komponen_gaji && komponen_gaji.length > 0) {
            komponen_gaji.forEach(komponen => {
                komponenGajiRows += `
                    <tr>
                        <td>${komponen.id_komponen_gaji}</td>
                        <td>${komponen.nama_komponen}</td>
                        <td>${komponen.kategori}</td>
                        <td>${komponen.jabatan}</td>
                        <td>${komponen.nominal}</td>
                        <td>${komponen.satuan}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${komponen.id_komponen_gaji}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${komponen.id_komponen_gaji}" data-name="${komponen.nama_komponen}">Hapus</button>
                        </td>
                    </tr>`;
            });
        } else {
            komponenGajiRows = '<tr><td colspan="4" class="text-center">Tidak ada data komponen gaji.</td></tr>';
        }

        let paginationHtml = '';
        if (pager && pager.pageCount > 1) {
            paginationHtml = `
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item ${pager.currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pager.currentPage - 1}">Previous</a>
                        </li>
                        <li class="page-item ${pager.currentPage === pager.pageCount ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pager.currentPage + 1}">Next</a>
                        </li>
                    </ul>
                </nav>
            `;
        }

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Kelola Komponen Gaji</h2>
                <button class="btn btn-primary add-komponen-btn">Tambah Komponen Gaji</button>
            </div>
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Nama Komponen</th><th>Kategori</th><th>Jabatan</th><th>Nominal</th><th>Satuan</th><th>Aksi</th></tr></thead>
                <tbody>${komponenGajiRows}</tbody>
            </table>
            ${paginationHtml}`;
    }

    // Fungsi untuk merender modal form tambah/edit komponen gaji
    function renderKomponenFormModal(title, komponen = {}) {
        const isEdit = !!komponen.id_komponen_gaji;
        const formId = isEdit ? 'edit-komponen-form' : 'add-komponen-form';
        const namaKomponen = komponen.nama_komponen || '';
        const kategori = komponen.kategori || '';
        const jabatan = komponen.jabatan || '';
        const nominal = komponen.nominal || '';
        const satuan = komponen.satuan || '';
        const actionUrl = isEdit ? `/api/komponengaji/${komponen.id_komponen_gaji}` : '/api/komponengaji';
        const method = isEdit ? 'PUT' : 'POST';

        const modalHtml = `
            <div class="modal fade" id="komponenFormModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="${formId}" data-url="${actionUrl}" data-method="${method}">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="form-nama-komponen" class="form-label">Nama Komponen</label>
                                    <input type="text" id="form-nama-komponen" class="form-control" value="${namaKomponen}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-kategori" class="form-label">Kategori</label>
                                    <input type="text" id="form-kategori" class="form-control" value="${kategori}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-jabatan" class="form-label">Jabatan</label>
                                    <input type="text" id="form-jabatan" class="form-control" value="${jabatan}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-nominal" class="form-label">Nominal</label>
                                    <input type="text" id="form-nominal" class="form-control" value="${nominal}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-satuan" class="form-label">Satuan</label>
                                    <input type="text" id="form-satuan" class="form-control" value="${satuan}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>`;
        modalPlaceholder.innerHTML = modalHtml;
        const komponenModal = new bootstrap.Modal(document.getElementById('komponenFormModal'));
        komponenModal.show();
    }

    // --- FUNGSI UTAMA & EVENT HANDLING ---

    // Fungsi untuk memuat daftar komponen dari API
    async function loadKomponen(page = 1) {
        try {
            const data = await fetchData(`/api/komponengaji?page=${page}`);
            renderKomponenList(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Event listener untuk klik pada appContent (tombol aksi, pagination, dll.)
    appContent.addEventListener('click', async (event) => {

        if (event.target.classList.contains('add-komponen-btn')) {
            renderKomponenFormModal('Tambah Komponen Baru');
        }

        if (event.target.classList.contains('edit-btn')) {
            const komponenId = event.target.dataset.id;
            try {
                const komponenData = await fetchData(`/api/komponengaji/${komponenId}`);
                renderKomponenFormModal('Edit Komponen', komponenData);
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }

        if (event.target.classList.contains('delete-btn')) {
            const komponenId = event.target.dataset.id;
            const komponenName = event.target.dataset.name;

            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus komponen: <b>${komponenName}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            });

            if (result.isConfirmed) {
                try {
                    // Kirim request DELETE ke API
                    const response = await fetchData(`/api/komponengaji/${komponenId}`, {
                        method: 'DELETE'
                    });
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadKomponen(); // Muat ulang daftar komponen
                } catch (error) {
                    Swal.fire('Error!', error.message, 'error');
                }
            }        
        }
    });

    // Event listener untuk submit form di modal (tambah/edit komponen gaji)
    modalPlaceholder.addEventListener('submit', async (event) => {
        if (event.target.id === 'add-komponen-form' || event.target.id === 'edit-komponen-form') {
            event.preventDefault();
            const form = event.target;
            const url = form.dataset.url;
            const method = form.dataset.method;
            const formData = {
                nama_komponen: document.getElementById('form-nama-komponen').value,
                kategori: document.getElementById('form-kategori').value,
                jabatan: document.getElementById('form-jabatan').value,
                nominal: document.getElementById('form-nominal').value,
                satuan: document.getElementById('form-satuan').value
            };

            try {
                const response = await fetchData(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                bootstrap.Modal.getInstance(document.getElementById('komponenFormModal')).hide();
                Swal.fire('Berhasil!', response.message, 'success');
                loadKomponen();
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    });

    // Inisialisasi: muat daftar komponen pertama kali
    loadKomponen(1);
});