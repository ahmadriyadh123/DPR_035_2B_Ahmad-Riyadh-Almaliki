<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DPR\AnggotaModel;
use App\Models\DPR\KomponenGajiModel;
use App\Models\DPR\PenggajianModel;

class PenggajianControllerOptimized extends ResourceController
{
    /**
     * OPTIMIZED: Mengambil ringkasan penggajian untuk semua anggota.
     * Fixed N+1 query problem by batch processing
     */
    public function summary()
    {
        log_message('info', '🔄 PenggajianController::summary called (OPTIMIZED)');
        
        $anggotaModel = new AnggotaModel();
        $penggajianModel = new PenggajianModel();

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $perPage = 10;

        try {
            // Get all anggota that have penggajian data
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
                
                if (!empty($search)) {
                    $anggotaBuilder->groupStart()
                        ->like('nama_depan', $search, 'both', null, true)
                        ->orLike('nama_belakang', $search, 'both', null, true)
                        ->orLike('jabatan', $search, 'both', null, true)
                        ->orLike('id_anggota', $search, 'both', null, true)
                    ->groupEnd();
                }
                
                $anggotaList = $anggotaBuilder->paginate($perPage, 'default', $page);
                
                if ($anggotaModel->pager) {
                    $pagerDetails = $anggotaModel->pager->getDetails();
                }
            }
            
            // OPTIMIZATION: Batch fetch all penggajian data for current page
            $currentAnggotaIds = array_column($anggotaList, 'id_anggota');
            $groupedPenggajian = [];
            
            if (!empty($currentAnggotaIds)) {
                $batchData = $penggajianModel
                    ->select('penggajian.*, komponen_gaji.nama_komponen, komponen_gaji.nominal')
                    ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                    ->whereIn('penggajian.id_anggota', $currentAnggotaIds)
                    ->findAll();
                
                foreach ($batchData as $item) {
                    $groupedPenggajian[$item->id_anggota][] = $item;
                }
            }
            
            $summaryData = [];
            foreach ($anggotaList as $anggota) {
                $anggotaPenggajian = $groupedPenggajian[$anggota->id_anggota] ?? [];
                $takeHomePay = $this->calculateTakeHomePayOptimized($anggota, $anggotaPenggajian);
                
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

            return $this->respond([
                'penggajian' => $summaryData,
                'pager'      => $pagerDetails,
                'csrf_hash'  => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in summary: ' . $e->getMessage());
            
            return $this->respond([
                'penggajian' => [],
                'pager' => ['currentPage' => 1, 'pageCount' => 0, 'total' => 0],
                'csrf_hash' => csrf_hash(),
                'error' => 'Terjadi kesalahan saat memuat data penggajian'
            ], 200);
        }
    }
    
    private function calculateTakeHomePayOptimized($anggota, $penggajianData): float
    {
        $totalGaji = 0;
        
        foreach ($penggajianData as $komponen) {
            $nama_komponen = strtolower($komponen->nama_komponen);
            $nominal = (float) $komponen->nominal;

            if (str_contains($nama_komponen, 'istri') || str_contains($nama_komponen, 'suami')) {
                if (strtolower($anggota->status_pernikahan) === 'kawin') {
                    $totalGaji += $nominal;
                }
                continue;
            }

            if (str_contains($nama_komponen, 'anak')) {
                $totalGaji += $nominal * 1; // Default 1 child for summary
                continue;
            }

            $totalGaji += $nominal;
        }

        return $totalGaji;
    }
}