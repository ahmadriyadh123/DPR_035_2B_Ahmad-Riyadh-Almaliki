<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
// Ganti 'NamaModel' dengan nama Model Anda (misal: DosenModel)
use App\Models\NamaModel;

class GenericApiController extends ResourceController
{
    /**
     * Mengambil daftar semua resource dengan pagination.
     * Method: GET
     * URL: /api/resource
     */
    public function index()
    {
        // Ganti 'NamaModel' sesuai kebutuhan
        $model = new NamaModel();   
        
        $data = [
            // Ganti 'resources' dengan nama yang sesuai (misal: 'dosens')
            'resources' => $model->paginate(10), // Mengambil 10 data per halaman
            'pager'     => $model->pager->getDetails(),
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil detail satu resource.
     * Method: GET
     * URL: /api/resource/{id}
     */
    public function show($id = null)
    {
        $model = new NamaModel();
        $data = $model->find($id);

        if (!$data) {
            return $this->failNotFound('Data tidak ditemukan.');
        }

        return $this->respond($data);
    }

    /**
     * Menyimpan resource baru. Menerima data dalam format JSON.
     * Method: POST
     * URL: /api/resource
     */
    public function create()
    {
        $model = new NamaModel();
        $json = $this->request->getJSON();

        // Siapkan data dari JSON
        $data = [
            // Ganti 'field1' dan 'field2' dengan nama kolom di tabel Anda
            'field1' => $json->field1,
            'field2' => $json->field2,
        ];

        // Validasi sederhana
        if (empty($data['field1']) || empty($data['field2'])) {
             return $this->fail('Semua field harus diisi.', 400); // 400 = Bad Request
        }

        if ($model->insert($data)) {
            $response = [
                'status'   => 201, // 201 = Created
                'error'    => null,
                'messages' => [
                    'success' => 'Data berhasil ditambahkan.'
                ]
            ];
            return $this->respondCreated($response);
        }

        return $this->fail('Gagal menyimpan data.');
    }

    /**
     * Memperbarui resource yang ada. Menerima data dalam format JSON.
     * Method: PUT
     * URL: /api/resource/{id}
     */
    public function update($id = null)
    {
        $model = new NamaModel();
        $json = $this->request->getJSON();

        // Cek apakah data ada
        if (!$model->find($id)) {
            return $this->failNotFound('Data tidak ditemukan.');
        }

        $data = [
            'field1' => $json->field1,
            'field2' => $json->field2,
        ];
        
        $model->update($id, $data);

        $response = [
            'status'   => 200, // 200 = OK
            'error'    => null,
            'messages' => [
                'success' => 'Data berhasil diperbarui.'
            ]
        ];
        return $this->respond($response);
    }

    /**
     * Menghapus resource yang ada.
     * Method: DELETE
     * URL: /api/resource/{id}
     */
    public function delete($id = null)
    {
        $model = new NamaModel();
        
        if (!$model->find($id)) {
            return $this->failNotFound('Data tidak ditemukan.');
        }

        $model->delete($id);

        $response = [
            'status'   => 200,
            'error'    => null,
            'messages' => [
                'success' => 'Data berhasil dihapus.'
            ]
        ];
        return $this->respondDeleted($response);
    }
}