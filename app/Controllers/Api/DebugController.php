<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * Debug controller to help troubleshoot access issues
 */
class DebugController extends ResourceController
{
    /**
     * Debug endpoint to check current user permissions
     */
    public function permissions()
    {
        $session = session();
        
        $debug = [
            'session_data' => [
                'isLoggedIn' => $session->get('isLoggedIn'),
                'user_id' => $session->get('user_id'),
                'user_name' => $session->get('user_name'),
                'user_role' => $session->get('user_role'),
            ],
            'role_checks' => [
                'raw_role' => $session->get('user_role'),
                'lowercase_role' => strtolower($session->get('user_role') ?? ''),
                'is_admin_exact' => $session->get('user_role') === 'admin',
                'is_admin_case_insensitive' => strtolower($session->get('user_role') ?? '') === 'admin',
            ],
            'request_info' => [
                'method' => $this->request->getMethod(),
                'uri' => (string)$this->request->getUri(),
            ]
        ];
        
        return $this->respond($debug);
    }
}