<?php

namespace App\Controllers\Api\Public;

use App\Controllers\BaseController;
use App\Models\DPR\PenggajianModel;
use App\Models\DPR\AnggotaModel;
use CodeIgniter\API\ResponseTrait;

/**
 * Public Penggajian API Controller
 * Provides read-only API access to DPR payroll data for public users
 */
class PenggajianController extends BaseController
{
    use ResponseTrait;

    protected $penggajianModel;
    protected $anggotaModel;

    public function __construct()
    {
        $this->penggajianModel = new PenggajianModel();
        $this->anggotaModel = new AnggotaModel();
    }

    /**
     * Get all penggajian data (summary view)
     * Uses the same fresh data approach as Admin controller
     */
    public function index()
    {
        try {
            // DEBUG: Log that public API is being called
            log_message('info', '[PUBLIC API] Public\PenggajianController::index() called - USING SAME DATA AS ADMIN');
            
            $request = service('request');
            $page = (int) ($request->getGet('page') ?? 1);
            $search = $request->getGet('search') ?? '';
            $perPage = 10;

            // Use same approach as Admin controller for fresh data
            
            // 1. Get all anggota IDs that have penggajian data
            $builder = $this->penggajianModel->distinct()->select('id_anggota');
            $anggotaIdsWithPenggajian = $builder->findColumn('id_anggota') ?? [];

            $penggajianData = [];
            $pagerDetails = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_pages' => 0,
                'total_records' => 0
            ];

            if (!empty($anggotaIdsWithPenggajian)) {
                $anggotaBuilder = $this->anggotaModel->whereIn('id_anggota', $anggotaIdsWithPenggajian);
                
                // Apply search filter if provided - same as Admin
                if (!empty($search)) {
                    $anggotaBuilder->groupStart()
                        ->like('nama_depan', $search, 'both', null, true)
                        ->orLike('nama_belakang', $search, 'both', null, true)
                        ->orLike('jabatan', $search, 'both', null, true)
                        ->orLike('id_anggota', $search, 'both', null, true)
                    ->groupEnd();
                }
                
                $anggotaList = $anggotaBuilder->paginate($perPage, 'default', $page);
                
                // Get pager details
                if ($this->anggotaModel->pager) {
                    $pagerDetails = [
                        'current_page' => $this->anggotaModel->pager->getCurrentPage(),
                        'per_page' => $this->anggotaModel->pager->getPerPage(),
                        'total_pages' => $this->anggotaModel->pager->getPageCount(),
                        'total_records' => $this->anggotaModel->pager->getTotal()
                    ];
                }

                // Process each anggota using fresh data
                foreach ($anggotaList as $anggota) {
                    // Calculate take home pay using SAME method as admin (with cached data)
                    $takeHomePay = $this->penggajianModel->calculateTakeHomePay($anggota->id_anggota);
                    
                    $namaLengkap = trim(implode(' ', array_filter([
                        $anggota->gelar_depan ?? '', 
                        $anggota->nama_depan ?? '', 
                        $anggota->nama_belakang ?? '', 
                        $anggota->gelar_belakang ?? ''
                    ])));

                    $penggajianData[] = [
                        'id_anggota' => $anggota->id_anggota,
                        'nama_anggota' => $namaLengkap,
                        'jabatan' => $anggota->jabatan ?? '',
                        'take_home_pay' => $takeHomePay,
                    ];
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $penggajianData,
                'pagination' => $pagerDetails
            ]);

        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan saat mengambil data penggajian: ' . $e->getMessage());
        }
    }

    /**
     * Get penggajian summary (same as index but with different endpoint for compatibility)
     */
    public function summary()
    {
        // DEBUG: Log that public API summary is being called
        log_message('info', '🌐 [PUBLIC API] Public\\PenggajianController::summary() called - SHOWING SAME DATA AS ADMIN');
        
        return $this->index();
    }

    /**
     * Get single penggajian data by anggota ID
     */
    public function show($id_anggota)
    {
        try {
            // Get anggota data
            $anggota = $this->anggotaModel->find($id_anggota);
            if (!$anggota) {
                return $this->failNotFound('Data anggota tidak ditemukan');
            }

            // Get penggajian details for this anggota
            $penggajianDetail = $this->penggajianModel->getPenggajianDetail($id_anggota);
            
            // Calculate take home pay using SAME method as admin (with session data)
            $takeHomePay = $this->penggajianModel->calculateTakeHomePay($id_anggota);

            $namaLengkap = trim(($anggota->gelar_depan ? $anggota->gelar_depan . ' ' : '') . 
                              $anggota->nama_depan . ' ' . $anggota->nama_belakang . 
                              ($anggota->gelar_belakang ? ', ' . $anggota->gelar_belakang : ''));

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'id_anggota' => $anggota->id_anggota,
                    'nama_anggota' => $namaLengkap,
                    'jabatan' => $anggota->jabatan,
                    'status_pernikahan' => $anggota->status_pernikahan,
                    'jumlah_anak' => $anggota->jumlah_anak,
                    'take_home_pay' => $takeHomePay,
                    'komponen_gaji' => $penggajianDetail['komponen'] ?? []
                ]
            ]);

        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan saat mengambil detail penggajian: ' . $e->getMessage());
        }
    }
}