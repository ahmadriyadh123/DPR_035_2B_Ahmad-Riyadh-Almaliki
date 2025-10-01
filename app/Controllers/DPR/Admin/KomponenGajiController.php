<?php
namespace App\Controllers\DPR\Admin;
use App\Controllers\BaseController;

class KomponenGajiController extends BaseController
{
    public function spaShell()
    {
        $data = ['title' => 'Kelola Komponen Gaji'];
        return view('admin/komponen_gaji/index', $data);
    }
}