// File: public/js/DPR/penggajian-app.js

console.log('Penggajian-app.js loaded successfully!');

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - Starting app initialization');
    
    const appContent = document.getElementById('app-content');
    let csrfName = document.querySelector('meta[name="X-CSRF-TOKEN"]').content;
    let csrfHash = document.querySelector('meta[name="X-CSRF-HEADER"]').content;

    console.log('App elements found:', {
        appContent: !!appContent,
        csrfName: csrfName,
        csrfHash: csrfHash
    });

    // Cek apakah elemen penting tersedia
    if (!appContent) {
        console.error('ERROR: app-content element not found!');
        return;
    }

    if (!csrfName || !csrfHash) {
        console.error('ERROR: CSRF tokens not found!', { csrfName, csrfHash });
    }

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
            // Update meta tag yang benar
            const metaTag = document.querySelector('meta[name="X-CSRF-HEADER"]');
            if (metaTag) {
                metaTag.content = newHash;
            } else {
                console.warn('CSRF meta tag not found for update');
            }
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
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id_anggota}">Edit</button>
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
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" id="search-input" placeholder="Cari nama, jabatan, ID, atau gaji..." style="width: 300px;">
                    <button class="btn btn-secondary" id="clear-search-btn">Clear</button>
                    <button class="btn btn-primary add-btn">Tambah Penggajian</button>
                </div>
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
                anggotaOptions += `<option value="${anggota.id_anggota}" data-jabatan="${anggota.jabatan}">${namaLengkap}</option>`;
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

    async function fetchDetailPenggajianData(id) {
        const response = await fetch(`${apiUrl}/penggajian/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('Gagal mengambil detail penggajian');
        const data = await response.json();
        updateCsrf(data.csrf_hash);
        return data;
    }

    async function fetchAllAnggotaData() {
        try {
            // Untuk edit, kita perlu semua anggota, bukan hanya yang available
            // Sementara kita gunakan available anggota API dan tambahkan anggota yang sedang diedit
            const response = await fetch(`${apiUrl}/penggajian/available-anggota`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Gagal mengambil daftar anggota');
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            return data.anggota;
        } catch (error) {
            console.error('Error fetching all anggota:', error);
            return [];
        }
    }

    // Update renderKomponenGajiSelector untuk mendukung pre-selected checkboxes
    function renderKomponenGajiSelector(komponenList, selectedKomponen = []) {
        const container = document.getElementById('komponen-gaji-selector');
        if (!komponenList || komponenList.length === 0) {
            container.innerHTML = '<p class="text-danger">Tidak ada komponen gaji yang tersedia untuk jabatan ini.</p>';
            return;
        }

        let checkboxesHtml = '<h5>Komponen Gaji Tersedia</h5>';
        komponenList.forEach(komponen => {
            const isSelected = selectedKomponen.some(selected => selected.id_komponen_gaji == komponen.id_komponen_gaji);
            checkboxesHtml += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="id_komponen[]" 
                           value="${komponen.id_komponen_gaji}" id="komponen-${komponen.id_komponen_gaji}"
                           ${isSelected ? 'checked' : ''}>
                    <label class="form-check-label" for="komponen-${komponen.id_komponen_gaji}">
                        ${komponen.nama_komponen} (Rp ${parseFloat(komponen.nominal).toLocaleString('id-ID')})
                    </label>
                </div>`;
        });
        container.innerHTML = checkboxesHtml;
    }

    // --- FUNGSI-FUNGSI API ---

    const apiUrl = document.querySelector('meta[name="api-url"]').content;

    async function fetchPenggajianSummary(page = 1, search = '') {
        showLoading('Memuat Data Penggajian...');
        
        console.log('Starting fetchPenggajianSummary, page:', page, 'search:', search);
        console.log('API URL:', apiUrl);
        
        try {
            let url = `${apiUrl}/penggajian/summary?page=${page}`;
            if (search.trim()) {
                url += `&search=${encodeURIComponent(search.trim())}`;
            }
            console.log('Requesting:', url);
            
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Received data:', data);
            
            updateCsrf(data.csrf_hash);
            
            // Cek jika ada error di response data
            if (data.error) {
                console.error('Server returned error:', data.error);
                showError(`Error: ${data.error}`);
                return;
            }
            
            // Pastikan data memiliki struktur yang diharapkan
            if (!data.penggajian || !Array.isArray(data.penggajian)) {
                console.error('Invalid data structure:', data);
                showError('Data penggajian tidak valid dari server');
                return;
            }
            
            console.log('Data is valid, rendering...');
            renderPenggajianSummary(data);
            
        } catch (error) {
            console.error('Error fetching summary:', error);
            console.error('Error stack:', error.stack);
            showError(`Gagal memuat data penggajian: ${error.message || 'Periksa koneksi internet dan coba lagi.'}`);
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
            if (!response.ok) throw new Error(data.message || `Gagal mengambil data (Status: ${response.status})`);
            renderDetailPenggajian(data);
        } catch (error) {
            console.error('Error fetching detail:', error);
            showError(`Terjadi kesalahan: ${error.message}.`);
        }
    }

    async function fetchAvailableAnggota() {
        try {
            // Mengambil semua anggota yang belum memiliki data penggajian
            const response = await fetch(`${apiUrl}/penggajian/available-anggota`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Gagal mengambil daftar anggota.');
            const data = await response.json();
            updateCsrf(data.csrf_hash);
            return data.anggota;
        } catch (error) {
            console.error('Error fetching available anggota:', error);
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
            return data.komponen;
        } catch (error) {
            console.error('Error fetching komponen by jabatan:', error);
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
                // Menampilkan pesan error validasi dari server
                const errorMessages = result.messages && result.messages.error ? result.messages.error : 'Gagal menyimpan data.';
                throw new Error(errorMessages);
            }
            
            await fetchPenggajianSummary(); // Tunggu summary selesai sebelum menampilkan success
            showSuccess('Data penggajian berhasil disimpan.');

        } catch (error) {
            // Render ulang form dengan pesan error
            await handleAddButtonClick(error.message);
        }
    }

    async function updatePenggajian(id, formData) {
        showLoading('Memperbarui data...');
        try {
            const response = await fetch(`${apiUrl}/penggajian/${id}`, {
                method: 'PUT',
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
                // Menampilkan pesan error validasi dari server
                const errorMessages = result.messages && result.messages.error ? result.messages.error : 'Gagal memperbarui data.';
                throw new Error(errorMessages);
            }
            
            await fetchPenggajianSummary(); // Tunggu summary selesai sebelum menampilkan success
            showSuccess('Data penggajian berhasil diperbarui.');

        } catch (error) {
            console.error('❌ Error updating penggajian:', error);
            showError(error.message);
        }
    }

    async function deletePenggajian(id) {
        try {
            const response = await fetch(`${apiUrl}/penggajian/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfName]: csrfHash
                }
            });

            const result = await response.json();
            updateCsrf(result.csrf_hash);

            if (!response.ok) {
                throw new Error(result.error || 'Gagal menghapus data.');
            }
            
            await fetchPenggajianSummary();
            showSuccess('Data penggajian berhasil dihapus.');

        } catch (error) {
            console.error('Error deleting penggajian:', error);
            showError(error.message);
        }
    }

    // --- EVENT HANDLERS ---

    async function handleAddButtonClick(errorMessage = null) {
        const anggotaList = await fetchAvailableAnggota();
        renderAddEditForm({ anggotaList });
        if (errorMessage) {
            // Sisipkan pesan error di atas form
            const formCard = document.querySelector('#penggajian-form').closest('.card');
            if (formCard) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = errorMessage;
                formCard.parentNode.insertBefore(errorDiv, formCard);
            }
        }
    }

    function handleDetailButtonClick(event) {
        const id = event.target.dataset.id;
        if (id) fetchDetailPenggajian(id);
    }

    function handleDeleteButtonClick(event) {
        const id = event.target.dataset.id;
        const nama = event.target.closest('tr').querySelector('td').textContent;
        if (!id) return;

        Swal.fire({
            title: 'Anda yakin?',
            text: `Menghapus data penggajian untuk "${nama}" tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                deletePenggajian(id);
            }
        });
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
        const selectedOption = event.target.options[event.target.selectedIndex];
        const jabatan = selectedOption.dataset.jabatan;
        
        if (!jabatan) {
            renderKomponenGajiSelector([]);
            return;
        }
        
        const komponenList = await fetchKomponenByJabatan(jabatan);
        renderKomponenGajiSelector(komponenList);
    }

    function handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const id_anggota = form.id_anggota.value;
        const id_komponen = [...form.querySelectorAll('input[name="id_komponen[]"]:checked')].map(cb => cb.value);
        
        // Check if this is edit mode (penggajian ID is stored in form data-id attribute)
        const penggajianId = form.getAttribute('data-penggajian-id');
        const isEdit = penggajianId && penggajianId !== '';

        console.log('Form submitted:', { id_anggota, id_komponen, penggajianId, isEdit });

        if (!id_anggota) {
            // Gunakan SweetAlert untuk notifikasi yang lebih baik
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Harap pilih anggota terlebih dahulu.',
            });
            return;
        }
        
        if (id_komponen.length === 0) {
             Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Harap pilih minimal satu komponen gaji.',
            });
            return;
        }

        if (isEdit) {
            updatePenggajian(penggajianId, { id_anggota, id_komponen });
        } else {
            savePenggajian({ id_anggota, id_komponen });
        }
    }

    // --- DELEGASI EVENT ---

    appContent.addEventListener('click', (event) => {
        if (event.target.classList.contains('add-btn')) handleAddButtonClick();
        else if (event.target.classList.contains('detail-btn')) handleDetailButtonClick(event);
        else if (event.target.classList.contains('edit-btn')) handleEditButtonClick(event);
        else if (event.target.classList.contains('delete-btn')) handleDeleteButtonClick(event);
        else if (event.target.classList.contains('back-btn')) handleBackButtonClick();
        else if (event.target.matches('.pagination a')) handlePaginationClick(event);
        else if (event.target.id === 'clear-search-btn') handleClearSearch();
    });

    appContent.addEventListener('change', (event) => {
        if (event.target.id === 'id_anggota') handleAnggotaChange(event);
    });

    appContent.addEventListener('submit', (event) => {
        if (event.target.id === 'penggajian-form') handleFormSubmit(event);
    });

    appContent.addEventListener('input', (event) => {
        if (event.target.id === 'search-input') {
            // Debounce search untuk menghindari terlalu banyak request
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                const searchValue = event.target.value;
                fetchPenggajianSummary(1, searchValue);
            }, 500);
        }
    });

    // Fungsi untuk clear search
    function handleClearSearch() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = '';
            fetchPenggajianSummary(1, '');
        }
    }

    // Fungsi untuk handle edit button click
    async function handleEditButtonClick(event) {
        const id = event.target.dataset.id;
        if (id) {
            showLoading('Memuat data untuk edit...');
            try {
                // Ambil detail penggajian
                const detailData = await fetchDetailPenggajianData(id);
                // Ambil semua anggota (tidak hanya yang available)
                const anggotaModel = new AnggotaModel();
                const allAnggota = await fetchAllAnggotaData();
                
                renderAddEditForm({ 
                    anggotaList: allAnggota, 
                    editData: detailData, 
                    isEdit: true 
                });
                
                // Pre-load komponen gaji berdasarkan jabatan anggota
                if (detailData && detailData.anggota) {
                    const komponenList = await fetchKomponenByJabatan(detailData.anggota.jabatan);
                    renderKomponenGajiSelector(komponenList, detailData.komponen_gaji);
                }
                
            } catch (error) {
                console.error('Error loading edit data:', error);
                showError('Gagal memuat data untuk edit: ' + error.message);
            }
        }
    }

    // --- INISIALISASI ---
    fetchPenggajianSummary();
});