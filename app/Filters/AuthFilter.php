<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;

/**
 * Kelas AuthFilter adalah filter autentikasi yang memeriksa status login pengguna
 * sebelum request diproses. Jika belum login, redirect ke halaman login.
 * Jika ada argumen role, memeriksa apakah role pengguna diizinkan.
 */
class AuthFilter implements FilterInterface
{
    use ResponseTrait;
    
    /**
     * Fungsi before() dijalankan sebelum controller dipanggil.
     * Memeriksa apakah user sudah login dan role-nya sesuai.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jika session 'isLoggedIn' tidak ada atau false
        if (!session()->get('isLoggedIn')) {
            // Redirect pengguna ke halaman login
            return redirect()->to('/login');
        }
        
        // Jika ada argument role, periksa apakah user memiliki role yang sesuai
        if ($arguments && count($arguments) > 0) {
            $requiredRole = $arguments[0];
            $userRole = session()->get('user_role');
            
            if ($userRole !== $requiredRole) {
                // Jika role tidak sesuai, redirect ke dashboard
                return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        }
    }

    /**
     * Fungsi after() dijalankan setelah controller selesai.
     * Tidak perlu melakukan apa-apa setelah request.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}