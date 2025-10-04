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
        // Jika sudah login, redirect ke home
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
        // Kita tidak akan menggunakan template utama untuk halaman login
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
        $session = session();
        $model = new \App\Models\UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        // Debug: log request data
        log_message('debug', 'Login attempt for username: ' . $username);
        
        if (empty($username) || empty($password)) {
            $session->setFlashdata('msg', 'Username dan password harus diisi.');
            return redirect()->to('/login');
        }

        $user = $model->where('username', $username)->first();

        if ($user && password_verify($password, $user->password)) {
            $ses_data = [
                'user_id'    => $user->id_pengguna,
                'user_name'  => $user->username,
                'user_role'  => $user->role,
                'isLoggedIn' => true,
            ];
            $session->set($ses_data);

            log_message('debug', 'Login successful. Session data: ' . json_encode($session->get()));

            if ($user->role === 'admin') {
                return redirect()->to('/admin/dpr/anggota');
            } elseif ($user->role === 'dpr') {
                return redirect()->to('/dpr/anggota');
            } else {
                return redirect()->to('/dashboard');
            }
        }
        
        log_message('error', 'Login failed for username: ' . $username);
        $session->setFlashdata('msg', 'Username atau Password salah.');
        return redirect()->to('/login');
    }

    /**
     * Method logout() menghancurkan session dan redirect ke halaman login.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}