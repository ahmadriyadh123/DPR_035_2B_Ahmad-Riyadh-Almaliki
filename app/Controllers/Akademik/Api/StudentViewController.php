<?php

namespace App\Controllers\Akademik\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Akademik\CourseModel;
use App\Models\Akademik\StudentModel;
use App\Models\Akademik\TakeModel;

/**
 * Kelas StudentViewController mengelola API untuk tampilan mahasiswa,
 * termasuk pengambilan data mata kuliah yang diambil dan tersedia,
 * serta pendaftaran batch ke mata kuliah.
 */
class StudentViewController extends ResourceController
{
    /**
     * Mengambil daftar mata kuliah yang tersedia untuk mahasiswa yang sedang login.
     */
    public function getCoursesData()
    {
        $session = session();
        $courseModel = new CourseModel();
        $studentModel = new StudentModel();
        $takeModel = new TakeModel();

        $userId = $session->get('user_id');
        $studentId = $studentModel->getStudentIdByUserId($userId);

        if (!$studentId) {
            return $this->respond(['enrolled' => [], 'available' => []]);
        }

        // 1. Ambil mata kuliah yang sudah diambil
        $enrolledCourses = $takeModel->getEnrolledCourses($studentId);

        // 2. Ambil ID dari mata kuliah yang sudah diambil
        $enrolledCourseIds = array_column($enrolledCourses, 'id'); // Perlu modifikasi di TakeModel
        
        // 3. Ambil mata kuliah yang tersedia
        $builder = $courseModel->builder();
        if (!empty($enrolledCourseIds)) {
            $builder->whereNotIn('id', $enrolledCourseIds);
        }
        $availableCourses = $builder->get()->getResult();

        // 4. Kirim kedua set data
        return $this->respond([
            'enrolled'  => $enrolledCourses,
            'available' => $availableCourses,
        ]);
    }

    /**
     * Mendaftarkan mahasiswa yang login ke beberapa mata kuliah sekaligus.
     */
    public function enrollBatch()
    {
        $session = session();
        $studentModel = new StudentModel();
        $takeModel = new TakeModel();
        
        $userId = $session->get('user_id');
        $studentId = $studentModel->getStudentIdByUserId($userId);
        
        $selectedCourses = $this->request->getJSON()->course_ids;

        if (!$studentId || empty($selectedCourses)) {
            return $this->fail('Tidak ada mata kuliah yang dipilih atau data mahasiswa tidak ditemukan.', 400);
        }

        $dataToInsert = [];
        foreach ($selectedCourses as $courseId) {
            $dataToInsert[] = [
                'student_id'  => $studentId,
                'course_id'   => (int) $courseId,
                'enroll_date' => date('Y-m-d'),
            ];
        }

        $takeModel->insertBatch($dataToInsert);

        return $this->respondCreated(['message' => 'Mata kuliah berhasil didaftarkan!']);
    }
}