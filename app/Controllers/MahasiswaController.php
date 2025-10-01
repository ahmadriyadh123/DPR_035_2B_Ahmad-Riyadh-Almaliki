<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;

class MahasiswaController extends BaseController
{
    public function index()
    {
        $model = new MahasiswaModel();     
        
        $keyword = $this->request->getGet('keyword');
        
        if ($keyword) {
            $data_mahasiswa = $model->search($keyword);
        } else {
            
            $data_mahasiswa = $model->findAll(); 
        }

        $data = [
            'title'     => 'Daftar Mahasiswa',
            'mahasiswa' => $data_mahasiswa,
            'keyword'   => $keyword,
        ];

        $template_data = [
            'title'   => 'Daftar Mahasiswa',
            'content' => view('mahasiswa_view', $data),
        ];

        return view('template', $template_data);
    }
    public function detail($id)
    {
        $model = new MahasiswaModel();
        $mahasiswa = $model->find($id);

        if (empty($mahasiswa)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Mahasiswa dengan ID ' . $id . ' tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Mahasiswa',
            'mahasiswa' => $mahasiswa
        ];

        $template_data = [
            'title'   => 'Detail Mahasiswa',
            'content' => view('detail_mahasiswa_view', $data),
        ];

        return view('template', $template_data);
    }
    public function create()
    {
        $data = [
            'title' => 'Tambah Data Mahasiswa',
        ];

        $template_data = [
            'title'   => 'Tambah Data Mahasiswa',
            'content' => view('create_mahasiswa_view', $data),
        ];

        return view('template', $template_data);
    }
    public function store()
    {
        
        $nim = $this->request->getPost('nim');
        $nama = $this->request->getPost('nama');
        $umur = $this->request->getPost('umur');
     
        $data = [
            'nim' => $nim,
            'nama' => $nama,
            'umur' => $umur,
        ];

        $model = new MahasiswaModel();
        $model->insert($data);
        
        return redirect()->to('/mahasiswa');
    }
    public function delete($id)
    {
        $model = new MahasiswaModel();
        
        $model->delete($id);

        return redirect()->to('/mahasiswa');
    }
    public function edit($id)
    {
        $model = new MahasiswaModel();
        $mahasiswa = $model->find($id);

        if (empty($mahasiswa)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Mahasiswa dengan ID ' . $id . ' tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Data Mahasiswa',
            'mahasiswa' => $mahasiswa,
        ];

        $template_data = [
            'title'   => 'Edit Data Mahasiswa',
            'content' => view('edit_mahasiswa_view', $data),
        ];

        return view('template', $template_data);
    }

    public function update($id)
    {
        $nim = $this->request->getPost('nim');
        $nama = $this->request->getPost('nama');
        $umur = $this->request->getPost('umur');

        $data = [
            'nim' => $nim,
            'nama' => $nama,
            'umur' => $umur,
        ];
            
        $model = new MahasiswaModel();
        $model->update($id, $data); 

        return redirect()->to('/mahasiswa');
    }
}