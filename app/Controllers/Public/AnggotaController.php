<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * Public Anggota Controller
 * Provides read-only access to DPR member data for public users
 */
class AnggotaController extends BaseController
{
    /**
     * Display public view of DPR member data
     * 
     * @return string
     */
    public function index()
    {
        $data = [
            'title' => 'Data Anggota DPR - Akses Publik',
            'description' => 'Informasi data anggota DPR yang dapat diakses oleh publik'
        ];
        
        return view('public/anggota/index', $data);
    }
}