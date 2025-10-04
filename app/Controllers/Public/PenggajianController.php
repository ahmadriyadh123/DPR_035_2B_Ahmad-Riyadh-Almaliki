<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * Public Penggajian Controller  
 * Provides read-only access to DPR payroll data for public users
 */
class PenggajianController extends BaseController
{
    /**
     * Display public view of DPR payroll data
     * 
     * @return string
     */
    public function index()
    {
        $data = [
            'title' => 'Data Penggajian DPR - Akses Publik',
            'description' => 'Informasi data penggajian anggota DPR yang dapat diakses oleh publik'
        ];
        
        return view('public/penggajian/index', $data);
    }
}