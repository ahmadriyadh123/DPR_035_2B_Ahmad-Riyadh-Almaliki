<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class DosenController extends BaseController
{
    public function spaShell()
    {
        $data = ['title' => 'Kelola Dosen'];
        return view('admin/dosen/index', $data);
    }
}