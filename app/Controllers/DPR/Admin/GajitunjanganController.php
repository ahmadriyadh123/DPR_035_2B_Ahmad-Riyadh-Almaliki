<?php
namespace App\Controllers\DPR\Admin;
use App\Controllers\BaseController;

class GajiTunjanganController extends BaseController
{
    public function spaShell()
    {
        $data = ['title' => 'Kelola Gaji Tunjangan'];
        return view('admin/gaji_tunjangan/index', $data);
    }
}