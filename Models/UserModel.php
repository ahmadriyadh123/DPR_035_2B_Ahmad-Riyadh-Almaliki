<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'pengguna';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'role', ''];
    protected $returnType    = 'object';
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }
    public function getStudents($perPage = 10)
    {
        return $this->where('role !=', 'admin')->paginate($perPage);
    }
    public function searchStudents($keyword, $perPage = 10)
    {
        return $this->where('role !=', 'admin')
                    ->like('username', $keyword)
                    ->paginate($perPage);
    }
}