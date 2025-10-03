<?php
namespace App\Controllers\DPR;
use App\Controllers\BaseController;

class PenggajianController extends BaseController
{
    public function spaShell()
    {
        $data = ['title' => 'Kelola Penggajian'];
        return view('DPR/penggajian/index', $data);
    }
}