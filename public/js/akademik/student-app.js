// File: public/js/student-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');
    const modalPlaceholder = document.getElementById('modal-placeholder');

    /**
     * ===================================================================
     * FUNGSI-FUNGSI UNTUK MERENDER TAMPILAN (VIEWS)
     * ===================================================================
     */

    // Fungsi untuk merender daftar mahasiswa beserta pagination
    function renderStudentList(data) {
        const { students, pager } = data;
        let studentRows = '';

        if (students && students.length > 0) {
            students.forEach(student => {
                studentRows += `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.full_name || student.username}</td>
                        <td>${student.username}</td>
                        <td>
                            <button class="btn btn-sm btn-info view-detail-btn" data-id="${student.id}">Detail</button>
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${student.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${student.id}" data-username="${student.username}">Hapus</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            studentRows = '<tr><td colspan="4" class="text-center">Tidak ada data mahasiswa.</td></tr>';
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
                <h2>Daftar Mahasiswa</h2>
                <button class="btn btn-primary add-student-btn">Tambah Mahasiswa</button>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr> <th>ID</th> <th>Nama Lengkap</th> <th>Username</th> <th>Aksi</th> </tr>
                </thead>
                <tbody>
                    ${studentRows}
                </tbody>
            </table>
            ${paginationHtml}
        `;
    }

    // Fungsi untuk merender modal form tambah mahasiswa baru
    function renderAddFormModal() {
        const modalHtml = `
            <div class="modal fade" id="addStudentModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="add-student-form">
                            <div class="modal-header">
                                <h5 class="modal-title">Tambah Mahasiswa Baru</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="add-username" class="form-label">Username</label>
                                    <input type="text" id="add-username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add-fullname" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="add-fullname" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add-password" class="form-label">Password</label>
                                    <input type="password" id="add-password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add-entryyear" class="form-label">Tahun Angkatan</label>
                                    <input type="number" id="add-entryyear" class="form-control" placeholder="Contoh: 2023" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        modalPlaceholder.innerHTML = modalHtml;
        const addModal = new bootstrap.Modal(document.getElementById('addStudentModal'));
        addModal.show();
    }

    // Fungsi untuk merender modal form edit mahasiswa
    // 2. Buat fungsi baru untuk merender form edit di dalam modal
    function renderEditFormModal(data) {
        const { user, student } = data;
        const modalHtml = `
            <div class="modal fade" id="editStudentModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="edit-student-form" data-id="${user.id}">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Mahasiswa: ${user.full_name}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit-username" class="form-label">Username</label>
                                    <input type="text" id="edit-username" class="form-control" value="${user.username}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-fullname" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="edit-fullname" class="form-control" value="${user.full_name}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-entryyear" class="form-label">Tahun Angkatan</label>
                                    <input type="number" id="edit-entryyear" class="form-control" value="${student.entry_year}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-password" class="form-label">Password Baru (Opsional)</label>
                                    <input type="password" id="edit-password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        modalPlaceholder.innerHTML = modalHtml;
        const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        editModal.show();
    }
    
    // Fungsi untuk menampilkan modal detail mahasiswa beserta opsi tambah/hapus mata kuliah
    async function showStudentDetailModal(data, availableCourses) {
        const { user, courses } = data;
        
        // Render baris untuk mata kuliah yang sudah diambil
        let enrolledCoursesHtml = '';
        if (courses && courses.length > 0) {
            courses.forEach(course => {
                enrolledCoursesHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${course.course_name} (${course.credits} SKS)
                        <button class="btn btn-sm btn-outline-danger remove-course-btn" data-take-id="${course.take_id}">Hapus</button>
                    </li>`;
            });
        } else {
            enrolledCoursesHtml = '<li class="list-group-item">Mahasiswa ini belum mengambil mata kuliah.</li>';
        }

        // Render opsi untuk dropdown mata kuliah yang tersedia
        let availableCoursesOptions = '';
        if(availableCourses && availableCourses.length > 0){
            availableCourses.forEach(course => {
                availableCoursesOptions += `<option value="${course.id}">${course.course_name}</option>`;
            });
        } else {
            availableCoursesOptions = '<option disabled>Tidak ada mata kuliah tersedia</option>';
        }

        const modalHtml = `
            <div class="modal fade" id="studentDetailModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${user.full_name}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Username:</strong> ${user.username}</p>
                            <hr>
                            <h6>Mata Kuliah yang Diambil</h6>
                            <ul class="list-group mb-4">${enrolledCoursesHtml}</ul>
                            
                            <h6>Tambahkan Mata Kuliah Baru</h6>
                            <div class="input-group">
                                <select id="available-courses-select" class="form-select">
                                    ${availableCoursesOptions}
                                </select>
                                <button class="btn btn-primary add-course-to-student-btn" data-user-id="${user.id}" ${!availableCourses || availableCourses.length === 0 ? 'disabled' : ''}>
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

        modalPlaceholder.innerHTML = modalHtml;
        const studentModal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
        studentModal.show();
    }


    /**
     * ===================================================================
     * FUNGSI UTAMA & EVENT HANDLING
     * ===================================================================
     */

    // Fungsi utama untuk memuat data berdasarkan halaman
    async function loadStudents(page = 1) {
        try {
            const data = await fetchData(`/api/students?page=${page}`);
            renderStudentList(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Event listener untuk menangani semua klik di dalam #app-content
    appContent.addEventListener('click', async (event) => {
        if (event.target.classList.contains('view-detail-btn')) {
            const userId = event.target.dataset.id;
            const button = event.target;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            button.disabled = true;

            try {
                // Ambil detail student DAN mata kuliah yang tersedia secara bersamaan
                const [studentData, availableCourses] = await Promise.all([
                    fetchData(`/api/students/${userId}`),
                    // --- UBAH URL DI BARIS INI ---
                    fetchData(`/api/available-courses/${userId}`) 
                ]);
                showStudentDetailModal(studentData, availableCourses);
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            } finally {
                button.innerHTML = 'Detail';
                button.disabled = false;
            }
        }
        if (event.target.classList.contains('add-student-btn')) {
            renderAddFormModal();
        }    
        // --- Logika untuk Tombol Edit ---
        if (event.target.classList.contains('edit-btn')) {
            const userId = event.target.dataset.id;
            try {
                // Ambil data student yang akan diedit dari API show()
                const studentData = await fetchData(`/api/students/${userId}`);
                renderEditFormModal(studentData);
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }

        // --- Logika untuk Tombol Delete ---
        if (event.target.classList.contains('delete-btn')) {
            const userId = event.target.dataset.id;
            const username = event.target.dataset.username;

            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus mahasiswa: <b>${username}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            });

            if (result.isConfirmed) {
                try {
                    // Kirim request DELETE ke API
                    const response = await fetchData(`/api/students/${userId}`, {
                        method: 'DELETE'
                    });
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadStudents(); // Muat ulang daftar mahasiswa
                } catch (error) {
                    Swal.fire('Error!', error.message, 'error');
                }
            }
        }
    });

    // Event listener untuk menangani klik di dalam modal (tambah/hapus mata kuliah)
    modalPlaceholder.addEventListener('click', async (event) => {
        // Tombol untuk menambah mata kuliah baru ke mahasiswa
        if (event.target.classList.contains('add-course-to-student-btn')) {
            const button = event.target;
            const userId = button.dataset.userId;
            const courseId = document.getElementById('available-courses-select').value;

            if (!courseId) return;

            try {
                await fetchData('/api/enrollments', {
                    method: 'POST',
                    body: JSON.stringify({ user_id: userId, course_id: courseId })
                });
                Swal.fire('Berhasil!', 'Mata kuliah telah ditambahkan.', 'success');
                
                // Refresh modal
                bootstrap.Modal.getInstance(document.getElementById('studentDetailModal')).hide();
                document.querySelector(`.view-detail-btn[data-id="${userId}"]`).click(); // Trik untuk membuka kembali modal
            } catch (error) {
                Swal.fire('Error!', 'Gagal menambahkan mata kuliah.', 'error');
            }
        }

        // Tombol untuk menghapus mata kuliah dari mahasiswa
        if (event.target.classList.contains('remove-course-btn')) {
            const button = event.target;
            const takeId = button.dataset.takeId;
            const userId = document.querySelector('.add-course-to-student-btn').dataset.userId;

            const result = await Swal.fire({
                title: 'Anda yakin?',
                text: "Mata kuliah ini akan dihapus dari mahasiswa.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            });

            if (result.isConfirmed) {
                try {
                    await fetchData(`/api/enrollments/${takeId}`, { method: 'DELETE' });
                    Swal.fire('Berhasil!', 'Mata kuliah telah dihapus.', 'success');
                    
                    // Refresh modal
                    bootstrap.Modal.getInstance(document.getElementById('studentDetailModal')).hide();
                    document.querySelector(`.view-detail-btn[data-id="${userId}"]`).click();
                } catch (error) {
                    Swal.fire('Error!', 'Gagal menghapus mata kuliah.', 'error');
                }
            }
        }
    });

    // 4. Tambahkan Event Listener untuk submit form edit di dalam modal
    // Event listener untuk submit form tambah/edit mahasiswa di modal
    modalPlaceholder.addEventListener('submit', async (event) => {
        if (event.target.id === 'add-student-form') {
        event.preventDefault();
        
        // Kumpulkan data dari form tambah
        const formData = {
            username: document.getElementById('add-username').value,
            full_name: document.getElementById('add-fullname').value,
            password: document.getElementById('add-password').value,
            entry_year: document.getElementById('add-entryyear').value,
        };

        try {
            // Kirim request POST ke API
            const response = await fetchData(`/api/students`, {
                method: 'POST',
                body: JSON.stringify(formData)
            });

            // Tutup modal
            bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
            
            Swal.fire('Berhasil!', response.message, 'success');
            loadStudents(); // Muat ulang daftar mahasiswa untuk menampilkan data baru
        } catch (error) {
            Swal.fire('Error!', error.message, 'error');
            }
        }
        if (event.target.id === 'edit-student-form') {
            event.preventDefault();
            const form = event.target;
            const userId = form.dataset.id;

            // Kumpulkan data dari form
            const formData = {
                username: document.getElementById('edit-username').value,
                full_name: document.getElementById('edit-fullname').value,
                entry_year: document.getElementById('edit-entryyear').value,
                password: document.getElementById('edit-password').value,
            };

            try {
                // Kirim request PUT ke API
                const response = await fetchData(`/api/students/${userId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                // Tutup modal
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
                editModal.hide();
                
                Swal.fire('Berhasil!', response.message, 'success');
                loadStudents(); // Muat ulang daftar mahasiswa
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    });

    // Muat halaman pertama saat aplikasi dibuka
    loadStudents(1);
});