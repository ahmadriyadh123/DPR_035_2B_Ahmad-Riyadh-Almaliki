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
            return redirect()->to('/');
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

        $user = $model->where('username', $username)->first();

        // 1. Cek jika user ditemukan DAN password cocok
        if ($user && password_verify($password, $user->password)) {
            
            // 2. Jika berhasil, buat data session
            $ses_data = [
                'user_id'    => $user->id,
                'user_name'  => $user->username,
                'user_role'  => $user->role,
                'isLoggedIn'  => TRUE // <--- PERUBAHAN DI SINI
            ];
            $session->set($ses_data);

            if ($user->role === 'admin') {
                return redirect()->to('/admin/berita');
            } else {
                return redirect()->to('/dashboard');
            }
        }
        
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