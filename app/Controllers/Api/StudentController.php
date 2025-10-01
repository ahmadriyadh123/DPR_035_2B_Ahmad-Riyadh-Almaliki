<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\TakeModel;

class StudentController extends ResourceController
{
    /**
     * Mengambil daftar semua mahasiswa
     */
    public function index()
    {
        $userModel = new UserModel();
        
        // Ambil nomor halaman dari URL, default ke halaman 1
        $page = $this->request->getGet('page') ?? 1;
        // Tentukan jumlah item per halaman
        $perPage = 10; 

        $keyword = $this->request->getGet('search');
        
        if ($keyword) {
            $students = $userModel->searchStudents($keyword, $perPage);
        } else {
            $students = $userModel->getStudents($perPage);
        }

        // Siapkan data respons
        $data = [
            'students' => $students,
            'pager'    => $userModel->pager->getDetails() // Ambil detail pagination
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil detail satu mahasiswa
     */
    public function show($userId = null)
    {
        $userModel = new UserModel();
        $studentModel = new StudentModel();
        $takeModel = new TakeModel();

        $user = $userModel->find($userId);
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan');
        }
        
        $studentData = $studentModel->where('user_id', $userId)->first();
        $enrolledCourses = [];
        if ($studentData) {
            // Modifikasi getEnrolledCourses di TakeModel untuk menyertakan take_id
            $takeModel = new \App\Models\TakeModel();
            $enrolledCourses = $takeModel->builder('takes')
                ->select('takes.id as take_id, courses.course_name, courses.credits')
                ->join('courses', 'courses.id = takes.course_id')
                ->where('takes.student_id', $studentData->id)
                ->get()->getResult();
        }
        $data = [
            'user'    => $user,
            'student' => $studentData,
            'courses' => $enrolledCourses,
        ];

        return $this->respond($data);
    }
    public function create()
    {
        $userModel = new UserModel();
        $studentModel = new StudentModel();
        
        $json = $this->request->getJSON();

        // Validasi sederhana
        if (empty($json->username) || empty($json->password) || empty($json->full_name) || empty($json->entry_year)) {
            return $this->fail('Semua field harus diisi.', 400);
        }

        // Siapkan data untuk tabel 'users'
        $userData = [
            'username'  => $json->username,
            'full_name' => $json->full_name,
            'password'  => password_hash($json->password, PASSWORD_DEFAULT),
            'role'      => 'student'
        ];

        // Simpan data user
        if ($userModel->save($userData)) {
            $userId = $userModel->getInsertID();

            // Siapkan dan simpan data untuk tabel 'students'
            $studentData = [
                'user_id'    => $userId,
                'entry_year' => $json->entry_year
            ];
            $studentModel->save($studentData);

            return $this->respondCreated(['message' => 'Mahasiswa baru berhasil ditambahkan.']);
        }
        
        return $this->fail('Gagal menyimpan data mahasiswa.');
    }
    public function update($userId = null)
    {
        $userModel = new UserModel();
        $studentModel = new StudentModel();
        
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        $json = $this->request->getJSON();

        // Data untuk tabel users
        $userData = [
            'username'  => $json->username,
            'full_name' => $json->full_name,
        ];
        if (!empty($json->password)) {
            $userData['password'] = password_hash($json->password, PASSWORD_DEFAULT);
        }
        $userModel->update($userId, $userData);

        // Data untuk tabel students
        $student = $studentModel->where('user_id', $userId)->first();
        $studentData = ['entry_year' => $json->entry_year];
        $studentModel->update($student->id, $studentData);
        
        return $this->respondUpdated(['message' => 'Data mahasiswa berhasil diperbarui.']);
    }

    /**
     * Menghapus data mahasiswa.
     */
    public function delete($userId = null)
    {
        $userModel = new UserModel();
        
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        $userModel->delete($userId);

        return $this->respondDeleted(['message' => 'Data mahasiswa berhasil dihapus.']);
    }
}