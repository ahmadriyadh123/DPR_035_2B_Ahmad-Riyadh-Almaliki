<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseController;
use App\Models\Akademik\CourseModel;
use App\Models\Akademik\StudentModel;
use App\Models\Akademik\TakeModel;   

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