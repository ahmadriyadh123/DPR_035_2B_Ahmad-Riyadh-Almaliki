<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DPR\AnggotaModel;

class AnggotaController extends ResourceController
{
    /**
     * Mengambil daftar semua anggota dengan pagination dan search.
     * Method: GET
     * URL: /api/anggota
     */
    public function index()
    {
        $anggotaModel = new AnggotaModel();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $perPage = 10;
        
        // Jika ada parameter search, lakukan filtering
        if (!empty($search)) {
            $anggotaModel->groupStart()
                ->like('CAST(id_anggota AS TEXT)', $search, 'both', null, true)
                ->orLike('nama_depan', $search, 'both', null, true)
                ->orLike('nama_belakang', $search, 'both', null, true)
                ->orLike('gelar_depan', $search, 'both', null, true)
                ->orLike('gelar_belakang', $search, 'both', null, true)
                ->orLike('CAST(jabatan AS TEXT)', $search, 'both', null, true)
                ->orLike('CAST(status_pernikahan AS TEXT)', $search, 'both', null, true)
                ->groupEnd();
        }
        
        $data = [
            'anggota' => $anggotaModel->paginate($perPage, 'default', $page),
            'pager' => $anggotaModel->pager->getDetails()
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
        
        return $this->respond((array)$anggota);
    }

    /**
     * Membuat anggota baru.
     * Method: POST
     * URL: /api/anggota
     */
    public function create()
    {
        $anggotaModel = new AnggotaModel();
        $input = $this->request->getBody();
        $json = json_decode($input, true);
        
        if (!$json) {
            return $this->fail('Invalid JSON data', 400);
        }
        
        $data = [
            'nama_depan' => $json['nama_depan'] ?? '',
            'nama_belakang' => $json['nama_belakang'] ?? '',
            'gelar_depan' => $json['gelar_depan'] ?? '',
            'gelar_belakang' => $json['gelar_belakang'] ?? '',
            'jabatan' => $json['jabatan'] ?? '',
            'status_pernikahan' => $json['status_pernikahan'] ?? 'Belum Kawin'
        ];

        // Validasi sederhana
        if (empty($data['nama_depan']) || empty($data['nama_belakang'])) {
            return $this->fail('Nama depan dan nama belakang harus diisi.', 400);
        }

        try {
            $anggotaModel->insert($data);
            return $this->respondCreated(['message' => 'Anggota berhasil ditambahkan.']);
        } catch (\Exception $e) {
            log_message('error', 'Create anggota error: ' . $e->getMessage());
            return $this->fail('Gagal menambahkan anggota: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mengupdate anggota berdasarkan ID.
     * Method: PUT/PATCH
     * URL: /api/anggota/{id}
     */
    public function update($id = null)
    {
        log_message('info', 'Update anggota called with ID: ' . $id);
        
        $anggotaModel = new AnggotaModel();
        $input = $this->request->getBody();
        
        log_message('info', 'Raw input: ' . $input);
        
        $json = json_decode($input, true);
        
        if (!$json) {
            log_message('error', 'Invalid JSON data received');
            return $this->fail('Invalid JSON data', 400);
        }
        
        log_message('info', 'Decoded JSON: ' . print_r($json, true));
        
        if (!$anggotaModel->find($id)) {
            return $this->failNotFound('Anggota tidak ditemukan.');
        }
        
        $data = [
            'nama_depan' => $json['nama_depan'] ?? '',
            'nama_belakang' => $json['nama_belakang'] ?? '',
            'gelar_depan' => $json['gelar_depan'] ?? '',
            'gelar_belakang' => $json['gelar_belakang'] ?? '',
            'jabatan' => $json['jabatan'] ?? '',
            'status_pernikahan' => $json['status_pernikahan'] ?? 'Belum Kawin'
        ];

        log_message('info', 'Data to update: ' . print_r($data, true));

        // Validasi sederhana
        if (empty($data['nama_depan']) || empty($data['nama_belakang'])) {
            return $this->fail('Nama depan dan nama belakang harus diisi.', 400);
        }

        try {
            $anggotaModel->update($id, $data);
            log_message('info', 'Anggota updated successfully');
            return $this->respond(['message' => 'Anggota berhasil diupdate.']);
        } catch (\Exception $e) {
            log_message('error', 'Update anggota error: ' . $e->getMessage());
            return $this->fail('Gagal mengupdate anggota: ' . $e->getMessage(), 500);
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