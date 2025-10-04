<?php

namespace App\Controllers\Api\Public;

use App\Controllers\BaseController;
use App\Models\DPR\AnggotaModel;
use CodeIgniter\API\ResponseTrait;

/**
 * Public Anggota API Controller
 * Provides read-only API access to DPR member data for public users
 */
class AnggotaController extends BaseController
{
    use ResponseTrait;

    protected $anggotaModel;

    public function __construct()
    {
        $this->anggotaModel = new AnggotaModel();
    }

    /**
     * Get all anggota data (paginated)
     */
    public function index()
    {
        try {
            $request = service('request');
            $page = (int) ($request->getGet('page') ?? 1);
            $perPage = (int) ($request->getGet('per_page') ?? 10);
            $search = $request->getGet('search') ?? '';

            $builder = $this->anggotaModel->select('
                id_anggota, 
                CONCAT(
                    COALESCE(gelar_depan, ""), 
                    IF(gelar_depan IS NOT NULL AND gelar_depan != "", " ", ""),
                    nama_depan, " ", nama_belakang,
                    IF(gelar_belakang IS NOT NULL AND gelar_belakang != "", ", ", ""),
                    COALESCE(gelar_belakang, "")
                ) as nama_lengkap,
                jabatan,
                status_pernikahan,
                jumlah_anak
            ');

            // Apply search filter if provided
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('nama_depan', $search)
                    ->orLike('nama_belakang', $search)
                    ->orLike('jabatan', $search)
                    ->groupEnd();
            }

            // Get paginated results
            $data = $builder->paginate($perPage, 'default', $page);
            $pager = $this->anggotaModel->pager;

            return $this->respond([
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $pager->getPageCount(),
                    'total_records' => $pager->getTotal()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan saat mengambil data anggota: ' . $e->getMessage());
        }
    }

    /**
     * Get single anggota data by ID
     * 
     * @param int $id
     */
    public function show($id)
    {
        try {
            $anggota = $this->anggotaModel->select('
                id_anggota, 
                nama_depan,
                nama_belakang,
                gelar_depan,
                gelar_belakang,
                CONCAT(
                    COALESCE(gelar_depan, ""), 
                    IF(gelar_depan IS NOT NULL AND gelar_depan != "", " ", ""),
                    nama_depan, " ", nama_belakang,
                    IF(gelar_belakang IS NOT NULL AND gelar_belakang != "", ", ", ""),
                    COALESCE(gelar_belakang, "")
                ) as nama_lengkap,
                jabatan,
                status_pernikahan,
                jumlah_anak
            ')->find($id);

            if (!$anggota) {
                return $this->failNotFound('Data anggota tidak ditemukan');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $anggota
            ]);

        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan saat mengambil detail anggota: ' . $e->getMessage());
        }
    }
}