<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Kelas RoleBasedFilter mengelola akses berdasarkan peran pengguna.
 * Admin mendapatkan akses penuh, sementara non-admin hanya boleh melakukan operasi baca (GET).
 */
class RoleBasedFilter implements FilterInterface
{
    /**
     * Method before() dieksekusi sebelum request diproses.
     * Memeriksa peran pengguna dan metode request untuk menentukan akses.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        /**
         * Ambil peran pengguna dari session dan metode request.
         * Catat informasi debugging ke log.
         */
        $userRole = session()->get('user_role');
        $method = $request->getMethod();

        log_message('debug', 'RoleBasedFilter: User Role = ' . json_encode($userRole) . ', Method = ' . $method);

        /**
         * Izinkan akses penuh untuk admin.
         */
        if (strtolower($userRole ?? '') === 'admin') {
            log_message('debug', 'RoleBasedFilter: Akses diberikan untuk Admin.');
            return;
        }

        /**
         * Untuk non-admin, tolak akses jika bukan metode GET.
         * Kembalikan respons forbidden dengan pesan error.
         */
        if (strtolower($method) !== 'get') {
            log_message('warning', 'RoleBasedFilter: Akses DITOLAK untuk non-admin dengan metode ' . $method);
            return Services::response()
                ->setJSON([
                    'message' => 'Anda hanya memiliki akses untuk melihat data. Untuk melakukan perubahan, hubungi administrator.',
                    'userRole' => $userRole,
                    'requestedMethod' => $method
                ])
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }
        
        /**
         * Izinkan akses read-only untuk non-admin dengan metode GET.
         */
        log_message('debug', 'RoleBasedFilter: Akses read-only diberikan untuk role ' . ($userRole ?? 'Guest'));
    }

    /**
     * Method after() dieksekusi setelah request diproses.
     * Tidak ada aksi tambahan yang diperlukan.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi yang diperlukan
    }
}