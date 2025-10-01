// File: public/js/DPR/anggota-anggota-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    // Fungsi untuk merender daftar anggota beserta pagination (view only)
    function renderAnggotaList(data) {
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
                    </tr>`;
            });
        } else {
            anggotaRows = '<tr><td colspan="6" class="text-center">Tidak ada data anggota.</td></tr>';
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
            <h2>Daftar Anggota DPR</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Anggota</th>
                                <th>Nama Depan</th>
                                <th>Nama Belakang</th>
                                <th>Gelar Depan</th>
                                <th>Gelar Belakang</th>
                                <th>Jabatan</th>
                            </tr>
                        </thead>
                        <tbody>${anggotaRows}</tbody>
                    </table>
                    ${paginationHtml}
                </div>
            </div>
        `;
    }

    // --- FUNGSI-FUNGSI API ---

    // Fungsi untuk memuat daftar anggota dengan pagination
    async function loadMembers(page = 1) {
        try {
            appContent.innerHTML = '<h4>Memuat data anggota...</h4>';
            const data = await fetchData(`/api/anggota?page=${page}`);
            renderAnggotaList(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // --- EVENT LISTENERS ---

    // Event listener untuk pagination
    appContent.addEventListener('click', (event) => {
        if (event.target.matches('.page-link')) {
            event.preventDefault();
            const page = parseInt(event.target.dataset.page, 10);
            if (page && page > 0) {
                loadMembers(page);
            }
        }
    });

    // Muat data anggota saat halaman pertama kali dibuka
    loadMembers();
});
