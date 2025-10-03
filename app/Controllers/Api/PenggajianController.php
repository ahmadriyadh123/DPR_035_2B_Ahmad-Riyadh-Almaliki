<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DPR\AnggotaModel;
use App\Models\DPR\KomponenGajiModel;
use App\Models\DPR\PenggajianModel;

class PenggajianController extends ResourceController
{
    /**
     * Mengambil daftar penggajian semua anggota dengan pagination.
     * Method: GET
     * URL: /api/penggajian
     */
    public function index()
    {
        $anggotaModel = new AnggotaModel();
        $komponenGajiModel = new KomponenGajiModel();
        
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;
        
        // Ambil daftar anggota dengan pagination
        $anggotaList = $anggotaModel->paginate($perPage, 'default', $page);
        $penggajianData = [];
        
        foreach ($anggotaList as $anggota) {
            // Ambil komponen gaji berdasarkan jabatan anggota
            $komponenGaji = $komponenGajiModel->where('jabatan', $anggota->jabatan)->findAll();
            
            $penggajianData[] = [
                'id_anggota' => $anggota->id_anggota,
                'nama_anggota' => $anggota->nama_depan . ' ' . $anggota->nama_belakang,
                'jabatan' => $anggota->jabatan,
                'komponen_gaji' => $komponenGaji
            ];
        }
        
        $data = [
            'penggajian' => $penggajianData,
            'pager' => $anggotaModel->pager
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil detail penggajian anggota berdasarkan ID.
     * Method: GET
     * URL: /api/penggajian/{id}
     */
    public function show($id = null)
    {
        $penggajianModel = new PenggajianModel();
        
        $data = $penggajianModel->getPenggajianDetail($id);

        if (!$data) {
            return $this->failNotFound('Data penggajian untuk anggota ini tidak ditemukan.');
        }
        
        return $this->respond($data);
    }

    /**
     * Menghitung total gaji anggota berdasarkan jabatan.
     * Method: GET
     * URL: /api/penggajian/calculate/{id}
     */
    public function calculate($id = null)
    {
        $anggotaModel = new AnggotaModel();
        $komponenGajiModel = new KomponenGajiModel();
        
        // Ambil data anggota
        $anggota = $anggotaModel->find($id);
        if (!$anggota) {
            return $this->failNotFound('Anggota tidak ditemukan.');
        }
        
        // Ambil komponen gaji berdasarkan jabatan anggota
        $komponenGaji = $komponenGajiModel->where('jabatan', $anggota->jabatan)->findAll();
        
        $totalGaji = 0;
        $rincianGaji = [];
        
        foreach ($komponenGaji as $komponen) {
            $nominal = (float)$komponen->nominal;
            $totalGaji += $nominal;
            
            $rincianGaji[] = [
                'nama_komponen' => $komponen->nama_komponen,
                'kategori' => $komponen->kategori,
                'nominal' => $nominal,
                'satuan' => $komponen->satuan
            ];
        }
        
        $data = [
            'anggota' => [
                'id_anggota' => $anggota->id_anggota,
                'nama' => $anggota->nama_depan . ' ' . $anggota->nama_belakang,
                'jabatan' => $anggota->jabatan
            ],
            'rincian_gaji' => $rincianGaji,
            'total_gaji' => $totalGaji,
            'jumlah_komponen' => count($rincianGaji)
        ];
        
        return $this->respond($data);
    }

    /**
     * Mengambil ringkasan penggajian untuk semua anggota.
     */
    public function summary()
    {
        $anggotaModel = new AnggotaModel();
        $penggajianModel = new PenggajianModel();

        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;

        // Mengambil semua anggota yang memiliki data di tabel penggajian
        $builder = $penggajianModel->distinct()->select('id_anggota');
        $anggotaIds = $builder->findColumn('id_anggota') ?? [];

        $anggotaList = [];
        if (!empty($anggotaIds)) {
            $anggotaList = $anggotaModel->whereIn('id', $anggotaIds)->paginate($perPage, 'default', $page);
        }
        
        $summaryData = [];

        foreach ($anggotaList as $anggota) {
            $takeHomePay = $penggajianModel->calculateTakeHomePay($anggota->id);
            
            $summaryData[] = [
                'id_anggota' => $anggota->id,
                'nama_anggota' => trim(implode(' ', array_filter([$anggota->gelar_depan, $anggota->nama_depan, $anggota->nama_belakang, $anggota->gelar_belakang]))),
                'jabatan' => $anggota->jabatan,
                'take_home_pay' => $takeHomePay,
            ];
        }

        $pager = $anggotaModel->pager ? $anggotaModel->pager->getDetails() : [
            'currentPage' => 1,
            'pageCount' => 1
        ];

        return $this->respond([
            'penggajian' => $summaryData,
            'pager' => $pager,
            'csrf_hash' => csrf_hash() // Selalu kirim CSRF hash baru
        ]);
    }

    /**
     * Membuat data penggajian baru untuk seorang anggota.
     * Method: POST
     * URL: /api/penggajian
     */
    public function create()
    {
        $model = new PenggajianModel();
        $data = $this->request->getJSON(true);

        // Selalu perbarui CSRF hash di setiap response
        $csrf_hash = csrf_hash();

        $id_anggota = $data['id_anggota'] ?? null;
        $id_komponen = $data['id_komponen'] ?? [];

        // Validasi input dasar
        if (empty($id_anggota) || empty($id_komponen)) {
            return $this->fail('ID Anggota dan minimal satu komponen gaji harus dipilih.', 400);
        }

        try {
            // Panggil metode di model untuk menyimpan data
            $model->assignKomponenToAnggota($id_anggota, $id_komponen);
            
            return $this->respondCreated([
                'message' => 'Data penggajian berhasil disimpan.',
                'csrf_hash' => $csrf_hash
            ]);

        } catch (\Exception $e) {
            // Tangani error, termasuk dari validasi di model
            return $this->fail([
                'error' => $e->getMessage(),
                'csrf_hash' => $csrf_hash
            ], 400);
        }
    }
}