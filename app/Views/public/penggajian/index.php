<?= $this->extend('public/template') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-money-bill-wave text-success"></i> Data Penggajian DPR</h2>
    <span class="badge bg-success fs-6">Akses Publik</span>
</div>

<!-- Search and Controls -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="search-input" class="form-control" placeholder="Cari berdasarkan nama, jabatan, atau gaji...">
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
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memuat data penggajian...</p>
</div>

<!-- Content Area -->
<div id="app-content">
    <!-- Data will be loaded here via JavaScript -->
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Public Penggajian App initialized');
    
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
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    // Fetch and render penggajian data
    async function fetchPenggajianData(page = 1, search = '') {
        showLoading('Memuat Data Penggajian...');
        
        try {
            const params = new URLSearchParams({
                page: page
            });
            
            if (search) {
                params.append('search', search);
            }
            
            const response = await fetch(`${apiUrl}/penggajian?${params}`);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Gagal memuat data penggajian');
            }
            
            renderPenggajianTable(result.data, result.pagination);
            
        } catch (error) {
            console.error('Error fetching penggajian:', error);
            showError(error.message);
        } finally {
            hideLoading();
        }
    }
    
    // Render penggajian table
    function renderPenggajianTable(data, pagination) {
        if (!data || data.length === 0) {
            appContent.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    Tidak ada data penggajian yang ditemukan.
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
                                    <th>ID Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th>Jabatan</th>
                                    <th>Take Home Pay</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        data.forEach(penggajian => {
            html += `
                <tr>
                    <td>${penggajian.id_anggota}</td>
                    <td>${penggajian.nama_anggota}</td>
                    <td>
                        <span class="badge bg-primary">${penggajian.jabatan}</span>
                    </td>
                    <td>
                        <strong class="text-success">${formatCurrency(penggajian.take_home_pay)}</strong>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info view-btn" data-id="${penggajian.id_anggota}">
                            <i class="fas fa-eye"></i> Detail
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
        if (!pagination || pagination.total_pages <= 1) {
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
        for (let i = 1; i <= pagination.total_pages; i++) {
            const isActive = i === pagination.current_page;
            html += `
                <li class="page-item ${isActive ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
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
    
    // View penggajian detail
    async function viewPenggajianDetail(id) {
        showLoading('Memuat detail penggajian...');
        
        try {
            const response = await fetch(`${apiUrl}/penggajian/${id}`);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Gagal memuat detail penggajian');
            }
            
            const penggajian = result.data;
            
            let komponenHtml = '';
            if (penggajian.komponen_gaji && penggajian.komponen_gaji.length > 0) {
                komponenHtml = `
                    <h6 class="mt-3">Komponen Gaji:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Nominal</th>
                                    <th>Jenis</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                penggajian.komponen_gaji.forEach(komponen => {
                    const jenisClass = komponen.jenis === 'Tunjangan' ? 'success' : 'warning';
                    komponenHtml += `
                        <tr>
                            <td>${komponen.nama_komponen}</td>
                            <td>${formatCurrency(komponen.nominal)}</td>
                            <td><span class="badge bg-${jenisClass}">${komponen.jenis}</span></td>
                        </tr>
                    `;
                });
                
                komponenHtml += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            Swal.fire({
                title: 'Detail Penggajian DPR',
                html: `
                    <div class="text-start">
                        <table class="table table-borderless">
                            <tr><th>ID Anggota:</th><td>${penggajian.id_anggota}</td></tr>
                            <tr><th>Nama Anggota:</th><td>${penggajian.nama_anggota}</td></tr>
                            <tr><th>Jabatan:</th><td><span class="badge bg-primary">${penggajian.jabatan}</span></td></tr>
                            <tr><th>Status Pernikahan:</th><td>${penggajian.status_pernikahan || '-'}</td></tr>
                            <tr><th>Jumlah Anak:</th><td>${penggajian.jumlah_anak || 0}</td></tr>
                            <tr><th>Take Home Pay:</th><td><strong class="text-success">${formatCurrency(penggajian.take_home_pay)}</strong></td></tr>
                        </table>
                        ${komponenHtml}
                    </div>
                `,
                width: '700px',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#3085d6'
            });
            
        } catch (error) {
            console.error('Error fetching penggajian detail:', error);
            Swal.fire({
                icon: 'error',
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
            viewPenggajianDetail(id);
        } else if (event.target.closest('[data-page]')) {
            event.preventDefault();
            const page = event.target.closest('[data-page]').getAttribute('data-page');
            const search = document.getElementById('search-input').value;
            fetchPenggajianData(parseInt(page), search);
        }
    });
    
    // Search functionality
    document.getElementById('search-input').addEventListener('input', (event) => {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            const searchValue = event.target.value;
            fetchPenggajianData(1, searchValue);
        }, 500);
    });
    
    // Clear search
    document.getElementById('clear-search-btn').addEventListener('click', () => {
        document.getElementById('search-input').value = '';
        fetchPenggajianData(1, '');
    });
    
    // Initial load
    fetchPenggajianData();
});
</script>
<?= $this->endSection() ?>