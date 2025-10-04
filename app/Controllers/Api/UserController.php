<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * UserController untuk mengelola data user dan session info
 */
class UserController extends ResourceController
{
    /**
     * Mendapatkan informasi user yang sedang login
     * Method: GET
     * URL: /api/user/info
     */
    public function info()
    {
        $session = session();
        
        if (!$session->get('isLoggedIn')) {
            // Return guest user info instead of error
            return $this->respond([
                'user_id' => null,
                'username' => 'guest',
                'role' => 'guest',
                'isLoggedIn' => false,
                'isAdmin' => false,
                'jumlah_anak' => 0
            ]);
        }
        
        $userData = [
            'user_id' => $session->get('user_id'),
            'username' => $session->get('user_name'),
            'role' => $session->get('user_role'),
            'isLoggedIn' => $session->get('isLoggedIn'),
            'isAdmin' => strtolower($session->get('user_role') ?? '') === 'admin',
            'jumlah_anak' => $session->get('jumlah_anak') ?? 0
        ];
        
        return $this->respond($userData);
    }
}