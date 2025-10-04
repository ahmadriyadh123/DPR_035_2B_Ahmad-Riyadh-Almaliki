<?php
namespace App\Models\DPR;
use CodeIgniter\Model;
use App\Models\DPR\AnggotaModel;
use App\Models\DPR\KomponenGajiModel;

class PenggajianModel extends Model
{
    protected $table            = 'penggajian';
    protected $primaryKey       = 'id_anggota';
    protected $useAutoIncrement = false;
    protected $allowedFields    = ['id_anggota', 'id_komponen_gaji'];
    protected $returnType       = 'object';

    /**
     * Fungsi untuk mendapatkan ringkasan penggajian semua anggota.
     */
    public function getSummary(): array
    {
        /**
         * Ambil semua anggota yang memiliki data penggajian (unik).
         */
        $anggotaIds = $this->distinct()->select('id_anggota')->findAll();
        
        $anggotaData = [];
        if (empty($anggotaIds)) {
            return [
                'penggajian' => [],
                'pager' => null
            ];
        }

        $anggotaModel = new AnggotaModel();
        $session = session();
        
        /**
         * Loop melalui setiap anggota untuk menghitung take home pay.
         */
        foreach ($anggotaIds as $item) {
            $anggota = $anggotaModel->find($item->id_anggota);
            if ($anggota) {
                $tunjanganAnakInfo = $session->get("tunjangan_anak_{$item->id_anggota}");
                
                log_message('info', 'getSummary processing anggota: ' . $item->id_anggota . ' (' . $anggota->nama_depan . ')');
                log_message('info', 'Session data: ' . print_r($tunjanganAnakInfo, true));
                
                $takeHomePay = $this->calculateTakeHomePay($item->id_anggota, $tunjanganAnakInfo);
                
                $namaLengkap = trim(($anggota->gelar_depan ? $anggota->gelar_depan . ' ' : '') . $anggota->nama_depan . ' ' . $anggota->nama_belakang . ($anggota->gelar_belakang ? ', ' . $anggota->gelar_belakang : ''));

                $anggotaData[] = [
                    'id_anggota'    => $anggota->id_anggota,
                    'nama_anggota'  => $namaLengkap,
                    'jabatan'       => $anggota->jabatan,
                    'take_home_pay' => $takeHomePay,
                ];
            }
        }
        return [
            'penggajian' => $anggotaData,
            'pager'      => null // Placeholder untuk paginasi
        ];
    }

    /**
     * Fungsi untuk menetapkan komponen gaji ke seorang anggota.
     */
    public function assignKomponenToAnggota(int $id_anggota, array $id_komponen)
    {
        /**
         * Mulai transaksi dan log awal proses.
         */
        log_message('info', 'Starting assignKomponenToAnggota for anggota: ' . $id_anggota);
        log_message('info', 'Komponen IDs: ' . print_r($id_komponen, true));
        
        $this->db->transStart();

        /**
         * Validasi: Pastikan anggota ada.
         */
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            log_message('error', 'Anggota tidak ditemukan: ' . $id_anggota);
            throw new \Exception('Anggota tidak ditemukan.');
        }
        
        log_message('info', 'Anggota ditemukan: ' . $anggota->nama_depan . ' ' . $anggota->nama_belakang . ' (' . $anggota->jabatan . ')');

        /**
         * Hapus komponen lama untuk anggota ini (jika ada).
         */
        $deleted = $this->where('id_anggota', $id_anggota)->delete();
        log_message('info', 'Deleted old records: ' . ($deleted ? 'success' : 'none or failed'));

        /**
         * Validasi & Insert komponen baru.
         */
        $komponenGajiModel = new KomponenGajiModel();
        foreach ($id_komponen as $id) {
            log_message('info', 'Processing komponen ID: ' . $id);
            
            $komponen = $komponenGajiModel->find($id);
            if (!$komponen) {
                log_message('error', 'Komponen gaji tidak valid: ' . $id);
                throw new \Exception("Komponen gaji dengan ID {$id} tidak valid.");
            }

            log_message('info', 'Komponen found: ' . $komponen->nama_komponen . ' for jabatan: ' . $komponen->jabatan);

            if ($komponen->jabatan !== 'Semua' && $komponen->jabatan !== $anggota->jabatan) {
                log_message('error', 'Jabatan mismatch: ' . $komponen->jabatan . ' vs ' . $anggota->jabatan);
                throw new \Exception("Komponen '{$komponen->nama_komponen}' tidak sesuai untuk jabatan '{$anggota->jabatan}'.");
            }

            $insertData = [
                'id_anggota' => $id_anggota,
                'id_komponen_gaji' => $id,
            ];
            
            log_message('info', 'Inserting data: ' . print_r($insertData, true));
            
            $insertResult = $this->db->table($this->table)->insert($insertData);
            if (!$insertResult) {
                $error = $this->db->error();
                log_message('error', 'Failed to insert: ' . print_r($error, true));
                throw new \Exception('Gagal menyimpan data komponen gaji: ' . ($error['message'] ?? 'Unknown error'));
            }
            
            log_message('info', 'Successfully inserted komponen: ' . $id);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'Transaction failed');
            throw new \Exception('Gagal menyimpan data penggajian ke database.');
        }
        
        log_message('info', 'Transaction completed successfully');
    }

    /**
     * Fungsi untuk menghitung Take Home Pay untuk seorang anggota dengan data session.
     * Optimized version with reduced logging for better performance.
     */
    public function calculateTakeHomePay(int $id_anggota, $tunjangan_anak_info = null): float
    {
        /**
         * Ambil data anggota.
         */
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return 0;
        }

        /**
         * Ambil semua komponen gaji yang terhubung dengan anggota.
         */
        $assignedKomponen = $this->where('id_anggota', $id_anggota)
                                ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                                ->findAll();

        $totalGaji = 0;

        /**
         * Iterasi dan hitung total gaji berdasarkan aturan.
         */
        foreach ($assignedKomponen as $komponen) {
            $nama_komponen = strtolower($komponen->nama_komponen);
            $nominal = (float) $komponen->nominal;

            if (str_contains($nama_komponen, 'istri') || str_contains($nama_komponen, 'suami')) {
                if (strtolower($anggota->status_pernikahan) === 'kawin') {
                    $totalGaji += $nominal;
                }
                continue;
            }

            if (str_contains($nama_komponen, 'anak')) {
                if ($tunjangan_anak_info && $tunjangan_anak_info['komponen_id'] == $komponen->id_komponen_gaji) {
                    $jumlah_anak_dihitung = $tunjangan_anak_info['jumlah_dihitung'];
                    $calculated = $nominal * $jumlah_anak_dihitung;
                    $totalGaji += $calculated;
                } else {
                    $jumlah_anak = (int) ($anggota->jumlah_anak ?? 0);
                    $calculated = $nominal * $jumlah_anak;
                    $totalGaji += $calculated;
                }
                continue;
            }

            $totalGaji += $nominal;
        }

        return $totalGaji;
    }

    /**
     * Calculate take home pay for public view (read-only, no session data)
     * Always uses database values, never session customizations
     */
    public function calculateTakeHomePayPublic(int $id_anggota): float
    {
        /**
         * Ambil data anggota.
         */
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return 0;
        }

        /**
         * Ambil semua komponen gaji yang terhubung dengan anggota.
         */
        $assignedKomponen = $this->where('id_anggota', $id_anggota)
                                ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                                ->findAll();

        $totalGaji = 0;

        /**
         * Iterasi dan hitung total gaji berdasarkan aturan (tanpa session data).
         */
        foreach ($assignedKomponen as $komponen) {
            $nama_komponen = strtolower($komponen->nama_komponen);
            $nominal = (float) $komponen->nominal;

            if (str_contains($nama_komponen, 'istri') || str_contains($nama_komponen, 'suami')) {
                if (strtolower($anggota->status_pernikahan) === 'kawin') {
                    $totalGaji += $nominal;
                }
                continue;
            }

            if (str_contains($nama_komponen, 'anak')) {
                $jumlah_anak = (int) ($anggota->jumlah_anak ?? 0);
                $calculated = $nominal * $jumlah_anak;
                $totalGaji += $calculated;
                continue;
            }

            $totalGaji += $nominal;
        }

        return $totalGaji;
    }

    /**
     * Fungsi untuk mengambil detail penggajian untuk seorang anggota dengan data session.
     */
    public function getPenggajianDetail(int $id_anggota, $tunjangan_anak_info = null): ?array
    {
        /**
         * Ambil data anggota.
         */
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return null;
        }

        /**
         * Ambil semua komponen gaji yang terhubung dengan anggota.
         */
        $assignedKomponen = $this->where('id_anggota', $id_anggota)
                                ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                                ->select('komponen_gaji.*')
                                ->findAll();

        /**
         * Hitung take home pay dengan data session.
         */
        $takeHomePay = $this->calculateTakeHomePay($id_anggota, $tunjangan_anak_info);

        /**
         * Kembalikan data.
         */
        return [
            'anggota' => $anggota,
            'komponen_gaji' => $assignedKomponen,
            'take_home_pay' => $takeHomePay
        ];
    }
}