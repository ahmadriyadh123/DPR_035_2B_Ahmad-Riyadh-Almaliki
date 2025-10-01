<?php

namespace App\Models;

use CodeIgniter\Model;

class TakeModel extends Model
{
    protected $table            = 'takes';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['student_id', 'course_id', 'enroll_date'];
    protected $returnType       = 'object';

    /**
     * Cek apakah seorang mahasiswa sudah mengambil mata kuliah tertentu.
     */
    public function isEnrolled($studentId, $courseId)
    {
        if (!$studentId) {
            return false;
        }
        return $this->where('student_id', $studentId)
                    ->where('course_id', $courseId)
                    ->first() !== null;
    }

    /**
     * Ambil semua mata kuliah yang diambil oleh seorang mahasiswa.
     */
    public function getEnrolledCourses($studentId)
    {
        // Tambahkan courses.id
        return $this->select('courses.id, courses.course_name, courses.credits') 
                    ->join('courses', 'courses.id = takes.course_id')
                    ->where('takes.student_id', $studentId)
                    ->findAll();
    }
    public function getEnrolledCourseIds($studentId)
    {
        if (!$studentId) {
            return [];
        }

        $query = $this->where('student_id', $studentId)->findAll();

        // Mengembalikan array yang hanya berisi 'course_id'
        return array_column($query, 'course_id');
    }
}