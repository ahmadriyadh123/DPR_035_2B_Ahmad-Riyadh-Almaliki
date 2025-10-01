<?php
namespace App\Models;
use CodeIgniter\Model;

class NamaModel extends Model
{
    protected $table            = 'nama_tabel';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['field1', 'field2'];
    protected $returnType       = 'object';
}