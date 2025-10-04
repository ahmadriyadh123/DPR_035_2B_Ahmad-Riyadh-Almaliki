<?php
namespace App\Controllers\DPR\Admin;
use App\Controllers\BaseController;

class AnggotaController extends BaseController
{
    public function spaShell()
    {
        $data = ['title' => 'Kelola Anggota'];
        return view('DPR/admin/anggota/index', $data);
    }
}