<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UpdateAdminPassword extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Update password admin menjadi "admin" 
        $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
        
        $db->table('pengguna')
           ->where('username', 'admin')
           ->update(['password' => $hashedPassword]);
           
        echo "Admin password updated to 'admin'\n";
    }
}