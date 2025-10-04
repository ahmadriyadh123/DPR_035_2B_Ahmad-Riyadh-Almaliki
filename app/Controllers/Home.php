<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Portal Data DPR - Akses Publik'
        ];
        return view('public/homepage', $data);
    }
}