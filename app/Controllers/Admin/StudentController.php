<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\TakeModel; 
class StudentController extends BaseController
{
    // Menampilkan daftar mahasiswa
    public function spaShell()
    {
        $data = [
            'title' => 'Kelola Mahasiswa'
        ];
        // Selalu kembalikan view yang sama, yaitu index.php
        return view('admin/students/index', $data);
    }
    /**
     * Menampilkan form untuk menambah mahasiswa baru.
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Mahasiswa Baru'
        ];
        return view('admin/students/create', $data);
    }

    /**
     * Menyimpan data mahasiswa baru ke database.
     */
    public function store()
    {
        $userModel = new UserModel();
        $studentModel = new StudentModel();

        // 1. Siapkan data untuk tabel 'users'
        $userData = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => 'student'
        ];

        // 2. Simpan data user
        if ($userModel->save($userData)) {
            // 3. Ambil ID dari user yang baru saja dibuat
            $userId = $userModel->getInsertID();

            // 4. Siapkan dan simpan data untuk tabel 'students'
            $studentData = [
                'user_id'    => $userId,
                'entry_year' => $this->request->getPost('entry_year')
            ];
            $studentModel->save($studentData);

            return redirect()->to('/admin/students')->with('message', 'Mahasiswa baru berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }
    }
    // Melihat detail mahasiswa dan mata kuliah yang diambil
    public function view($userId)
    {
        $userModel = new UserModel();
        $studentModel = new StudentModel();
        $takeModel = new TakeModel();
        
        $user = $userModel->find($userId);

        if(!$user || $user->role !== 'student') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 1. Cari student_id berdasarkan user_id
        $studentId = $studentModel->getStudentIdByUserId($userId);

        $enrolledCourses = [];
        if ($studentId) {
            $enrolledCourses = $takeModel->getEnrolledCourses($studentId);
        }

        $data = [
            'title'    => 'Detail Mahasiswa',
            'student'  => $user, // Mengirim data dari tabel 'users'
            'courses'  => $enrolledCourses,
        ];

        return view('admin/students/view', $data);
    }
}