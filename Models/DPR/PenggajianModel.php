<?php
namespace App\Models\DPR;
use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table            = 'penggajian';
    protected $primaryKey       = ['id_anggota', 'id_komponen_gaji'];
    protected $useAutoIncrement = false;
    protected $allowedFields    = ['id_anggota', 'id_komponen_gaji'];
    protected $returnType       = 'object';

    /**
     * Fungsi untuk mendapatkan ringkasan penggajian semua anggota.
     */
    public function getSummary(): array
    {
        // 1. Ambil semua anggota yang memiliki data penggajian (unik)
        $anggotaIds = $this->distinct()->select('id_anggota')->findAll();
        
        $anggotaData = [];
        if (empty($anggotaIds)) {
            return [
                'penggajian' => [],
                'pager' => null
            ];
        }

        $anggotaModel = new AnggotaModel();
        
        // 2. Loop melalui setiap anggota untuk menghitung take home pay
        foreach ($anggotaIds as $item) {
            $anggota = $anggotaModel->find($item->id_anggota);
            if ($anggota) {
                $takeHomePay = $this->calculateTakeHomePay($item->id_anggota);
                
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
        $this->db->transStart();

        // 1. Validasi: Pastikan anggota ada
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            throw new \Exception('Anggota tidak ditemukan.');
        }

        // 2. Hapus komponen lama untuk anggota ini (jika ada)
        $this->where('id_anggota', $id_anggota)->delete();

        // 3. Validasi & Insert komponen baru
        $komponenGajiModel = new KomponenGajiModel();
        foreach ($id_komponen as $id) {
            $komponen = $komponenGajiModel->find($id);
            if (!$komponen) {
                throw new \Exception("Komponen gaji dengan ID {$id} tidak valid.");
            }

            // Validasi jabatan
            if ($komponen->jabatan !== 'Semua' && $komponen->jabatan !== $anggota->jabatan) {
                throw new \Exception("Komponen '{$komponen->nama_komponen}' tidak sesuai untuk jabatan '{$anggota->jabatan}'.");
            }

            // Insert data baru
            $this->insert([
                'id_anggota' => $id_anggota,
                'id_komponen_gaji' => $id,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Gagal menyimpan data penggajian ke database.');
        }
    }

    /**
     * Fungsi untuk menghitung Take Home Pay untuk seorang anggota.
     */
    public function calculateTakeHomePay(int $id_anggota): float
    {
        // 1. Ambil data anggota
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return 0;
        }

        // 2. Ambil semua komponen gaji yang terhubung dengan anggota
        $assignedKomponen = $this->where('id_anggota', $id_anggota)
                                ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                                ->findAll();

        $totalGaji = 0;

        // 3. Iterasi dan hitung total gaji berdasarkan aturan
        foreach ($assignedKomponen as $komponen) {
            $nama_komponen = strtolower($komponen->nama_komponen);
            $nominal = (float) $komponen->nominal;

            // Aturan Tunjangan Istri/Suami
            if (str_contains($nama_komponen, 'istri') || str_contains($nama_komponen, 'suami')) {
                if (strtolower($anggota->status_pernikahan) === 'kawin') {
                    $totalGaji += $nominal;
                }
                continue; // Lanjut ke komponen berikutnya
            }

            // Aturan Tunjangan Anak
            if (str_contains($nama_komponen, 'anak')) {
                if ($anggota->jumlah_anak > 0) {
                    $jumlah_anak_dihitung = min($anggota->jumlah_anak, 2); // Maksimal 2 anak
                    $totalGaji += $nominal * $jumlah_anak_dihitung;
                }
                continue; // Lanjut ke komponen berikutnya
            }

            // Untuk komponen gaji lainnya, tambahkan langsung
            $totalGaji += $nominal;
        }

        return $totalGaji;
    }

    /**
     * Fungsi untuk mengambil detail penggajian untuk seorang anggota.
     */
    public function getPenggajianDetail(int $id_anggota): ?array
    {
        // 1. Ambil data anggota
        $anggotaModel = new AnggotaModel();
        $anggota = $anggotaModel->find($id_anggota);
        if (!$anggota) {
            return null;
        }

        // 2. Ambil semua komponen gaji yang terhubung dengan anggota
        $assignedKomponen = $this->where('id_anggota', $id_anggota)
                                ->join('komponen_gaji', 'komponen_gaji.id_komponen_gaji = penggajian.id_komponen_gaji')
                                ->select('komponen_gaji.*') // Hanya pilih kolom dari komponen_gaji
                                ->findAll();

        // 3. Hitung take home pay
        $takeHomePay = $this->calculateTakeHomePay($id_anggota);

        // 4. Kembalikan data
        return [
            'anggota' => $anggota,
            'komponen_gaji' => $assignedKomponen,
            'take_home_pay' => $takeHomePay
        ];
    }
}