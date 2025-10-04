<?php

namespace App\Controllers\Akademik\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Akademik\CourseModel;

class CourseController extends ResourceController
{
    /**
     * Mengambil daftar semua mata kuliah dengan pagination.
     */
    public function index()
    {
        $model = new CourseModel();
        $data = [
            'courses' => $model->paginate(10), // Ambil 10 data per halaman
            'pager'   => $model->pager->getDetails(),
        ];
        return $this->respond($data);
    }

    /**
     * Mengambil detail satu mata kuliah.
     */
    public function show($id = null)
    {
        $model = new CourseModel();
        $course = $model->find($id);
        if (!$course) {
            return $this->failNotFound('Mata kuliah tidak ditemukan.');
        }
        return $this->respond($course);
    }

    /**
     * Menyimpan mata kuliah baru.
     */
    public function create()
    {
        $model = new CourseModel();
        $json = $this->request->getJSON();

        $data = [
            'course_name' => $json->course_name,
            'credits'     => $json->credits,
        ];

        if ($model->insert($data)) {
            return $this->respondCreated(['message' => 'Mata kuliah berhasil ditambahkan.']);
        }
        
        return $this->fail('Gagal menyimpan mata kuliah.');
    }
    public function availableForStudent($userId = null)
    {
        $courseModel = new \App\Models\CourseModel();
        $studentModel = new \App\Models\StudentModel();
        $takeModel = new \App\Models\TakeModel();

        $studentId = $studentModel->getStudentIdByUserId($userId);
        if (!$studentId) {
            return $this->respond([]); // Kembalikan array kosong jika mahasiswa tidak ditemukan
        }

        // Ambil ID semua mata kuliah yang sudah diambil
        $enrolledCourseIds = $takeModel->getEnrolledCourseIds($studentId);
        
        $builder = $courseModel->builder();
        
        if (!empty($enrolledCourseIds)) {
            $builder->whereNotIn('id', $enrolledCourseIds);
        }

        $availableCourses = $builder->get()->getResult();

        return $this->respond($availableCourses);
    }

    /**
     * Memperbarui mata kuliah yang ada.
     */
    public function update($id = null)
    {
        $model = new CourseModel();
        $json = $this->request->getJSON();

        $data = [
            'course_name' => $json->course_name,
            'credits'     => $json->credits,
        ];
        
        $model->update($id, $data);
        return $this->respondUpdated(['message' => 'Mata kuliah berhasil diperbarui.']);
    }

    /**
     * Menghapus mata kuliah.
     */
    public function delete($id = null)
    {
        $model = new CourseModel();
        $course = $model->find($id);
        if (!$course) {
            return $this->failNotFound('Mata kuliah tidak ditemukan.');
        }
        
        $model->delete($id);
        return $this->respondDeleted(['message' => 'Mata kuliah berhasil dihapus.']);
    }
}