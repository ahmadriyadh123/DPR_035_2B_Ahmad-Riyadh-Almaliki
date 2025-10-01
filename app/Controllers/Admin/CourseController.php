<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CourseModel;

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