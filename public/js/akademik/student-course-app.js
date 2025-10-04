// File: public/js/student-course-app.js

// Event listener utama yang dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    const appContent = document.getElementById('app-content');

    // Fungsi untuk merender tampilan
    function renderCoursePage(data) {
        const { enrolled, available } = data;

        // Bagian 1: HTML untuk mata kuliah yang sudah diambil
        let enrolledHtml = '';
        if (enrolled && enrolled.length > 0) {
            enrolled.forEach(course => {
                enrolledHtml += `
                    <tr>
                        <td>${course.course_name}</td>
                        <td class="text-center">${course.credits}</td>
                    </tr>`;
            });
        } else {
            enrolledHtml = '<tr><td colspan="2" class="text-center">Anda belum mengambil mata kuliah apapun.</td></tr>';
        }

        // Bagian 2: HTML untuk mata kuliah yang tersedia
        let availableHtml = '';
        if (available && available.length > 0) {
            available.forEach(course => {
                availableHtml += `
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input course-checkbox" type="checkbox" value="${course.id}" data-credits="${course.credits}">
                            </div>
                        </td>
                        <td>${course.course_name}</td>
                        <td class="text-center">${course.credits}</td>
                    </tr>`;
            });
        } else {
            availableHtml = '<tr><td colspan="3" class="text-center">Selamat! Semua mata kuliah telah diambil.</td></tr>';
        }
        
        // Gabungkan semua bagian menjadi satu tampilan
        appContent.innerHTML = `
            <div class="mb-5">
                <h3>Mata Kuliah yang Telah Diambil</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center" style="width: 10%;">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>${enrolledHtml}</tbody>
                </table>
            </div>

            <div>
                <h3>Mata Kuliah Tersedia</h3>
                <p>Silakan pilih mata kuliah yang ingin Anda ambil semester ini.</p>
                <form id="enroll-form">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">Pilih</th>
                                <th>Nama Mata Kuliah</th>
                                <th class="text-center" style="width: 10%;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>${availableHtml}</tbody>
                    </table>
                    ${available && available.length > 0 ? `
                    <div class="d-flex justify-content-end align-items-center mt-3">
                        <h4 class="me-3">Total Kredit Dipilih: <span id="total-credits" class="badge bg-success">0</span></h4>
                        <button type="submit" class="btn btn-primary">Daftarkan Mata Kuliah</button>
                    </div>` : ''}
                </form>
            </div>
        `;
    }

    // Fungsi utama untuk memuat data
    async function loadCoursesData() {
        appContent.innerHTML = '<h4>Memuat data mata kuliah...</h4>';
        try {
            // Panggil API baru
            const data = await fetchData('/api/student/courses-data');
            renderCoursePage(data); // Kirim semua data ke fungsi render
        } catch (error) {
            appContent.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        }
    }

    // Event handling
    // Event listener untuk perubahan checkbox, menghitung total kredit
    appContent.addEventListener('change', (event) => {
        if (event.target.classList.contains('course-checkbox')) {
            let total = 0;
            document.querySelectorAll('.course-checkbox:checked').forEach(checkbox => {
                total += parseInt(checkbox.dataset.credits, 10);
            });
            document.getElementById('total-credits').textContent = total;
        }
    });

    // Event listener untuk submit form pendaftaran mata kuliah
    appContent.addEventListener('submit', async (event) => {
        if (event.target.id === 'enroll-form') {
            event.preventDefault();
            const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
            if (checkedBoxes.length === 0) {
                Swal.fire('Peringatan', 'Anda belum memilih mata kuliah sama sekali.', 'warning');
                return;
            }

            const courseIds = Array.from(checkedBoxes).map(cb => cb.value);

            try {
                const response = await fetchData('/api/student/enroll-batch', {
                    method: 'POST',
                    body: JSON.stringify({ course_ids: courseIds })
                });

                await Swal.fire('Berhasil!', response.message, 'success');
                loadCoursesData(); // Muat ulang daftar mata kuliah setelah berhasil
            } catch (error) {
                Swal.fire('Error!', 'Gagal mendaftarkan mata kuliah.', 'error');
            }
        }
    });

    // Muat data saat halaman pertama kali dibuka
    loadCoursesData();
});