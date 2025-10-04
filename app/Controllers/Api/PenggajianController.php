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
        $penggajianModel = new PenggajianModel();
        
        // getSummary() sudah mengintegrasikan session data tunjangan anak
        $summaryData = $penggajianModel->getSummary();
        
        return $this->respond($summaryData);
    }

    /**
     * Mengambil detail penggajian anggota berdasarkan ID.
     * Method: GET
     * URL: /api/penggajian/{id}
     */
    public function show($id = null)
    {
        $penggajianModel = new PenggajianModel();
        
        // Ambil info Tunjangan Anak dari session jika ada
        $session = session();
        $tunjanganAnakInfo = $session->get("tunjangan_anak_{$id}");
        
        $data = $penggajianModel->getPenggajianDetail((int)$id, $tunjanganAnakInfo);

        if (!$data) {
            return $this->failNotFound('Data penggajian untuk anggota ini tidak ditemukan.');
        }
        
        // Tambahkan info Tunjangan Anak ke response
        if ($tunjanganAnakInfo) {
            $data['tunjangan_anak_info'] = $tunjanganAnakInfo;
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
        log_message('info', '🔄 [ADMIN API] PenggajianController::summary called - USING SESSION DATA');
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
            $summaryData = []; // Initialize outside the if block
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
                        ->like('nama_depan', $search, 'both', null, true)
                        ->orLike('nama_belakang', $search, 'both', null, true)
                        ->orLike('jabatan', $search, 'both', null, true)
                        ->orLike('id_anggota', $search, 'both', null, true)
                    ->groupEnd();
                }
                
                $anggotaList = $anggotaBuilder->paginate($perPage, 'default', $page);
                
                // Pastikan pager tidak null sebelum mengambil detail
                if ($anggotaModel->pager) {
                    $pagerDetails = $anggotaModel->pager->getDetails();
                }
            
                // OPTIMIZATION: Batch fetch all penggajian data for current page anggota
                $currentAnggotaIds = array_column($anggotaList, 'id_anggota');
                $groupedPenggajian = [];
                
                if (!empty($currentAnggotaIds)) {
                    // Get all penggajian data for current page anggota in one query
                    $batchPenggajianData = $penggajianModel
                        ->select('penggajian.*, komponen_gaji.nama_komponen, komponen_gaji.nominal')
                        ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                        ->whereIn('penggajian.id_anggota', $currentAnggotaIds)
                        ->findAll();
                    
                    // Group by anggota ID for faster lookup
                    foreach ($batchPenggajianData as $item) {
                        $groupedPenggajian[$item->id_anggota][] = $item;
                    }
                }
                
                foreach ($anggotaList as $anggota) {
                    // Use batch data instead of individual database calls
                    $anggotaPenggajian = $groupedPenggajian[$anggota->id_anggota] ?? [];
                    $takeHomePay = $this->calculateTakeHomePayFromBatch($anggota, $anggotaPenggajian);
                    
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
        log_message('info', '🔄 PenggajianController::create called');
        log_message('info', '📊 Request method: ' . $this->request->getMethod());
        
        $model = new PenggajianModel();
        $input = $this->request->getBody();
        
        log_message('info', '📥 Raw input data: ' . $input);
        
        $data = json_decode($input, true);
        
        if (!$data) {
            log_message('error', '❌ Invalid JSON data received');
            return $this->fail('Data JSON tidak valid', 400);
        }
        
        log_message('info', '📋 Decoded data: ' . print_r($data, true));

        // Selalu perbarui CSRF hash di setiap response
        $csrf_hash = csrf_hash();

        $id_anggota = $data['id_anggota'] ?? null;
        $id_komponen = $data['id_komponen'] ?? [];
        $tunjangan_anak_info = $data['tunjangan_anak_info'] ?? null;
        
        log_message('info', '👤 ID Anggota: ' . $id_anggota);
        log_message('info', '💰 ID Komponen: ' . print_r($id_komponen, true));
        log_message('info', '👶 Tunjangan Anak Info: ' . print_r($tunjangan_anak_info, true));

        // Validasi input dasar
        if (empty($id_anggota)) {
            log_message('error', '❌ ID Anggota tidak boleh kosong');
            return $this->fail('ID Anggota harus dipilih.', 400);
        }
        
        if (empty($id_komponen) || !is_array($id_komponen)) {
            log_message('error', '❌ Komponen gaji tidak valid: ' . print_r($id_komponen, true));
            return $this->fail('Minimal satu komponen gaji harus dipilih.', 400);
        }

        try {
            log_message('info', '💾 Attempting to save data...');
            
            // Simpan info Tunjangan Anak ke session dan cache jika ada
            if ($tunjangan_anak_info) {
                $session = session();
                $session->set("tunjangan_anak_{$id_anggota}", $tunjangan_anak_info);
                
                // Save to persistent cache as well
                $model = new PenggajianModel();
                $model->getTunjanganAnakInfo($id_anggota); // This will save to cache
                
                log_message('info', '👶 Tunjangan Anak info saved to session and cache');
            }
            
            // Panggil metode di model untuk menyimpan data (tanpa parameter tambahan)
            $model->assignKomponenToAnggota($id_anggota, $id_komponen);
            
            log_message('info', '✅ Data penggajian berhasil disimpan');
            return $this->respondCreated([
                'message' => 'Data penggajian berhasil disimpan.',
                'csrf_hash' => $csrf_hash
            ]);

        } catch (\Exception $e) {
            log_message('error', '💥 Error saving penggajian: ' . $e->getMessage());
            log_message('error', '🔍 Stack trace: ' . $e->getTraceAsString());
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
        $tunjangan_anak_info = $data['tunjangan_anak_info'] ?? null;

        // Validasi input dasar
        if (empty($id) || empty($id_komponen)) {
            return $this->fail('ID Anggota dan minimal satu komponen gaji harus dipilih.', 400);
        }

        try {
            // Simpan info Tunjangan Anak ke session dan cache jika ada
            if ($tunjangan_anak_info) {
                $session = session();
                $session->set("tunjangan_anak_{$id}", $tunjangan_anak_info);
                
                // Save to persistent cache as well
                $penggajianModel = new PenggajianModel();
                $penggajianModel->getTunjanganAnakInfo($id); // This will save to cache
                
                log_message('info', '👶 Tunjangan Anak info updated in session and cache for anggota: ' . $id);
            }
            
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
    
    /**
     * Optimized calculation method that works with pre-fetched data
     */
    private function calculateTakeHomePayFromBatch($anggota, $penggajianData): float
    {
        $totalGaji = 0;
        $session = session();
        
        foreach ($penggajianData as $komponen) {
            $nama_komponen = strtolower($komponen->nama_komponen);
            $nominal = (float) $komponen->nominal;

            // Tunjangan Istri/Suami rule
            if (str_contains($nama_komponen, 'istri') || str_contains($nama_komponen, 'suami')) {
                if (strtolower($anggota->status_pernikahan) === 'kawin') {
                    $totalGaji += $nominal;
                }
                continue;
            }

            // Tunjangan Anak rule - use session data if available, same logic as calculateTakeHomePay
            if (str_contains($nama_komponen, 'anak')) {
                // Check if session data exists for this anggota and komponen
                $tunjanganAnakInfo = $session->get("tunjangan_anak_{$anggota->id_anggota}");
                
                if ($tunjanganAnakInfo && $tunjanganAnakInfo['komponen_id'] == $komponen->id_komponen_gaji) {
                    // Use session data - exactly like in calculateTakeHomePay
                    $jumlah_anak_dihitung = $tunjanganAnakInfo['jumlah_dihitung'];
                    $calculated = $nominal * $jumlah_anak_dihitung;
                    $totalGaji += $calculated;
                } else {
                    // Default behavior: use actual jumlah_anak from database or fallback to 0
                    $jumlah_anak = (int) ($anggota->jumlah_anak ?? 0);
                    $calculated = $nominal * $jumlah_anak;
                    $totalGaji += $calculated;
                }
                continue;
            }

            // Regular components
            $totalGaji += $nominal;
        }

        return $totalGaji;
    }
}