// File: public/js/DPR/penggajian-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    // Fungsi untuk merender daftar penggajian (view only)
    function renderPenggajianList(data) {
        const { penggajian, pager } = data;
        let penggajianRows = '';
        if (penggajian && penggajian.length > 0) {
            penggajian.forEach(item => {
                const totalGaji = item.komponen_gaji.reduce((sum, komponen) => sum + parseFloat(komponen.nominal || 0), 0);
                penggajianRows += `
                    <tr>
                        <td>${item.nama_anggota}</td>
                        <td>${item.jabatan}</td>
                        <td>${item.komponen_gaji.length} komponen</td>
                        <td class="text-end">Rp ${totalGaji.toLocaleString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-info detail-btn" data-id="${item.id_anggota}">Detail</button>
                        </td>
                    </tr>`;
            });
        } else {
            penggajianRows = '<tr><td colspan="5" class="text-center">Tidak ada data penggajian.</td></tr>';
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
                <h2>Data Penggajian Anggota DPR</h2>
                <button class="btn btn-primary add-btn">Tambah Penggajian</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Jabatan</th>
                                <th>Jumlah Komponen</th>
                                <th class="text-end">Total Gaji</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>${penggajianRows}</tbody>
                    </table>
                    ${paginationHtml}
                </div>
            </div>
        `;
    }

    // Fungsi untuk merender detail penggajian
    function renderDetailPenggajian(data) {
        const { anggota, komponen_gaji } = data;
        let komponenRows = '';
        let totalGaji = 0;

        if (komponen_gaji && komponen_gaji.length > 0) {
            komponen_gaji.forEach(komponen => {
                const nominal = parseFloat(komponen.nominal || 0);
                totalGaji += nominal;
                komponenRows += `
                    <tr>
                        <td>${komponen.nama_komponen}</td>
                        <td>${komponen.kategori}</td>
                        <td class="text-end">Rp ${nominal.toLocaleString('id-ID')}</td>
                    </tr>`;
            });
        } else {
            komponenRows = '<tr><td colspan="3" class="text-center">Tidak ada komponen gaji.</td></tr>';
        }

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Detail Penggajian: ${anggota.nama_depan} ${anggota.nama_belakang}</h2>
                <button class="btn btn-secondary back-btn">Kembali</button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Informasi Anggota</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nama:</strong> ${anggota.gelar_depan || ''} ${anggota.nama_depan} ${anggota.nama_belakang} ${anggota.gelar_belakang || ''}</p>
                            <p><strong>Jabatan:</strong> ${anggota.jabatan}</p>
                            <p><strong>Status Pernikahan:</strong> ${anggota.status_pernikahan || '-'}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ringkasan Gaji</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Jumlah Komponen:</strong> ${komponen_gaji.length}</p>
                            <p><strong>Total Gaji:</strong> <span class="text-success fs-4">Rp ${totalGaji.toLocaleString('id-ID')}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Rincian Komponen Gaji</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Komponen</th>
                                <th>Kategori</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>${komponenRows}</tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // Fungsi untuk merender form tambah/edit penggajian
    function renderAddEditForm(data = {}) {
        const { anggotaList = [], selectedAnggotaId = null } = data;
        
        // Membuat opsi dropdown untuk anggota
        let anggotaOptions = '<option value="">Pilih Anggota</option>';
        if (anggotaList && anggotaList.length > 0) {
            anggotaList.forEach(anggota => {
                anggotaOptions += `<option value="${anggota.id}" ${selectedAnggotaId == anggota.id ? 'selected' : ''}>
                    ${anggota.nama_depan} ${anggota.nama_belakang} (${anggota.jabatan})
                </option>`;
            });
        }

        appContent.innerHTML = `
            <h2>Tambah Data Penggajian</h2>
            <div class="card">
                <div class="card-body">
                    <form id="penggajian-form">
                        <div class="mb-3">
                            <label for="id_anggota" class="form-label">Anggota</label>
                            <select class="form-select" id="id_anggota" name="id_anggota" required>
                                ${anggotaOptions}
                            </select>
                        </div>
                        
                        <div id="komponen-gaji-container">
                            <!-- Komponen gaji akan dimuat di sini -->
                            <p class="text-muted">Pilih anggota untuk melihat komponen gaji yang tersedia.</p>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-secondary back-btn">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    // Fungsi untuk merender daftar komponen gaji yang bisa dipilih
    function renderKomponenGajiSelector(komponenList) {
        const container = document.getElementById('komponen-gaji-container');yyyyyyyyyy
        if (!komponenList || komponenList.length === 0) {
            container.innerHTML = '<p class="text-danger">Tidak ada komponen gaji yang tersedia untuk jabatan ini.</p>';
            return;
        }

        let checkboxesHtml = '<h5>Komponen Gaji Tersedia</h5>';
        komponenList.forEach(komponen => {
            checkboxesHtml += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="id_komponen[]" value="${komponen.id}" id="komponen-${komponen.id}">
                    <label class="form-check-label" for="komponen-${komponen.id}">
                        ${komponen.nama_komponen} (Rp ${parseFloat(komponen.nominal).toLocaleString('id-ID')})
                    </label>
                </div>
            `;
        });

        container.innerHTML = checkboxesHtml;
    }

    // --- FUNGSI-FUNGSI API ---

    // Fungsi untuk memuat daftar penggajian dengan pagination
    async function loadPenggajian(page = 1) {
        try {
            appContent.innerHTML = '<h4>Memuat data penggajian...</h4>';
            const data = await fetchData(`/api/penggajian?page=${page}`);
            renderPenggajianList(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Fungsi untuk memuat detail penggajian anggota
    async function loadDetailPenggajian(anggotaId) {
        try {
            appContent.innerHTML = '<h4>Memuat detail penggajian...</h4>';
            const data = await fetchData(`/api/penggajian/${anggotaId}`);
            renderDetailPenggajian(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Fungsi untuk mengambil daftar anggota yang belum memiliki data penggajian
    async function fetchAvailableAnggota() {
        try {
            const response = await fetch('/api/anggota?status=unassigned');
            if (!response.ok) throw new Error('Gagal mengambil data anggota.');
            return await response.json();
        } catch (error) {
            showToast(error.message, 'error');
            return [];
        }
    }

    // Fungsi untuk mengambil komponen gaji yang relevan berdasarkan jabatan
    async function fetchKomponenByJabatan(jabatan) {
        try {
            const response = await fetch(`/api/komponen_gaji?jabatan=${jabatan}`);
            if (!response.ok) throw new Error('Gagal mengambil komponen gaji.');
            return await response.json();
        } catch (error) {
            showToast(error.message, 'error');
            return [];
        }
    }

    // Fungsi untuk menyimpan data penggajian baru
    async function savePenggajian(data) {
        try {
            const response = await fetch('/api/penggajian', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="X-CSRF-HEADER"]').content
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.messages.error || 'Gagal menyimpan data penggajian.');
            }
            return await response.json();
        } catch (error) {
            showToast(error.message, 'error');
            return null;
        }
    }

    // --- EVENT HANDLERS ---

    // Handler untuk tombol "Tambah Penggajian"
    async function handleAddButtonClick() {
        const anggotaData = await fetchAvailableAnggota();
        renderAddEditForm({ anggotaList: anggotaData.anggota });
    }

    // Handler untuk perubahan pada dropdown anggota
    async function handleAnggotaChange(e) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const jabatan = selectedOption.text.split('(')[1]?.replace(')', '').trim();
        
        if (jabatan) {
            const komponenData = await fetchKomponenByJabatan(jabatan);
            renderKomponenGajiSelector(komponenData.komponen_gaji);
        } else {
            document.getElementById('komponen-gaji-container').innerHTML = '<p class="text-muted">Pilih anggota untuk melihat komponen gaji yang tersedia.</p>';
        }
    }

    // Handler untuk submit form penggajian
    async function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const data = {
            id_anggota: formData.get('id_anggota'),
            id_komponen: formData.getAll('id_komponen[]')
        };

        if (!data.id_anggota || data.id_komponen.length === 0) {
            showToast('Harap pilih anggota dan minimal satu komponen gaji.', 'warning');
            return;
        }

        const result = await savePenggajian(data);
        if (result) {
            showToast('Data penggajian berhasil disimpan.', 'success');
            loadPenggajianList(); // Kembali ke daftar penggajian
        }
    }

    // --- EVENT LISTENERS ---

    // Event listener untuk pagination dan tombol aksi
    appContent.addEventListener('click', (event) => {
        if (event.target.matches('.page-link')) {
            event.preventDefault();
            const page = parseInt(event.target.dataset.page, 10);
            if (page && page > 0) {
                loadPenggajian(page);
            }
        }
        
        if (event.target.matches('.detail-btn')) {
            const anggotaId = event.target.dataset.id;
            loadDetailPenggajian(anggotaId);
        }
        
        if (event.target.matches('.back-btn')) {
            loadPenggajian();
        }

        if (event.target.matches('.add-btn')) {
            handleAddButtonClick();
        }
    });

    // Event delegation untuk form
    appContent.addEventListener('change', (e) => {
        if (e.target.id === 'id_anggota') {
            handleAnggotaChange(e);
        }
    });

    // Event delegation untuk form
    appContent.addEventListener('submit', (e) => {
        if (e.target.id === 'penggajian-form') {
            handleFormSubmit(e);
        }
    });

    // Muat data penggajian saat halaman pertama kali dibuka
    loadPenggajian();
});