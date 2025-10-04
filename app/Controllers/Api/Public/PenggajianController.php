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
     */
    public function index()
    {
        try {
            $request = service('request');
            $page = (int) ($request->getGet('page') ?? 1);
            $search = $request->getGet('search') ?? '';

            // Get penggajian summary
            $summaryData = $this->penggajianModel->getSummary();
            $penggajianData = $summaryData['penggajian'];

            // Apply search filter if provided
            if (!empty($search)) {
                $penggajianData = array_filter($penggajianData, function($item) use ($search) {
                    return stripos($item['nama_anggota'], $search) !== false ||
                           stripos($item['jabatan'], $search) !== false ||
                           stripos((string)$item['id_anggota'], $search) !== false ||
                           stripos((string)$item['take_home_pay'], $search) !== false;
                });
            }

            // Apply simple pagination
            $perPage = 10;
            $total = count($penggajianData);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedData = array_slice($penggajianData, $offset, $perPage);

            return $this->respond([
                'status' => 'success',
                'data' => $paginatedData,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => $totalPages,
                    'total_records' => $total
                ]
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
        return $this->index();
    }

    /**
     * Get single penggajian data by anggota ID
     * 
     * @param int $id_anggota
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
            $penggajianDetail = $this->penggajianModel->getDetailByAnggota($id_anggota);
            
            // Calculate take home pay
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