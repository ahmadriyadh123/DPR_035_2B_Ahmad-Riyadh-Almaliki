<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\StudentModel; // Tambahkan ini
use App\Models\TakeModel;    // Ubah dari EnrollmentModel

class CourseController extends BaseController
{
    public function index()
    {
        $data = [
            'title'   => 'Ambil Mata Kuliah',
        ];

        return view('courses/index', $data);
    }
}