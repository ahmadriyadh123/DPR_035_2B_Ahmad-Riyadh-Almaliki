// File: public/js/course-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');
    const modalPlaceholder = document.getElementById('modal-placeholder');

    // --- FUNGSI-FUNGSI RENDER TAMPILAN ---

    // Fungsi untuk merender daftar mata kuliah beserta pagination
    function renderCourseList(data) {
        const { courses, pager } = data;
        let courseRows = '';
        if (courses && courses.length > 0) {
            courses.forEach(course => {
                courseRows += `
                    <tr>
                        <td>${course.id}</td>
                        <td>${course.course_name}</td>
                        <td>${course.credits}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${course.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${course.id}" data-name="${course.course_name}">Hapus</button>
                        </td>
                    </tr>`;
            });
        } else {
            courseRows = '<tr><td colspan="4" class="text-center">Tidak ada data mata kuliah.</td></tr>';
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
                <h2>Kelola Mata Kuliah</h2>
                <button class="btn btn-primary add-course-btn">Tambah Mata Kuliah</button>
            </div>
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Nama Mata Kuliah</th><th>Kredit</th><th>Aksi</th></tr></thead>
                <tbody>${courseRows}</tbody>
            </table>
            ${paginationHtml}`;
    }

    // Fungsi untuk merender modal form tambah/edit mata kuliah
    function renderCourseFormModal(title, course = {}) {
        const isEdit = !!course.id;
        const formId = isEdit ? 'edit-course-form' : 'add-course-form';
        const courseName = course.course_name || '';
        const credits = course.credits || '';
        const actionUrl = isEdit ? `/api/courses/${course.id}` : '/api/courses';
        const method = isEdit ? 'PUT' : 'POST';

        const modalHtml = `
            <div class="modal fade" id="courseFormModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="${formId}" data-url="${actionUrl}" data-method="${method}">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="form-coursename" class="form-label">Nama Mata Kuliah</label>
                                    <input type="text" id="form-coursename" class="form-control" value="${courseName}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="form-credits" class="form-label">Kredit</label>
                                    <input type="number" id="form-credits" class="form-control" value="${credits}" required>
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
        const courseModal = new bootstrap.Modal(document.getElementById('courseFormModal'));
        courseModal.show();
    }

    // --- FUNGSI UTAMA & EVENT HANDLING ---

    // Fungsi untuk memuat daftar mata kuliah dari API
    async function loadCourses(page = 1) {
        try {
            const data = await fetchData(`/api/courses?page=${page}`);
            renderCourseList(data);
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Event listener untuk klik pada appContent (tombol aksi, pagination, dll.)
    appContent.addEventListener('click', async (event) => {
        // ... (logika klik pagination)

        if (event.target.classList.contains('add-course-btn')) {
            renderCourseFormModal('Tambah Mata Kuliah Baru');
        }

        if (event.target.classList.contains('edit-btn')) {
            const courseId = event.target.dataset.id;
            try {
                const courseData = await fetchData(`/api/courses/${courseId}`);
                renderCourseFormModal('Edit Mata Kuliah', courseData);
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }

        if (event.target.classList.contains('delete-btn')) {
            const courseId = event.target.dataset.id;
            const courseName = event.target.dataset.coursename;

            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus course: <b>${courseName}</b>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            });

            if (result.isConfirmed) {
                try {
                    // Kirim request DELETE ke API
                    const response = await fetchData(`/api/courses/${courseId}`, {
                        method: 'DELETE'
                    });
                    Swal.fire('Berhasil!', response.message, 'success');
                    loadCourses(); // Muat ulang daftar mata kuliah
                } catch (error) {
                    Swal.fire('Error!', error.message, 'error');
                }
            }        
        }
    });

    // Event listener untuk submit form di modal (tambah/edit mata kuliah)
    modalPlaceholder.addEventListener('submit', async (event) => {
        if (event.target.id === 'add-course-form' || event.target.id === 'edit-course-form') {
            event.preventDefault();
            const form = event.target;
            const url = form.dataset.url;
            const method = form.dataset.method;
            const formData = {
                course_name: document.getElementById('form-coursename').value,
                credits: document.getElementById('form-credits').value,
            };

            try {
                const response = await fetchData(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                bootstrap.Modal.getInstance(document.getElementById('courseFormModal')).hide();
                Swal.fire('Berhasil!', response.message, 'success');
                loadCourses();
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        }
    });

    // Inisialisasi: muat daftar mata kuliah pertama kali
    loadCourses(1);
});