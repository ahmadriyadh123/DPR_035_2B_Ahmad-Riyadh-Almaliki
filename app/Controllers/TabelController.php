<?php

namespace App\Controllers;

class TabelController extends BaseController
{
    public function index()
    {
        $data_mahasiswa = [
            [
                'npm' => '2021001',
                'nama' => 'Budi Santoso',
                'jurusan' => 'Teknik Informatika'
            ],
            [
                'npm' => '2021002',
                'nama' => 'Ani Lestari',
                'jurusan' => 'Sistem Informasi'
            ],
            [
                'npm' => '2021003',
                'nama' => 'Candra Wijaya',
                'jurusan' => 'Ilmu Komputer'
            ],
            [
                'npm' => '2021004',
                'nama' => 'Dewi Anggraini',
                'jurusan' => 'Teknik Informatika'
            ]
        ];

        $data = [
            'title' => 'Daftar Mahasiswa Dinamis',
            'mahasiswa' => $data_mahasiswa
        ];

        return view('tabel', $data);
    }
}