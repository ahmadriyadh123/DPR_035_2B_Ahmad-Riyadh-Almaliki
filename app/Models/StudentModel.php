<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'entry_year'];
    protected $returnType       = 'object';

    /**
     * Mengambil student ID berdasarkan user ID.
     */
    public function getStudentIdByUserId($userId)
    {
        $student = $this->where('user_id', $userId)->first();
        return $student ? $student->id : null;
    }
}