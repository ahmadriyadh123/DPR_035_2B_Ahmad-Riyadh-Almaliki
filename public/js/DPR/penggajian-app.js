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
            <h2>Data Penggajian Anggota DPR</h2>
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
    });

    // Muat data penggajian saat halaman pertama kali dibuka
    loadPenggajian();
});