<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
// Ganti 'NamaModel' dengan nama Model Anda (misal: DosenModel)
use App\Models\NamaModel;

class GenericAdminController extends BaseController
{
    /**
     * Menampilkan halaman daftar semua resource.
     * Method: GET
     * URL: /admin/resource
     */
    public function index()
    {
        // Ganti 'NamaModel' sesuai kebutuhan
        $model = new NamaModel();

        $data = [
            'title' => 'Kelola Data Resource', // Ganti judul
            // Ganti 'resources' dengan nama yang sesuai (misal: 'dosens')
            'resources' => $model->findAll(),
        ];

        // Ganti 'admin/resource/index' sesuai path view Anda
        return view('admin/resource/index', $data);
    }

    /**
     * Menampilkan form untuk menambah data baru.
     * Method: GET
     * URL: /admin/resource/create
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Data Resource Baru', // Ganti judul
        ];

        // Ganti 'admin/resource/create' sesuai path view Anda
        return view('admin/resource/create', $data);
    }

    /**
     * Menyimpan data baru yang dikirim dari form 'create'.
     * Method: POST
     * URL: /admin/resource/store
     */
    public function store()
    {
        $model = new NamaModel();

        // Ambil semua data dari form post
        $data = [
            // Ganti 'field1' dan 'field2' dengan nama kolom di tabel Anda
            'field1' => $this->request->getPost('field1'),
            'field2' => $this->request->getPost('field2'),
        ];

        // Simpan data ke database
        $model->save($data);

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->to('/admin/resource')->with('message', 'Data berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit data yang sudah ada.
     * Method: GET
     * URL: /admin/resource/edit/{id}
     */
    public function edit($id)
    {
        $model = new NamaModel();

        $data = [
            'title'    => 'Edit Data Resource', // Ganti judul
            // Ganti 'resource' dengan nama yang sesuai (misal: 'dosen')
            'resource' => $model->find($id),
        ];
        
        // Handle jika data dengan ID tersebut tidak ditemukan
        if (empty($data['resource'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data tidak ditemukan: ' . $id);
        }

        // Ganti 'admin/resource/edit' sesuai path view Anda
        return view('admin/resource/edit', $data);
    }

    /**
     * Memperbarui data di database dari form 'edit'.
     * Method: POST
     * URL: /admin/resource/update/{id}
     */
    public function update($id)
    {
        $model = new NamaModel();

        // Ambil semua data dari form post
        $data = [
            'field1' => $this->request->getPost('field1'),
            'field2' => $this->request->getPost('field2'),
        ];

        // Update data di database berdasarkan ID
        $model->update($id, $data);

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->to('/admin/resource')->with('message', 'Data berhasil diperbarui.');
    }

    /**
     * Menghapus data dari database.
     * Method: GET
     * URL: /admin/resource/delete/{id}
     */
    public function delete($id)
    {
        $model = new NamaModel();

        // Hapus data dari database berdasarkan ID
        $model->delete($id);

        // Arahkan kembali ke halaman daftar dengan pesan sukses
        return redirect()->to('/admin/resource')->with('message', 'Data berhasil dihapus.');
    }
    
    /**
     * Method ini hanya untuk memuat "wadah" SPA jika Anda menggunakannya.
     * Jika ujian hanya tentang CRUD tradisional, Anda bisa mengabaikan method ini.
     */
    public function spaShell()
    {
        $data = [
            'title' => 'Kelola Data Resource (SPA)'
        ];
        return view('admin/resource/index', $data);
    }
}