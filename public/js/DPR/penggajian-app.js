// File: public/js/DPR/penggajian-app.js

document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');
    let csrfName = document.querySelector('meta[name=csrf-cookie-name]').content;
    let csrfHash = document.querySelector('meta[name=csrf-token]').content;

    // --- FUNGSI-FUNGSI HELPER ---
    const showLoading = (message = 'Memuat...') => {
        appContent.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                <div class="spinner-border text-primary" role="status"></div>
                <h4 class="ms-3">${message}</h4>
            </div>`;
    };

    const showError = (message) => {
        appContent.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    };
    
    const showSuccess = (message) => {
        const successAlert = `<div class="alert alert-success alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        // Prepend to appContent so it doesn't get overwritten by list render
        const currentContent = appContent.innerHTML;
        appContent.innerHTML = successAlert + currentContent;
    };

    const updateCsrf = (newHash) => {
        if (newHash) {
            csrfHash = newHash;
            document.querySelector('meta[name=csrf-token]').content = newHash;
        }
    };

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    function renderPenggajianSummary(data) {
        const { penggajian, pager } = data;
        let penggajianRows = '';
        if (penggajian && penggajian.length > 0) {
            penggajian.forEach(item => {
                penggajianRows += `
                    <tr>
                        <td>${item.nama_anggota}</td>
                        <td>${item.jabatan}</td>
                        <td class="text-end">Rp ${parseFloat(item.take_home_pay).toLocaleString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-info detail-btn" data-id="${item.id_anggota}">Detail</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id_anggota}">Hapus</button>
                        </td>
                    </tr>`;
            });
        } else {
            penggajianRows = '<tr><td colspan="4" class="text-center">Belum ada data penggajian yang dibuat.</td></tr>';
        }

        let paginationHtml = '';
        if (pager && pager.pageCount > 1) {
            paginationHtml = `
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item ${pager.currentPage == 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pager.currentPage - 1}">Previous</a>
                        </li>
                        ${[...Array(pager.pageCount).keys()].map(p => `
                            <li class="page-item ${pager.currentPage == p + 1 ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${p + 1}">${p + 1}</a>
                            </li>
                        `).join('')}
                        <li class="page-item ${pager.currentPage == pager.pageCount ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pager.currentPage + 1}">Next</a>
                        </li>
                    </ul>
                </nav>
            `;
        }

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Data Penggajian Anggota</h2>
                <button class="btn btn-primary add-btn">Tambah Penggajian</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Jabatan</th>
                                <th class="text-end">Take Home Pay</th>
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

    function renderDetailPenggajian(data) {
        const { anggota, komponen_gaji, take_home_pay } = data;
        let komponenRows = '';

        if (komponen_gaji && komponen_gaji.length > 0) {
            komponen_gaji.forEach(komponen => {
                const nominal = parseFloat(komponen.nominal || 0);
                komponenRows += `
                    <tr>
                        <td>${komponen.nama_komponen}</td>
                        <td>${komponen.kategori}</td>
                        <td class="text-end">Rp ${nominal.toLocaleString('id-ID')}</td>
                    </tr>`;
            });
        } else {
            komponenRows = '<tr><td colspan="3" class="text-center">Tidak ada komponen gaji yang ditetapkan.</td></tr>';
        }

        const namaLengkap = `${anggota.gelar_depan || ''} ${anggota.nama_depan} ${anggota.nama_belakang}, ${anggota.gelar_belakang || ''}`.trim().replace(/^,|,$/g, '');

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Detail Penggajian: ${namaLengkap}</h2>
                <button class="btn btn-secondary back-btn">Kembali</button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5>Informasi Anggota</h5></div>
                        <div class="card-body">
                            <p><strong>Nama:</strong> ${namaLengkap}</p>
                            <p><strong>Jabatan:</strong> ${anggota.jabatan}</p>
                            <p><strong>Status Pernikahan:</strong> ${anggota.status_pernikahan || '-'}</p>
                            <p><strong>Jumlah Anak:</strong> ${anggota.jumlah_anak || 0}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5>Ringkasan Gaji</h5></div>
                        <div class="card-body">
                            <p><strong>Jumlah Komponen:</strong> ${komponen_gaji.length}</p>
                            <p><strong>Take Home Pay:</strong> <span class="text-success fs-4 fw-bold">Rp ${parseFloat(take_home_pay).toLocaleString('id-ID')}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h5>Rincian Komponen Gaji</h5></div>
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

    function renderAddEditForm(data = {}) {
        const { anggotaList = [] } = data;
        let anggotaOptions = '<option value="">-- Pilih Anggota --</option>';
        if (anggotaList && anggotaList.length > 0) {
            anggotaList.forEach(anggota => {
                const namaLengkap = `${anggota.gelar_depan || ''} ${anggota.nama_depan} ${anggota.nama_belakang}, ${anggota.gelar_belakang || ''}`.trim().replace(/^,|,$/g, '');
                anggotaOptions += `<option value="${anggota.id}">${namaLengkap}</option>`;
            });
        }

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Tambah Penggajian</h2>
                <button class="btn btn-secondary back-btn">Kembali</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="penggajian-form">
                        <div class="mb-3">
                            <label for="id_anggota" class="form-label">Anggota</label>
                            <select class="form-select" id="id_anggota" name="id_anggota" required>${anggotaOptions}</select>
                        </div>
                        <div id="komponen-gaji-selector" class="mb-3">
                            <p class="text-muted">Pilih anggota untuk melihat komponen gaji.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        `;
    }

    function renderKomponenGajiSelector(komponenList) {
        const container = document.getElementById('komponen-gaji-selector');
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
                </div>`;
        });
        container.innerHTML = checkboxesHtml;
    }

    // --- FUNGSI-FUNGSI API ---

    const apiUrl = document.body.dataset.apiUrl;

    async function fetchPenggajianSummary(page = 1) {
        showLoading('Memuat Data Penggajian...');
        try {
            const response = await fetch(`${apiUrl}/penggajian/summary?page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
            renderPenggajianSummary(data);
        } catch (error) {
            showError(error.message);
        }
    }

    async function fetchDetailPenggajian(id) {
        showLoading('Memuat Detail Penggajian...');
        try {
            const response = await fetch(`${apiUrl}/penggajian/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
            renderDetailPenggajian(data);
        } catch (error) {
            showError(error.message);
        }
    }

    async function fetchAvailableAnggota() {
        try {
            const response = await fetch(`${apiUrl}/anggota?limit=1000`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Gagal mengambil daftar anggota.');
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            return data.anggota;
        } catch (error) {
            showError(error.message);
            return [];
        }
    }

    async function fetchKomponenByJabatan(jabatan) {
        if (!jabatan) return [];
        try {
            const response = await fetch(`${apiUrl}/komponengaji/by-jabatan/${jabatan}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Gagal mengambil komponen gaji.');
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            return data;
        } catch (error) {
            showError(error.message);
            return [];
        }
    }

    async function savePenggajian(formData) {
        showLoading('Menyimpan data...');
        try {
            const response = await fetch(`${apiUrl}/penggajian`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfName]: csrfHash
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            updateCsrf(result.csrf_hash);

            if (!response.ok) {
                throw new Error(result.error || 'Gagal menyimpan data.');
            }
            
            fetchPenggajianSummary();
            showSuccess('Data penggajian berhasil disimpan.');

        } catch (error) {
            fetchPenggajianSummary();
            showError(error.message);
        }
    }

    // --- EVENT HANDLERS ---

    async function handleAddButtonClick() {
        const anggotaList = await fetchAvailableAnggota();
        renderAddEditForm({ anggotaList });
    }

    function handleDetailButtonClick(event) {
        const id = event.target.dataset.id;
        if (id) fetchDetailPenggajian(id);
    }

    function handleBackButtonClick() {
        fetchPenggajianSummary();
    }

    function handlePaginationClick(event) {
        event.preventDefault();
        const page = event.target.dataset.page;
        if (page) fetchPenggajianSummary(page);
    }

    async function handleAnggotaChange(event) {
        const selectedAnggotaId = event.target.value;
        if (!selectedAnggotaId) {
            renderKomponenGajiSelector([]);
            return;
        }
        
        const anggotaList = await fetchAvailableAnggota();
        const selectedAnggota = anggotaList.find(a => a.id == selectedAnggotaId);
        
        if (selectedAnggota) {
            const komponenList = await fetchKomponenByJabatan(selectedAnggota.jabatan);
            renderKomponenGajiSelector(komponenList);
        }
    }

    function handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const id_anggota = form.id_anggota.value;
        const id_komponen = [...form.querySelectorAll('input[name="id_komponen[]"]:checked')].map(cb => cb.value);

        if (!id_anggota || id_komponen.length === 0) {
            showError('Harap pilih anggota dan minimal satu komponen gaji.');
            return;
        }

        savePenggajian({ id_anggota, id_komponen });
    }

    // --- DELEGASI EVENT ---

    appContent.addEventListener('click', (event) => {
        if (event.target.classList.contains('add-btn')) handleAddButtonClick();
        else if (event.target.classList.contains('detail-btn')) handleDetailButtonClick(event);
        else if (event.target.classList.contains('back-btn')) handleBackButtonClick();
        else if (event.target.matches('.pagination a')) handlePaginationClick(event);
    });

    appContent.addEventListener('change', (event) => {
        if (event.target.id === 'id_anggota') handleAnggotaChange(event);
    });

    appContent.addEventListener('submit', (event) => {
        if (event.target.id === 'penggajian-form') handleFormSubmit(event);
    });

    // --- INISIALISASI ---
    fetchPenggajianSummary();
});