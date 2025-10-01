<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nim', 'nama', 'umur'];

    public function search($keyword)
    {
        // Menggunakan Query Builder
        return $this->table('mahasiswa')
                    ->like('nama', $keyword)
                    ->orLike('nim', $keyword)
                    ->orLike('umur', $keyword)
                    ->get()
                    ->getResultArray();
    }
}