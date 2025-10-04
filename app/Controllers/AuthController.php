<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Kelas AuthController mengelola proses autentikasi pengguna,
 * termasuk login, pemrosesan login, dan logout.
 */
class AuthController extends BaseController
{
    /**
     * Method login() menangani tampilan halaman login.
     * Jika pengguna sudah login, redirect ke halaman home.
     * Jika belum, tampilkan view login tanpa template utama.
     */
    public function login()
    {
        /**
         * Cek status login pengguna.
         * Redirect berdasarkan role jika sudah login.
         */
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            if ($role === 'admin') {
                return redirect()->to('/admin/courses');
            } else {
                return redirect()->to('/dashboard');
            }
        }

        $data = [
            'title' => 'Login'
        ];
        return view('login_view', $data);
    }

    /**
     * Method processLogin() memproses kredensial login dari request.
     * Mencari user berdasarkan username, verifikasi password,
     * set session jika berhasil, dan redirect berdasarkan role.
     * Jika gagal, set flashdata error dan redirect ke login.
     */
    public function processLogin()
    {
        /**
         * Inisialisasi session dan model user.
         * Ambil input username dan password dari request.
         */
        $session = session();
        $userModel = new \App\Models\UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        log_message('debug', 'Mencoba login untuk username: ' . $username);

        /**
         * Cari user berdasarkan username.
         */
        $user = $userModel->where('username', $username)->first();

        if ($user) {
            log_message('debug', 'User ditemukan: ' . json_encode($user));

            /**
             * Verifikasi password dan set session jika valid.
             * Redirect ke dashboard jika berhasil.
             */
            if (password_verify($password, $user->password)) {
                $sessionData = [
                    'user_id'    => $user->id_pengguna,
                    'user_name'  => $user->username,
                    'user_role'  => $user->role,
                    'isLoggedIn' => TRUE
                ];

                $session->set($sessionData);

                log_message('debug', 'Login BERHASIL. Data sesi diatur: ' . json_encode($session->get()));

                return redirect()->to('/dashboard');
            } else {
                log_message('warning', 'Login GAGAL: Password salah untuk username: ' . $username);
                $session->setFlashdata('error', 'Username atau Password salah.');
                return redirect()->to('/login');
            }
        } else {
            /**
             * Handle kasus user tidak ditemukan.
             * Set error flashdata dan redirect ke login.
             */
            log_message('warning', 'Login GAGAL: Username tidak ditemukan: ' . $username);
            $session->setFlashdata('error', 'Username atau Password salah.');
            return redirect()->to('/login');
        }
    }

    /**
     * Method logout() menghancurkan session dan redirect ke halaman login.
     */
    public function logout()
    {
        /**
         * Hancurkan session dan redirect ke login.
         */
        session()->destroy();
        return redirect()->to('/login');
    }
}