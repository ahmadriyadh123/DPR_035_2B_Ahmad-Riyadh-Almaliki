<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DPR\KomponenGajiModel;

class KomponenGajiController extends ResourceController
{
    /**
     * Mengambil daftar semua komponen gaji dengan pagination dan search.
     * Method: GET
     * URL: /api/komponen_gaji
     */
    public function index()
    {
        $komponenGajiModel = new KomponenGajiModel();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $perPage = 10;
        
        // Jika ada parameter search, lakukan filtering
        if (!empty($search)) {
            $komponenGajiModel->groupStart()
                ->like('CAST(id_komponen_gaji AS TEXT)', $search, 'both', null, true)
                ->orLike('nama_komponen', $search, 'both', null, true)
                ->orLike('CAST(kategori AS TEXT)', $search, 'both', null, true)
                ->orLike('CAST(jabatan AS TEXT)', $search, 'both', null, true)
                ->orLike('CAST(nominal AS TEXT)', $search, 'both', null, true)
                ->orLike('CAST(satuan AS TEXT)', $search, 'both', null, true)
                ->groupEnd();
        }
        
        $data = [
            'komponen_gaji' => $komponenGajiModel->paginate($perPage, 'default', $page),
            'pager' => $komponenGajiModel->pager->getDetails()
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil detail komponen gaji berdasarkan ID.
     * Method: GET
     * URL: /api/komponen_gaji/{id}
     */
    public function show($id = null)
    {
        $komponenGajiModel = new KomponenGajiModel();
        $komponen = $komponenGajiModel->find($id);
        
        if (!$komponen) {
            return $this->failNotFound('Komponen gaji tidak ditemukan.');
        }
        
        return $this->respond((array)$komponen);
    }

    /**
     * Membuat komponen gaji baru.
     * Method: POST
     * URL: /api/komponen_gaji
     */
    public function create()
    {
        $komponenGajiModel = new KomponenGajiModel();
        $json = $this->request->getJSON(true);
        
        $data = [
            'nama_komponen' => $json['nama_komponen'] ?? '',
            'kategori' => $json['kategori'] ?? '',
            'jabatan' => $json['jabatan'] ?? '',
            'nominal' => $json['nominal'] ?? 0,
            'satuan' => $json['satuan'] ?? ''
        ];

        // Validasi sederhana
        if (empty($data['nama_komponen']) || empty($data['kategori'])) {
            return $this->fail('Nama komponen dan kategori harus diisi.');
        }

        try {
            $komponenGajiModel->insert($data);
            return $this->respondCreated(['message' => 'Komponen gaji berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal menambahkan komponen gaji: ' . $e->getMessage());
        }
    }

    /**
     * Mengupdate komponen gaji berdasarkan ID.
     * Method: PUT/PATCH
     * URL: /api/komponen_gaji/{id}
     */
    public function update($id = null)
    {
        $komponenGajiModel = new KomponenGajiModel();
        $json = $this->request->getJSON(true);
        
        if (!$komponenGajiModel->find($id)) {
            return $this->failNotFound('Komponen gaji tidak ditemukan.');
        }
        
        $data = [
            'nama_komponen' => $json['nama_komponen'] ?? '',
            'kategori' => $json['kategori'] ?? '',
            'jabatan' => $json['jabatan'] ?? '',
            'nominal' => $json['nominal'] ?? 0,
            'satuan' => $json['satuan'] ?? ''
        ];

        // Validasi sederhana
        if (empty($data['nama_komponen']) || empty($data['kategori'])) {
            return $this->fail('Nama komponen dan kategori harus diisi.');
        }

        try {
            $komponenGajiModel->update($id, $data);
            return $this->respond(['message' => 'Komponen gaji berhasil diupdate.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal mengupdate komponen gaji: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil komponen gaji berdasarkan jabatan.
     * Method: GET
     * URL: /api/komponengaji/by-jabatan/{jabatan}
     */
    public function getByJabatan($jabatan = null)
    {
        $komponenGajiModel = new KomponenGajiModel();
        $response_data = ['csrf_hash' => csrf_hash()];

        if (empty($jabatan)) {
            $response_data['error'] = 'Jabatan harus disediakan.';
            return $this->respond($response_data, 400);
        }

        try {
            $jabatan = urldecode($jabatan);
            
            // Ambil komponen gaji berdasarkan jabatan (termasuk "Semua")
            $komponen = $komponenGajiModel
                ->groupStart()
                    ->where('jabatan', $jabatan)
                    ->orWhere('jabatan', 'Semua')
                ->groupEnd()
                ->orderBy('kategori', 'ASC')
                ->orderBy('nama_komponen', 'ASC')
                ->findAll();
            
            $response_data['komponen'] = $komponen;
            return $this->respond($response_data, 200);

        } catch (\Exception $e) {
            $response_data['error'] = 'Gagal mengambil komponen gaji: ' . $e->getMessage();
            return $this->respond($response_data, 500);
        }
    }

    /**
     * Menghapus komponen gaji berdasarkan ID.
     * Method: DELETE
     * URL: /api/komponen_gaji/{id}
     */
    public function delete($id = null)
    {
        log_message('info', 'KomponenGajiController::delete called with ID: ' . ($id ?? 'null'));
        
        $komponenGajiModel = new KomponenGajiModel();
        
        // Validate ID parameter
        if ($id === null || !is_numeric($id)) {
            log_message('error', 'Invalid ID parameter in delete: ' . ($id ?? 'null'));
            return $this->fail('ID komponen gaji tidak valid.', 400);
        }
        
        // Check if komponen exists
        $komponen = $komponenGajiModel->find($id);
        if (!$komponen) {
            log_message('error', 'Komponen gaji not found with ID: ' . $id);
            return $this->failNotFound('Komponen gaji tidak ditemukan.');
        }
        
        // Check if komponen is still referenced in penggajian table
        $penggajianModel = new \App\Models\DPR\PenggajianModel();
        $referencesCount = $penggajianModel->where('id_komponen_gaji', $id)->countAllResults();
        
        if ($referencesCount > 0) {
            log_message('warning', "Cannot delete komponen gaji ID {$id}: still referenced by {$referencesCount} penggajian records");
            return $this->fail(
                "Komponen gaji tidak dapat dihapus karena masih digunakan dalam {$referencesCount} data penggajian. " .
                "Hapus terlebih dahulu data penggajian yang menggunakan komponen ini.",
                409 // Conflict status code
            );
        }
        
        try {
            log_message('info', 'Attempting to delete komponen gaji with ID: ' . $id);
            $result = $komponenGajiModel->delete($id);
            
            if ($result) {
                log_message('info', 'Successfully deleted komponen gaji with ID: ' . $id);
                return $this->respondDeleted(['message' => 'Komponen gaji berhasil dihapus.']);
            } else {
                log_message('error', 'Failed to delete komponen gaji with ID: ' . $id);
                return $this->fail('Gagal menghapus komponen gaji.', 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in delete komponen gaji: ' . $e->getMessage());
            return $this->fail('Gagal menghapus komponen gaji: ' . $e->getMessage(), 500);
        }
    }
}