<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * ApiAuthFilter untuk membatasi akses API berdasarkan role user
 * Admin: Full access (read/write)
 * Public/DPR: Read-only access
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $requestPath = $request->getPath();
        $requestMethod = $request->getMethod();
        
        log_message('info', "ApiAuthFilter - Request: {$requestMethod} {$requestPath}");
        
        // Pastikan user sudah login
        if (!session()->get('isLoggedIn')) {
            log_message('warning', "ApiAuthFilter - User not logged in for {$requestMethod} {$requestPath}");
            $response = Services::response();
            return $response->setJSON([
                'status' => 401,
                'error' => 'Authentication required',
                'message' => 'Akses ditolak: Anda harus login.'
            ])->setStatusCode(401);
        }

        $userRole = session()->get('user_role');
        log_message('info', "ApiAuthFilter - User role: {$userRole}, Method: {$requestMethod}");
        
        // Admin memiliki akses penuh ke semua method
        if (strtolower($userRole ?? '') === 'admin') {
            log_message('info', "ApiAuthFilter - Admin access granted for {$requestMethod} {$requestPath}");
            return null; // Allow access
        }
        
        // User non-admin hanya boleh akses method GET (read-only)
        if (strtolower($requestMethod) !== 'get') {
            log_message('warning', "ApiAuthFilter - Access denied for role '{$userRole}' on {$requestMethod} {$requestPath}");
            $response = Services::response();
            return $response->setJSON([
                'status' => 403,
                'error' => 'Access denied. You only have read-only permission.',
                'message' => 'Anda hanya memiliki akses untuk melihat data. Untuk melakukan perubahan, hubungi administrator.',
                'userRole' => $userRole
            ])->setStatusCode(403);
        }
        
        log_message('info', "ApiAuthFilter - GET access granted for role '{$userRole}' on {$requestPath}");
        return null; // Allow GET access for all authenticated users
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ...
    }
}