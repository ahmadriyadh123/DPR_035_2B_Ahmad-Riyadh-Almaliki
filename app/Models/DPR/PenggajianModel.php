<?php
namespace App\Models\DPR;
use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table            = 'penggajian';
    protected $primaryKey       = 'id_anggota, id_komponen_gaji';
    protected $allowedFields    = ['id_anggota', 'id_komponen_gaji'];
    protected $returnType       = 'object';
}