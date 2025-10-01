<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

/**
 * Kelas AuthFilter adalah filter autentikasi yang memeriksa status login pengguna
 * sebelum request diproses. Jika belum login, redirect ke halaman login.
 * Jika ada argumen role, memeriksa apakah role pengguna diizinkan.
 */
class AuthFilter implements FilterInterface
{
    use ResponseTrait;
    
    /**
     * Method before() dipanggil sebelum request diproses.
     * Memeriksa apakah pengguna sudah login; jika belum, redirect ke login.
     * Jika ada argumen (role yang diperlukan), verifikasi role pengguna;
     * jika tidak cocok, lempar exception PageNotFound.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek dulu apakah sudah login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Jika filter memerlukan role tertentu (contoh: ['filter' => 'auth:admin'])
        if ($arguments) {
            $userRole = session()->get('user_role');
            
            // Cek apakah role pengguna yang sedang login diizinkan
            if (!in_array($userRole, $arguments)) {
                // Jika tidak diizinkan, tampilkan halaman error 404
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("You don't have permission to access this page.");
            }
        }
    }

    /**
     * Method after() dipanggil setelah request diproses.
     * Tidak melakukan apa-apa dalam implementasi ini.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa
    }
}