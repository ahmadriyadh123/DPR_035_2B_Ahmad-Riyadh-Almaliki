<?= $this->extend('public/template') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users text-primary"></i> Data Anggota DPR</h2>
    <span class="badge bg-success fs-6">Akses Publik</span>
</div>

<!-- Search and Controls -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="search-input" class="form-control" placeholder="Cari berdasarkan nama atau jabatan...">
            <button class="btn btn-outline-secondary" id="clear-search-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <small class="text-muted">
            <i class="fas fa-eye"></i> Mode Tampilan: Read Only
        </small>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loading" class="text-center my-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memuat data anggota...</p>
</div>

<!-- Content Area -->
<div id="app-content">
    <!-- Data will be loaded here via JavaScript -->
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Public Anggota App initialized');
    
    const appContent = document.getElementById('app-content');
    const loadingElement = document.getElementById('loading');
    const apiUrl = document.querySelector('meta[name="api-url"]').content;
    
    // Show loading
    function showLoading(message = 'Memuat data...') {
        loadingElement.style.display = 'block';
        loadingElement.querySelector('p').textContent = message;
    }
    
    // Hide loading
    function hideLoading() {
        loadingElement.style.display = 'none';
    }
    
    // Show error
    function showError(message) {
        appContent.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> ${message}
            </div>
        `;
    }
    
    // Fetch and render anggota data
    async function fetchAnggotaData(page = 1, search = '') {
        showLoading('Memuat Data Anggota...');
        
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 10
            });
            
            if (search) {
                params.append('search', search);
            }
            
            const response = await fetch(`${apiUrl}/anggota?${params}`);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Gagal memuat data anggota');
            }
            
            renderAnggotaTable(result.data, result.pagination);
            
        } catch (error) {
            console.error('Error fetching anggota:', error);
            showError(error.message);
        } finally {
            hideLoading();
        }
    }
    
    // Render anggota table
    function renderAnggotaTable(data, pagination) {
        if (!data || data.length === 0) {
            appContent.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    Tidak ada data anggota yang ditemukan.
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jabatan</th>
                                    <th>Status Pernikahan</th>
                                    <th>Jumlah Anak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        data.forEach(anggota => {
            html += `
                <tr>
                    <td>${anggota.id_anggota}</td>
                    <td>${anggota.nama_lengkap}</td>
                    <td>
                        <span class="badge bg-primary">${anggota.jabatan}</span>
                    </td>
                    <td>${anggota.status_pernikahan || '-'}</td>
                    <td>${anggota.jumlah_anak || 0}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info view-btn" data-id="${anggota.id_anggota}">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    ${renderPagination(pagination)}
                </div>
            </div>
        `;
        
        appContent.innerHTML = html;
    }
    
    // Render pagination
    function renderPagination(pagination) {
        if (!pagination || pagination.total <= pagination.per_page) {
            return '';
        }
        
        let html = `
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
        `;
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
            `;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.total; i++) {
            const isActive = i === pagination.current_page;
            html += `
                <li class="page-item ${isActive ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        if (pagination.current_page < pagination.total) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
        }
        
        html += `
                </ul>
            </nav>
        `;
        
        return html;
    }
    
    // View anggota detail
    async function viewAnggotaDetail(id) {
        showLoading('Memuat detail anggota...');
        
        try {
            const response = await fetch(`${apiUrl}/anggota/${id}`);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Gagal memuat detail anggota');
            }
            
            const anggota = result.data;
            
            Swal.fire({
                title: 'Detail Anggota DPR',
                html: `
                    <div class="text-start">
                        <table class="table table-borderless">
                            <tr><th>ID Anggota:</th><td>${anggota.id_anggota}</td></tr>
                            <tr><th>Nama Lengkap:</th><td>${anggota.nama_lengkap}</td></tr>
                            <tr><th>Gelar Depan:</th><td>${anggota.gelar_depan || '-'}</td></tr>
                            <tr><th>Nama Depan:</th><td>${anggota.nama_depan}</td></tr>
                            <tr><th>Nama Belakang:</th><td>${anggota.nama_belakang}</td></tr>
                            <tr><th>Gelar Belakang:</th><td>${anggota.gelar_belakang || '-'}</td></tr>
                            <tr><th>Jabatan:</th><td><span class="badge bg-primary">${anggota.jabatan}</span></td></tr>
                            <tr><th>Status Pernikahan:</th><td>${anggota.status_pernikahan || '-'}</td></tr>
                            <tr><th>Jumlah Anak:</th><td>${anggota.jumlah_anak || 0}</td></tr>
                        </table>
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#3085d6'
            });
            
        } catch (error) {
            console.error('Error fetching anggota detail:', error);
            Swal.fire({
                title: 'Error',
                text: error.message
            });
        } finally {
            hideLoading();
        }
    }
    
    // Event Listeners
    appContent.addEventListener('click', (event) => {
        if (event.target.closest('.view-btn')) {
            const id = event.target.closest('.view-btn').getAttribute('data-id');
            viewAnggotaDetail(id);
        } else if (event.target.closest('[data-page]')) {
            event.preventDefault();
            const page = event.target.closest('[data-page]').getAttribute('data-page');
            const search = document.getElementById('search-input').value;
            fetchAnggotaData(parseInt(page), search);
        }
    });
    
    // Search functionality
    document.getElementById('search-input').addEventListener('input', (event) => {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            const searchValue = event.target.value;
            fetchAnggotaData(1, searchValue);
        }, 500);
    });
    
    // Clear search
    document.getElementById('clear-search-btn').addEventListener('click', () => {
        document.getElementById('search-input').value = '';
        fetchAnggotaData(1, '');
    });
    
    // Initial load
    fetchAnggotaData();
});
</script>
<?= $this->endSection() ?>