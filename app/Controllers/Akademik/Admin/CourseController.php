<?php

namespace App\Controllers\Akademik\Admin;

use App\Controllers\BaseController;
use App\Models\Akademik\CourseModel;

class CourseController extends BaseController
{
    // Menampilkan daftar semua mata kuliah
    public function spaShell()
    {
        $data = [
            'title' => 'Kelola Mata Kuliah'
        ];
        // Selalu kembalikan view yang sama, yaitu index.php
        return view('admin/courses/index', $data);
    }
}