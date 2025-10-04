// File: public/js/anggota-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');
    const modalPlaceholder = document.getElementById('modal-placeholder');

    // Cek apakah utils.js sudah dimuat
    if (typeof fetchData === 'undefined') {
        console.error('ERROR: utils.js tidak dimuat! Pastikan utils.js dimuat sebelum script ini.');
        if (appContent) {
            appContent.innerHTML = '<div class="alert alert-danger">Error: utils.js tidak dimuat!</div>';
        }
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
    
    // Initialize the app after user permissions are loaded
    function initializeApp() {
        // Load members data first time
        loadMembers(1);
    }

    // --- FUNGSI-FUNGSI HELPER ---
    
    // Global variable to store user role
    window.userRole = window.userRole || null;
    
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
    const getActionButtons = (id, name) => {
        if (isUserAdmin()) {
            return `
                <button class="btn btn-sm btn-warning edit-btn" data-id="${id}">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${id}" data-name="${name}">Hapus</button>
            `;
        } else {
            return '<span class="text-muted">Read Only</span>'; // No action buttons for non-admin users
        }
    };
    
    // Helper function to get add button for admin only
    const getAddButton = () => {
        if (isUserAdmin()) {
            return '<button class="btn btn-primary add-member-btn">Tambah Anggota</button>';
        } else {
            // Show info for non-admin users
            return '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Anda memiliki akses baca saja. Untuk melakukan perubahan data, hubungi administrator.</div>';
        }
    };
    
    const showError = (message) => {
        if (appContent) {
            appContent.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
    };

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    // Fungsi untuk merender daftar anggota beserta pagination
    function renderMemberList(data) {
        // Simpan nilai search yang sedang diketik sebelum re-render
        const currentSearchValue = document.getElementById('search-input')?.value || '';
        
        const { anggota, pager } = data;
        let anggotaRows = '';
        if (anggota && anggota.length > 0) {
            anggota.forEach(anggota => {
                anggotaRows += `
                    <tr>
                        <td>${anggota.id_anggota}</td>
                        <td>${anggota.nama_depan}</td>
                        <td>${anggota.nama_belakang}</td>
                        <td>${anggota.gelar_depan}</td>
                        <td>${anggota.gelar_belakang}</td>
                        <td>${anggota.jabatan}</td>
                        <td>${anggota.status_pernikahan || 'Belum Kawin'}</td>
                        <td>
                            ${getActionButtons(anggota.id_anggota, `${anggota.nama_depan} ${anggota.nama_belakang}`)}
                        </td>
                    </tr>`;
            });
        } else {
            anggotaRows = '<tr><td colspan="8" class="text-center">Tidak ada data anggota.</td></tr>';
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
                <h2>Kelola Anggota</h2>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" id="search-input" placeholder="Cari ID, nama, jabatan, atau status..." style="width: 300px;">
                    <button class="btn btn-secondary" id="clear-search-btn">Clear</button>
                    ${getAddButton()}
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead><tr><th>ID</th><th>Nama Depan</th><th>Nama Belakang</th><th>Gelar Depan</th><th>Gelar Belakang</th><th>Jabatan</th><th>Status Pernikahan</th><th>Aksi</th></tr></thead>
                        <tbody>${anggotaRows}</tbody>
                    </table>
                    ${paginationHtml}
                </div>
            </div>`;
            
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

    // Fungsi untuk merender modal form tambah/edit anggota
    function renderMemberFormModal(title, anggota = {}) {
        const isEdit = !!anggota.id_anggota;
        const formId = isEdit ? 'edit-member-form' : 'add-member-form';
        const namaDepan = anggota.nama_depan || '';
        const namaBelakang = anggota.nama_belakang || '';
        const gelarDepan = anggota.gelar_depan || '';
        const gelarBelakang = anggota.gelar_belakang || '';
        const jabatan = anggota.jabatan || '';
        const statusPernikahan = anggota.status_pernikahan || 'Belum Kawin';
        const actionUrl = isEdit ? `/api/anggota/${anggota.id_anggota}` : '/api/anggota';
        const method = isEdit ? 'PUT' : 'POST';

        const modalHtml = `
            <div class="modal fade" id="memberFormModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="${formId}" data-url="${actionUrl}" data-method="${method}">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="form-nama-depan" class="form-label">Nama Depan</label>
                                    <input type="text" id="form-nama-depan" class="form-control" value="${namaDepan}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-nama-belakang" class="form-label">Nama Belakang</label>
                                    <input type="text" id="form-nama-belakang" class="form-control" value="${namaBelakang}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-gelar-depan" class="form-label">Gelar Depan</label>
                                    <input type="text" id="form-gelar-depan" class="form-control" value="${gelarDepan}">
                                </div>
                                <div class="mb-3">
                                    <label for="form-gelar-belakang" class="form-label">Gelar Belakang</label>
                                    <input type="text" id="form-gelar-belakang" class="form-control" value="${gelarBelakang}">
                                </div>
                                <div class="mb-3">
                                    <label for="form-jabatan" class="form-label">Jabatan</label>
                                    <select id="form-jabatan" class="form-select" required>
                                        <option value="">Pilih Jabatan</option>
                                        <option value="Ketua" ${jabatan === 'Ketua' ? 'selected' : ''}>Ketua</option>
                                        <option value="Wakil Ketua" ${jabatan === 'Wakil Ketua' ? 'selected' : ''}>Wakil Ketua</option>
                                        <option value="Anggota" ${jabatan === 'Anggota' ? 'selected' : ''}>Anggota</option>
                                        <option value="Sekretaris" ${jabatan === 'Sekretaris' ? 'selected' : ''}>Sekretaris</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="form-status-pernikahan" class="form-label">Status Pernikahan</label>
                                    <select id="form-status-pernikahan" class="form-select" required>
                                        <option value="Belum Kawin" ${statusPernikahan === 'Belum Kawin' ? 'selected' : ''}>Belum Kawin</option>
                                        <option value="Kawin" ${statusPernikahan === 'Kawin' ? 'selected' : ''}>Kawin</option>
                                    </select>
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
        const memberModal = new bootstrap.Modal(document.getElementById('memberFormModal'));
        memberModal.show();
    }

    // --- FUNGSI UTAMA & EVENT HANDLING ---

    // Fungsi untuk memuat daftar anggota dari API
    async function loadMembers(page = 1, search = '') {
        try {
            let url = `/api/anggota?page=${page}`;
            if (search.trim()) {
                url += `&search=${encodeURIComponent(search.trim())}`;
            }
            const data = await fetchData(url);
            renderMemberList(data);
        } catch (error) {
            // Check if user is not logged in
            if (error.message.includes('Authentication required') || error.message.includes('login')) {
                window.location.href = '/login';
                return;
            }
            // For other errors, show appropriate message
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Event listener untuk klik pada appContent (tombol aksi, pagination, dll.)
    appContent.addEventListener('click', async (event) => {

        if (event.target.classList.contains('add-member-btn')) {
            renderMemberFormModal('Tambah Anggota Baru');
        }

        if (event.target.classList.contains('edit-btn')) {
            const memberId = event.target.dataset.id;
            try {
                const memberData = await fetchData(`/api/anggota/${memberId}`);
                renderMemberFormModal('Edit Anggota', memberData);
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }

        if (event.target.classList.contains('delete-btn')) {
            const memberId = event.target.dataset.id;
            const memberName = event.target.dataset.name;

            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus anggota: <b>${memberName}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            });

            if (result.isConfirmed) {
                try {
                    // Kirim request DELETE ke API
                    const response = await fetchData(`/api/anggota/${memberId}`, {
                        method: 'DELETE'
                    });
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadMembers(); // Muat ulang daftar anggota
                } catch (error) {
                    Swal.fire('Error!', error.message, 'error');
                }
            }        
        }
    });

    // Event listener untuk submit form di modal (tambah/edit anggota)
    modalPlaceholder.addEventListener('submit', async (event) => {
        if (event.target.id === 'add-member-form' || event.target.id === 'edit-member-form') {
            event.preventDefault();
            const form = event.target;
            const url = form.dataset.url;
            const method = form.dataset.method;
            const formData = {
                nama_depan: document.getElementById('form-nama-depan').value,
                nama_belakang: document.getElementById('form-nama-belakang').value,
                gelar_depan: document.getElementById('form-gelar-depan').value,
                gelar_belakang: document.getElementById('form-gelar-belakang').value,
                jabatan: document.getElementById('form-jabatan').value,
                status_pernikahan: document.getElementById('form-status-pernikahan').value
            };

            console.log('🚀 Sending data:', formData); // Debug log
            console.log('📡 Method:', method, 'URL:', url); // Debug log

            try {
                const response = await fetchData(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                bootstrap.Modal.getInstance(document.getElementById('memberFormModal')).hide();
                Swal.fire('Berhasil!', response.message, 'success');
                loadMembers();
            } catch (error) {
                console.error('❌ Error submitting form:', error); // Debug log
                Swal.fire('Error!', error.message, 'error');
            }
        }
    });

    // Event handler untuk search input (menggunakan debounce)
    appContent.addEventListener('input', (event) => {
        if (event.target.id === 'search-input') {
            // Debounce search untuk menghindari terlalu banyak request
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                const searchValue = event.target.value;
                loadMembers(1, searchValue);
            }, 500);
        }
    });

    // Event handler untuk clear search button
    appContent.addEventListener('click', (event) => {
        if (event.target.id === 'clear-search-btn') {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
                loadMembers(1, '');
            }
        }
    });
});