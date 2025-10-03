<?php
namespace App\Models\DPR;
use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table            = 'penggajian';
    protected $primaryKey       = 'id_anggota, id_komponen_gaji';
    protected $allowedFields    = ['id_anggota', 'id_komponen_gaji'];
    protected $returnType       = 'object';

    /**
     * Menetapkan komponen gaji ke seorang anggota.
     *
     * @param int   $id_anggota  ID anggota.
     * @param array $id_komponen Daftar ID komponen gaji.
     * @return void
     * @throws \Exception Jika terjadi error validasi atau database.
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
}