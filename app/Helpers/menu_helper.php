<?php

if (!function_exists('generate_menu')) {
    /**
     * Menghasilkan menu navigasi dinamis berdasarkan role pengguna.
     *
     * @return string HTML untuk navigasi.
     */
    function generate_menu(): string
    {
        // Definisikan item menu untuk setiap role
        $menu_items = [
            'admin' => [
                ['title' => 'Dashboard', 'url' => '/admin/berita'],
                ['title' => 'Kelola Courses', 'url' => '/admin/courses'],
                ['title' => 'Kelola Students', 'url' => '/admin/students'],
                ['title' => 'Kelola Dosen', 'url' => '/admin/dosens'],
            ],
            'student' => [ // <-- BAGIAN INI UNTUK STUDENT
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Courses', 'url' => '/courses'],
            ],
            // Anda bisa menambahkan role lain di sini, contoh: 'dosen'
        ];

        $session = session();
        $user_role = $session->get('user_role');
        $is_logged_in = $session->get('isLoggedIn');

        $current_path = '/' . service('uri')->getPath();

        // Memulai pembuatan navbar
        $html = '<div class="collapse navbar-collapse" id="navbarNav">';
        
        // Menu utama di kiri
        $html .= '<ul class="navbar-nav mr-auto">';
        if ($is_logged_in && isset($menu_items[$user_role])) {
            $current_menu = $menu_items[$user_role];

            // Loop untuk setiap item menu
            foreach ($current_menu as $item) {
                $active_class = ($current_path === $item['url']) ? 'active' : '';
                $html .= '<li class="nav-item">';
                $html .= '<a class="nav-link ' . $active_class . '" href="' . site_url($item['url']) . '">' . esc($item['title']) . '</a>';
                $html .= '</li>';
            }
        }
        $html .= '</ul>';

        // Menu login/logout di kanan
        $html .= '<ul class="navbar-nav mb-2 mb-lg-0">';
        if ($is_logged_in) {
            $html .= '<li class="nav-item"><a class="nav-link" href="' . site_url('logout') . '">Logout</a></li>';
        } else {
            $html .= '<li class="nav-item"><a class="nav-link" href="' . site_url('login') . '">Login</a></li>';
        }
        $html .= '</ul>';

        $html .= '</div>'; // Penutup div #navbarNav

        return $html;
    }
}