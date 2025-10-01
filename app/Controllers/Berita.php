<?php

namespace App\Controllers;

class Berita extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Berita',
            'content' => view('berita')
        ];
        return view('template', $data);
    }
}