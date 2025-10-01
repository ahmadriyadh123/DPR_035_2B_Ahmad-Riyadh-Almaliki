<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DPR\AnggotaModel;

class AnggotaController extends ResourceController
{
    /**
     * Mengambil daftar semua anggota dengan pagination.
     * Method: GET
     * URL: /api/anggota
     */
    public function index()
    {
        $anggotaModel = new AnggotaModel();
        
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        
        $data = [
            'anggota' => $anggotaModel->paginate($perPage, 'default', $page),
            'pager' => $anggotaModel->pager
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil detail anggota berdasarkan ID.
     * Method: GET
     * URL: /api/anggota/{id}
     */
    public function show($id = null)
    {
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id);
        
        if (!$anggota) {
            return $this->failNotFound('Anggota tidak ditemukan.');
        }
        
        return $this->respond($anggota);
    }

    /**
     * Membuat anggota baru.
     * Method: POST
     * URL: /api/anggota
     */
    public function create()
    {
        $anggotaModel = new AnggotaModel();
        $json = $this->request->getJSON();
        
        $data = [
            'nama_depan' => $json->nama_depan ?? '',
            'nama_belakang' => $json->nama_belakang ?? '',
            'gelar_depan' => $json->gelar_depan ?? '',
            'gelar_belakang' => $json->gelar_belakang ?? '',
            'jabatan' => $json->jabatan ?? '',
            'status_pernikahan' => $json->status_pernikahan ?? ''
        ];

        // Validasi sederhana
        if (empty($data['nama_depan']) || empty($data['nama_belakang'])) {
            return $this->fail('Nama depan dan nama belakang harus diisi.');
        }

        try {
            $anggotaModel->insert($data);
            return $this->respondCreated(['message' => 'Anggota berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal menambahkan anggota: ' . $e->getMessage());
        }
    }

    /**
     * Mengupdate anggota berdasarkan ID.
     * Method: PUT/PATCH
     * URL: /api/anggota/{id}
     */
    public function update($id = null)
    {
        $anggotaModel = new AnggotaModel();
        $json = $this->request->getJSON();
        
        if (!$anggotaModel->find($id)) {
            return $this->failNotFound('Anggota tidak ditemukan.');
        }
        
        $data = [
            'nama_depan' => $json->nama_depan ?? '',
            'nama_belakang' => $json->nama_belakang ?? '',
            'gelar_depan' => $json->gelar_depan ?? '',
            'gelar_belakang' => $json->gelar_belakang ?? '',
            'jabatan' => $json->jabatan ?? '',
            'status_pernikahan' => $json->status_pernikahan ?? ''
        ];

        // Validasi sederhana
        if (empty($data['nama_depan']) || empty($data['nama_belakang'])) {
            return $this->fail('Nama depan dan nama belakang harus diisi.');
        }

        try {
            $anggotaModel->update($id, $data);
            return $this->respond(['message' => 'Anggota berhasil diupdate.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal mengupdate anggota: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus anggota berdasarkan ID.
     * Method: DELETE
     * URL: /api/anggota/{id}
     */
    public function delete($id = null)
    {
        $anggotaModel = new AnggotaModel();
        
        if (!$anggotaModel->find($id)) {
            return $this->failNotFound('Anggota tidak ditemukan.');
        }
        
        try {
            $anggotaModel->delete($id);
            return $this->respondDeleted(['message' => 'Anggota berhasil dihapus.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
}