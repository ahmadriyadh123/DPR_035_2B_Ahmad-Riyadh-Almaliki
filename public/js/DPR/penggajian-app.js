// File: public/js/DPR/penggajian-app.js

console.log('Penggajian-app.js loaded successfully!');

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - Starting app initialization');
    
    const appContent = document.getElementById('app-content');

    console.log('App elements found:', {
        appContent: !!appContent
    });

    // Cek apakah elemen penting tersedia
    if (!appContent) {
        console.error('ERROR: app-content element not found!');
        return;
    }

    // Cek apakah utils.js sudah dimuat
    if (typeof fetchData === 'undefined') {
        console.error('ERROR: utils.js tidak dimuat! Pastikan utils.js dimuat sebelum script ini.');
        showError('Error: utils.js tidak dimuat!');
        return;
    }
    
    // Load user info first to set permissions
    loadUserInfo().then((userInfo) => {
        // Check if user is logged in
        if (!userInfo.isLoggedIn) {
            // Redirect to login page if not authenticated
            window.location.href = '/login';
            return;
        }
        initializeApp();
    }).catch(error => {
        console.error('Failed to load user info:', error);
        // Redirect to login on error
        window.location.href = '/login';
    });

    // --- FUNGSI-FUNGSI HELPER ---
    
    // Global variable to store user role
    window.userRole = null;
    
    // Load user info from API
    async function loadUserInfo() {
        try {
            const data = await fetchData('/api/user/info');
            window.userRole = data.role;
            console.log('User info loaded:', data);
            return data;
        } catch (error) {
            console.error('Error loading user info:', error);
            throw error;
        }
    }
    
    // Helper function to check if user is admin
    const isUserAdmin = () => {
        return window.userRole && window.userRole.toLowerCase() === 'admin';
    };
    
    // Helper function to get action buttons based on user role
    const getActionButtons = (id) => {
        const detailBtn = `<button class="btn btn-sm btn-info detail-btn" data-id="${id}">Detail</button>`;
        
        if (isUserAdmin()) {
            return `
                ${detailBtn}
                <button class="btn btn-sm btn-warning edit-btn" data-id="${id}">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${id}">Hapus</button>
            `;
        } else {
            return detailBtn; // Only show detail button for non-admin users
        }
    };
    
    // Helper function to get add button for admin only
    const getAddButton = () => {
        console.log('🔍 getAddButton called');
        console.log('🔍 Current user role:', window.userRole);
        console.log('🔍 Is user admin?', isUserAdmin());
        
        if (isUserAdmin()) {
            console.log('✅ User is admin - showing add button');
            return '<button class="btn btn-primary" onclick="handleAddButtonClick()">Tambah Data Penggajian</button>';
        } else {
            console.log('⚠️ User is not admin - showing read-only message');
            // Show info for non-admin users
            return '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Anda memiliki akses baca saja. Untuk melakukan perubahan data, hubungi administrator.</div>';
        }
    };
    
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

    // CSRF token management sekarang dihandle oleh utils.js fetchData function

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    function renderPenggajianSummary(data) {
        // Simpan nilai search yang sedang diketik sebelum re-render
        const currentSearchValue = document.getElementById('search-input')?.value || '';
        
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
                            ${getActionButtons(item.id_anggota)}
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
                    ${getAddButton()}
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
        
        // Kembalikan nilai search setelah re-render
        setTimeout(() => {
            const searchInput = document.getElementById('search-input');
            if (searchInput && currentSearchValue) {
                searchInput.value = currentSearchValue;
                // Set cursor di akhir teks
                searchInput.setSelectionRange(currentSearchValue.length, currentSearchValue.length);
            }
        }, 0);
    }

    function renderDetailPenggajian(data) {
        const { anggota, komponen_gaji, take_home_pay, tunjangan_anak_info } = data;
        let komponenRows = '';

        if (komponen_gaji && komponen_gaji.length > 0) {
            komponen_gaji.forEach(komponen => {
                let nominal = parseFloat(komponen.nominal || 0);
                let infoTambahan = '';
                
                // Cek apakah ini Tunjangan Anak dan ada info khusus
                const isTunjanganAnak = komponen.nama_komponen.toLowerCase().includes('tunjangan anak');
                if (isTunjanganAnak && tunjangan_anak_info && tunjangan_anak_info.komponen_id == komponen.id_komponen_gaji) {
                    // Hitung nominal berdasarkan jumlah anak (maksimal 2) - untuk display saja
                    nominal = nominal * tunjangan_anak_info.jumlah_dihitung;
                    infoTambahan = ` <small class="text-info">(${tunjangan_anak_info.jumlah_anak} anak, dihitung: ${tunjangan_anak_info.jumlah_dihitung})</small>`;
                }
                
                komponenRows += `
                    <tr>
                        <td>${komponen.nama_komponen}${infoTambahan}</td>
                        <td>${komponen.kategori}</td>
                        <td class="text-end">Rp ${nominal.toLocaleString('id-ID')}</td>
                    </tr>`;
            });
        } else {
            komponenRows = '<tr><td colspan="3" class="text-center">Tidak ada komponen gaji yang ditetapkan.</td></tr>';
        }

        const namaLengkap = `${anggota.gelar_depan || ''} ${anggota.nama_depan} ${anggota.nama_belakang}, ${anggota.gelar_belakang || ''}`.trim().replace(/^,|,$/g, '');

        // Cari info jumlah anak dari data Tunjangan Anak
        let jumlahAnak = 0;
        let infoAnak = '';
        if (tunjangan_anak_info) {
            jumlahAnak = tunjangan_anak_info.jumlah_anak || 0;
            if (jumlahAnak > 0) {
                infoAnak = ` <small class="text-info">(dihitung: ${tunjangan_anak_info.jumlah_dihitung})</small>`;
            }
        }

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
                            <p><strong>Jumlah Anak:</strong> ${jumlahAnak}${infoAnak}</p>
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
        const { anggotaList = [], editData = null, isEdit = false } = data;
        const selectedAnggotaId = editData?.anggota?.id_anggota || '';
        
        let anggotaOptions = '<option value="">-- Pilih Anggota --</option>';
        if (anggotaList && anggotaList.length > 0) {
            anggotaList.forEach(anggota => {
                const namaLengkap = `${anggota.gelar_depan || ''} ${anggota.nama_depan} ${anggota.nama_belakang}, ${anggota.gelar_belakang || ''}`.trim().replace(/^,|,$/g, '');
                const isSelected = selectedAnggotaId == anggota.id_anggota ? 'selected' : '';
                anggotaOptions += `<option value="${anggota.id_anggota}" data-jabatan="${anggota.jabatan}" ${isSelected}>${namaLengkap}</option>`;
            });
        }

        const formTitle = isEdit ? 'Edit Penggajian' : 'Tambah Penggajian';
        const formDataAttribute = isEdit && selectedAnggotaId ? `data-penggajian-id="${selectedAnggotaId}"` : '';

        appContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>${formTitle}</h2>
                <button class="btn btn-secondary back-btn">Kembali</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="penggajian-form" ${formDataAttribute}>
                        <div class="mb-3">
                            <label for="id_anggota" class="form-label">Anggota</label>
                            <select class="form-select" id="id_anggota" name="id_anggota" required ${isEdit ? 'disabled' : ''}>${anggotaOptions}</select>
                            ${isEdit ? `<input type="hidden" name="id_anggota_hidden" value="${selectedAnggotaId}">` : ''}
                            ${isEdit ? '<small class="form-text text-muted">Anggota tidak dapat diubah saat edit</small>' : ''}
                        </div>
                        <div id="komponen-gaji-selector" class="mb-3">
                            <p class="text-muted">Pilih anggota untuk melihat komponen gaji.</p>
                        </div>
                        <button type="submit" class="btn btn-primary">${isEdit ? 'Update' : 'Simpan'}</button>
                    </form>
                </div>
            </div>
        `;
    }

    async function fetchDetailPenggajianData(id) {
        try {
            const data = await fetchData(`${apiUrl}/penggajian/${id}`);
            return data;
        } catch (error) {
            throw new Error('Gagal mengambil detail penggajian: ' + error.message);
        }
    }

    async function fetchAllAnggotaData() {
        try {
            // Untuk edit, kita perlu semua anggota, bukan hanya yang available
            // Sementara kita gunakan available anggota API dan tambahkan anggota yang sedang diedit
            const data = await fetchData(`${apiUrl}/penggajian/available-anggota`);
            return data.anggota || [];
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
            const isTunjanganAnak = komponen.nama_komponen.toLowerCase().includes('tunjangan anak');
            
            checkboxesHtml += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="id_komponen[]" 
                           value="${komponen.id_komponen_gaji}" id="komponen-${komponen.id_komponen_gaji}"
                           ${isSelected ? 'checked' : ''} ${isTunjanganAnak ? 'data-tunjangan-anak="true"' : ''}>
                    <label class="form-check-label" for="komponen-${komponen.id_komponen_gaji}">
                        ${komponen.nama_komponen} (Rp ${parseFloat(komponen.nominal).toLocaleString('id-ID')})
                        ${isTunjanganAnak ? '<small class="text-info"> - Maksimal 2 anak</small>' : ''}
                    </label>
                </div>`;
        });
        container.innerHTML = checkboxesHtml;
        
        // Add event listener untuk Tunjangan Anak checkboxes
        const tunjanganAnakCheckboxes = container.querySelectorAll('input[data-tunjangan-anak="true"]');
        tunjanganAnakCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleTunjanganAnakChange);
        });
    }

    // Fungsi untuk menangani perubahan checkbox Tunjangan Anak
    function handleTunjanganAnakChange(event) {
        const checkbox = event.target;
        const komponenId = checkbox.value;
        
        if (checkbox.checked) {
            // Tampilkan modal input jumlah anak
            showJumlahAnakModal(komponenId, checkbox);
        } else {
            // Hapus data jumlah anak yang tersimpan
            delete window.tunjanganAnakData;
        }
    }

    // Fungsi untuk menampilkan modal input jumlah anak
    function showJumlahAnakModal(komponenId, checkbox) {
        const modalHtml = `
            <div class="modal fade" id="jumlahAnakModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Input Jumlah Anak</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="jumlah-anak-input" class="form-label">Jumlah Anak</label>
                                <input type="number" id="jumlah-anak-input" class="form-control" 
                                       min="0" max="10" value="1" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Tunjangan anak akan dihitung maksimal untuk 2 anak
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="confirm-jumlah-anak">Konfirmasi</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove modal lama jika ada
        const existingModal = document.getElementById('jumlahAnakModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Tambahkan modal ke body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Inisialisasi modal Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('jumlahAnakModal'));
        modal.show();

        // Handle konfirmasi
        document.getElementById('confirm-jumlah-anak').addEventListener('click', () => {
            const jumlahAnak = parseInt(document.getElementById('jumlah-anak-input').value) || 0;
            
            if (jumlahAnak < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Jumlah anak minimal 1 untuk mendapat tunjangan anak.'
                });
                return;
            }

            // Simpan data jumlah anak untuk digunakan saat submit
            window.tunjanganAnakData = {
                komponenId: komponenId,
                jumlahAnak: jumlahAnak,
                jumlahDihitung: Math.min(jumlahAnak, 2) // Maksimal 2 anak
            };

            // Update label checkbox untuk menampilkan info jumlah anak
            const label = checkbox.nextElementSibling;
            const originalText = label.innerHTML.split('<span')[0]; // Ambil text asli sebelum span info
            label.innerHTML = `${originalText} <span class="text-success">- ${jumlahAnak} anak (dihitung: ${window.tunjanganAnakData.jumlahDihitung})</span>`;

            modal.hide();
        });

        // Handle cancel - uncheck checkbox
        document.getElementById('jumlahAnakModal').addEventListener('hidden.bs.modal', () => {
            if (!window.tunjanganAnakData || window.tunjanganAnakData.komponenId !== komponenId) {
                checkbox.checked = false;
            }
            document.getElementById('jumlahAnakModal').remove();
        });
    }

    // --- FUNGSI-FUNGSI API ---

    const apiUrl = document.querySelector('meta[name="api-url"]').content;

    async function fetchPenggajianSummary(page = 1, search = '') {
        showLoading('Memuat Data Penggajian... (Mohon tunggu, sistem sedang menghitung gaji)');
        
        console.log('Starting fetchPenggajianSummary, page:', page, 'search:', search);
        console.log('API URL:', apiUrl);
        
        try {
            let url = `${apiUrl}/penggajian/summary?page=${page}`;
            if (search.trim()) {
                url += `&search=${encodeURIComponent(search.trim())}`;
            }
            console.log('Requesting:', url);
            
            const data = await fetchData(url);
            console.log('Received data:', data);
            
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
            const data = await fetchData(`${apiUrl}/penggajian/${id}`);
            renderDetailPenggajian(data);
        } catch (error) {
            console.error('Error fetching detail:', error);
            showError(`Terjadi kesalahan: ${error.message}.`);
        }
    }

    async function fetchAvailableAnggota() {
        try {
            // Mengambil semua anggota yang belum memiliki data penggajian
            const data = await fetchData(`${apiUrl}/penggajian/available-anggota`);
            return data.anggota || [];
        } catch (error) {
            console.error('Error fetching available anggota:', error);
            showError(error.message);
            return [];
        }
    }

    async function fetchKomponenByJabatan(jabatan) {
        if (!jabatan) return [];
        try {
            const data = await fetchData(`${apiUrl}/komponengaji/by-jabatan/${jabatan}`);
            return data.komponen || [];
        } catch (error) {
            console.error('Error fetching komponen by jabatan:', error);
            showError(error.message);
            return [];
        }
    }

    async function savePenggajian(formData) {
        showLoading('Menyimpan data...');
        try {
            const data = await fetchData(`${apiUrl}/penggajian`, {
                method: 'POST',
                body: JSON.stringify(formData)
            });
            
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
            const data = await fetchData(`${apiUrl}/penggajian/${id}`, {
                method: 'PUT',
                body: JSON.stringify(formData)
            });
            
            await fetchPenggajianSummary(); // Tunggu summary selesai sebelum menampilkan success
            showSuccess('Data penggajian berhasil diperbarui.');

        } catch (error) {
            console.error('❌ Error updating penggajian:', error);
            showError(error.message);
        }
    }

    async function deletePenggajian(id) {
        try {
            const data = await fetchData(`${apiUrl}/penggajian/${id}`, {
                method: 'DELETE'
            });
            
            await fetchPenggajianSummary();
            showSuccess('Data penggajian berhasil dihapus.');

        } catch (error) {
            console.error('Error deleting penggajian:', error);
            showError(error.message);
        }
    }

    // --- EVENT HANDLERS ---

    async function handleAddButtonClick(errorMessage = null) {
        console.log('🆕 handleAddButtonClick called');
        console.log('🔍 Current user role:', window.userRole);
        console.log('🔍 Is user admin?', isUserAdmin());
        
        try {
                console.log('📥 Fetching available anggota...');
            const anggotaList = await fetchAvailableAnggota();
            console.log('✅ Available anggota:', anggotaList);
            
            console.log('🎨 Rendering add/edit form...');
            renderAddEditForm({ anggotaList });
            
            if (errorMessage) {
                console.log('⚠️ Adding error message:', errorMessage);
                // Sisipkan pesan error di atas form
                const formCard = document.querySelector('#penggajian-form').closest('.card');
                if (formCard) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.innerHTML = errorMessage;
                    formCard.parentNode.insertBefore(errorDiv, formCard);
                }
            }
        } catch (error) {
            console.error('❌ Error in handleAddButtonClick:', error);
            showError('Gagal memuat form tambah penggajian: ' + error.message);
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
        
        // Untuk mode edit, gunakan hidden field jika select disabled
        const id_anggota = form.id_anggota_hidden?.value || form.id_anggota.value;
        const id_komponen = [...form.querySelectorAll('input[name="id_komponen[]"]:checked')].map(cb => cb.value);
        
        // Check if this is edit mode (penggajian ID is stored in form data-id attribute)
        const penggajianId = form.getAttribute('data-penggajian-id');
        const isEdit = penggajianId && penggajianId !== '';

        // Prepare form data dengan info Tunjangan Anak jika ada
        let formData = { id_anggota, id_komponen };
        
        // Jika ada data Tunjangan Anak, tambahkan ke form data
        if (window.tunjanganAnakData && id_komponen.includes(window.tunjanganAnakData.komponenId)) {
            console.log('👶 Adding tunjangan anak data:', window.tunjanganAnakData);
            formData.tunjangan_anak_info = {
                komponen_id: window.tunjanganAnakData.komponenId,
                jumlah_anak: window.tunjanganAnakData.jumlahAnak,
                jumlah_dihitung: window.tunjanganAnakData.jumlahDihitung
            };
            console.log('👶 Final tunjangan_anak_info:', formData.tunjangan_anak_info);
        } else {
            console.log('👶 No tunjangan anak data found or not selected');
            console.log('👶 window.tunjanganAnakData:', window.tunjanganAnakData);
            console.log('👶 id_komponen:', id_komponen);
        }

        console.log('📝 Form submitted:', { id_anggota, id_komponen, penggajianId, isEdit, formData });

        // Validasi anggota hanya untuk mode create, tidak untuk edit
        if (!isEdit && !id_anggota) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Harap pilih anggota terlebih dahulu.',
            });
            return;
        }
        
        // Validasi komponen gaji tetap berlaku untuk create dan edit
        if (id_komponen.length === 0) {
             Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Harap pilih minimal satu komponen gaji.',
            });
            return;
        }

        if (isEdit) {
            updatePenggajian(penggajianId, formData);
        } else {
            savePenggajian(formData);
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
                    
                    // Restore data Tunjangan Anak jika ada
                    if (detailData.tunjangan_anak_info) {
                        // Normalize property names untuk consistency
                        window.tunjanganAnakData = {
                            komponenId: detailData.tunjangan_anak_info.komponen_id,
                            komponen_id: detailData.tunjangan_anak_info.komponen_id,
                            jumlahAnak: detailData.tunjangan_anak_info.jumlah_anak,
                            jumlah_anak: detailData.tunjangan_anak_info.jumlah_anak,
                            jumlahDihitung: detailData.tunjangan_anak_info.jumlah_dihitung,
                            jumlah_dihitung: detailData.tunjangan_anak_info.jumlah_dihitung
                        };
                        
                        // Update label checkbox Tunjangan Anak
                        setTimeout(() => {
                            const checkbox = document.querySelector(`input[value="${detailData.tunjangan_anak_info.komponen_id}"][data-tunjangan-anak="true"]`);
                            if (checkbox && checkbox.checked) {
                                const label = checkbox.nextElementSibling;
                                const originalText = label.innerHTML.split('<span')[0];
                                label.innerHTML = `${originalText} <span class="text-success">- ${detailData.tunjangan_anak_info.jumlah_anak} anak (dihitung: ${detailData.tunjangan_anak_info.jumlah_dihitung})</span>`;
                            }
                        }, 100);
                    }
                }
                
            } catch (error) {
                console.error('Error loading edit data:', error);
                showError('Gagal memuat data untuk edit: ' + error.message);
            }
        }
    }

    // --- FUNGSI INISIALISASI ---
    function initializeApp() {
        fetchPenggajianSummary();
    }

    // --- EXPOSE FUNCTIONS TO GLOBAL SCOPE FOR ONCLICK HANDLERS ---
    window.handleAddButtonClick = handleAddButtonClick;

    // --- INISIALISASI ---
});