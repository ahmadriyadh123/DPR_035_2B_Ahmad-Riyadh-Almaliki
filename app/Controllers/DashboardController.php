<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard'
        ];
        
        // Pastikan view yang dipanggil adalah 'dashboard_view'
        return view('dashboard_view', $data);
    }
}