<?php
namespace App\Models\DPR;
use CodeIgniter\Model;

class AnggotaModel extends Model
{
    protected $table            = 'anggota';
    protected $primaryKey       = 'id_anggota';
    protected $allowedFields    = ['nama_depan', 'nama_belakang', 'gelar_depan', 'gelar_belakang', 'jabatan', 'status_pernikahan'];
    protected $returnType       = 'object';
}