<?php
namespace App\Models;
use CodeIgniter\Model;

class DosenModel extends Model
{
    protected $table            = 'dosens';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'nidn', 'jabatan'];
    protected $returnType       = 'object';
}