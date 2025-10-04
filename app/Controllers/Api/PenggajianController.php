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
        
        $data = $penggajianModel->getPenggajianDetail((int)$id);

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
        // Debug logging
        log_message('info', '🔄 PenggajianController::summary called');
        log_message('info', '📊 Request method: ' . $this->request->getMethod());
        log_message('info', '📊 Is AJAX: ' . ($this->request->hasHeader('X-Requested-With') ? 'yes' : 'no'));
        log_message('info', '📊 Session isLoggedIn: ' . (session()->get('isLoggedIn') ? 'yes' : 'no'));
        
        $anggotaModel = new AnggotaModel();
        $penggajianModel = new PenggajianModel();

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $perPage = 10;

        try {
            // Mengambil semua anggota yang memiliki data di tabel penggajian
            $builder = $penggajianModel->distinct()->select('id_anggota');
            $anggotaIdsWithPenggajian = $builder->findColumn('id_anggota') ?? [];

            $anggotaList = [];
            $pagerDetails = [
                'currentPage' => (int)$page,
                'pageCount'   => 0,
                'total'       => 0
            ];

            if (!empty($anggotaIdsWithPenggajian)) {
                $anggotaBuilder = $anggotaModel->whereIn('id_anggota', $anggotaIdsWithPenggajian);
                
                // Tambahkan search filter jika ada
                if (!empty($search)) {
                    $anggotaBuilder->groupStart()
                        ->like('nama_depan', $search)
                        ->orLike('nama_belakang', $search)
                        ->orLike('jabatan', $search)
                        ->orLike('id_anggota', $search)
                    ->groupEnd();
                }
                
                $anggotaList = $anggotaBuilder->paginate($perPage, 'default', $page);
                
                // Pastikan pager tidak null sebelum mengambil detail
                if ($anggotaModel->pager) {
                    $pagerDetails = $anggotaModel->pager->getDetails();
                }
            }
            
            $summaryData = [];
            foreach ($anggotaList as $anggota) {
                $takeHomePay = $penggajianModel->calculateTakeHomePay($anggota->id_anggota);
                
                $summaryData[] = [
                    'id_anggota' => $anggota->id_anggota,
                    'nama_anggota' => trim(implode(' ', array_filter([
                        $anggota->gelar_depan ?? '', 
                        $anggota->nama_depan ?? '', 
                        $anggota->nama_belakang ?? '', 
                        $anggota->gelar_belakang ?? ''
                    ]))),
                    'jabatan' => $anggota->jabatan ?? '',
                    'take_home_pay' => $takeHomePay,
                ];
            }

            // Filter berdasarkan Take Home Pay jika search berupa angka
            if (!empty($search) && is_numeric($search)) {
                $summaryData = array_filter($summaryData, function($item) use ($search) {
                    return strpos((string)$item['take_home_pay'], $search) !== false ||
                           strpos(number_format($item['take_home_pay'], 0, ',', '.'), $search) !== false;
                });
                $summaryData = array_values($summaryData); // Re-index array
            }

            return $this->respond([
                'penggajian' => $summaryData,
                'pager'      => $pagerDetails,
                'csrf_hash'  => csrf_hash()
            ]);

        } catch (\Exception $e) {
            // Log error untuk debugging
            log_message('error', 'Error in PenggajianController::summary - ' . $e->getMessage());
            
            // Return error response dengan structure yang konsisten
            return $this->respond([
                'penggajian' => [],
                'pager' => [
                    'currentPage' => 1,
                    'pageCount' => 0,
                    'total' => 0
                ],
                'csrf_hash' => csrf_hash(),
                'error' => 'Terjadi kesalahan saat memuat data penggajian'
            ], 200); // Return 200 agar frontend tidak crash
        }
    }

    /**
     * Membuat data penggajian baru untuk seorang anggota.
     * Method: POST
     * URL: /api/penggajian
     */
    public function create()
    {
        $model = new PenggajianModel();
        $input = $this->request->getBody();
        $data = json_decode($input, true) ?? [];

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

    /**
     * Mengupdate data penggajian untuk seorang anggota.
     * Method: PUT
     * URL: /api/penggajian/{id}
     */
    public function update($id = null)
    {
        $model = new PenggajianModel();
        $input = $this->request->getBody();
        $data = json_decode($input, true) ?? [];

        // Selalu perbarui CSRF hash di setiap response
        $csrf_hash = csrf_hash();

        $id_komponen = $data['id_komponen'] ?? [];

        // Validasi input dasar
        if (empty($id) || empty($id_komponen)) {
            return $this->fail('ID Anggota dan minimal satu komponen gaji harus dipilih.', 400);
        }

        try {
            // Panggil metode di model untuk mengupdate data
            $model->assignKomponenToAnggota((int)$id, $id_komponen);
            
            return $this->respond([
                'message' => 'Data penggajian berhasil diupdate.',
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

    /**
     * Mengambil anggota yang belum memiliki data penggajian.
     * Method: GET
     * URL: /api/penggajian/available-anggota
     */
    public function availableAnggota()
    {
        $anggotaModel = new AnggotaModel();
        $penggajianModel = new PenggajianModel();

        // Ambil semua ID anggota yang sudah ada di tabel penggajian
        $existingIds = $penggajianModel->distinct()->findColumn('id_anggota') ?? [];

        $builder = $anggotaModel;
        if (!empty($existingIds)) {
            $builder->whereNotIn('id_anggota', $existingIds);
        }
        
        $anggotaList = $builder->orderBy('nama_depan', 'ASC')->findAll();

        return $this->respond([
            'anggota' => $anggotaList,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Menghapus semua data penggajian untuk seorang anggota.
     * Method: DELETE
     * URL: /api/penggajian/{id}
     */
    public function delete($id = null)
    {
        $model = new PenggajianModel();
        $csrf_hash = csrf_hash();

        // Cek apakah ada data penggajian untuk anggota ini
        $exists = $model->where('id_anggota', $id)->first();
        if (!$exists) {
            return $this->failNotFound('Data penggajian untuk anggota ini tidak ditemukan.');
        }

        try {
            // Hapus semua entri yang terkait dengan id_anggota
            $model->where('id_anggota', $id)->delete();
            
            return $this->respondDeleted([
                'message' => 'Data penggajian berhasil dihapus.',
                'csrf_hash' => $csrf_hash
            ]);

        } catch (\Exception $e) {
            return $this->fail([
                'error' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
                'csrf_hash' => $csrf_hash
            ], 500);
        }
    }
}