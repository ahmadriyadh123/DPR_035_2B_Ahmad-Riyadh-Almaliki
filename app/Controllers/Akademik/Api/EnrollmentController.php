<?php

namespace App\Controllers\Akademik\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Akademik\TakeModel;
use App\Models\Akademik\StudentModel;

class EnrollmentController extends ResourceController
{
    /**
     * Mendaftarkan seorang mahasiswa ke sebuah mata kuliah.
     */
    public function create()
    {
        $json = $this->request->getJSON();
        $studentModel = new StudentModel();
        $takeModel = new TakeModel();

        // Dapatkan student_id dari user_id yang dikirim
        $studentId = $studentModel->getStudentIdByUserId($json->user_id);

        if (!$studentId) {
            return $this->failNotFound('Data mahasiswa tidak ditemukan.');
        }

        $data = [
            'student_id'  => $studentId,
            'course_id'   => $json->course_id,
            'enroll_date' => date('Y-m-d'),
        ];

        if ($takeModel->insert($data)) {
            return $this->respondCreated(['message' => 'Mata kuliah berhasil ditambahkan.']);
        }
        
        return $this->fail('Gagal menambahkan mata kuliah.');
    }

    /**
     * Menghapus pendaftaran mata kuliah seorang mahasiswa.
     * $id di sini adalah ID dari tabel 'takes'.
     */
    public function delete($id = null)
    {
        $takeModel = new TakeModel();
        
        if ($takeModel->find($id) === null) {
            return $this->failNotFound('Data pendaftaran tidak ditemukan.');
        }

        $takeModel->delete($id);

        return $this->respondDeleted(['message' => 'Mata kuliah berhasil dihapus dari mahasiswa.']);
    }
}