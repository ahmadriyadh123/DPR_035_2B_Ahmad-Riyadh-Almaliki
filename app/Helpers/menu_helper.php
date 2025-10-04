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
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                [
                    'title' => 'Manajemen Akademik',
                    'type' => 'dropdown',
                    'items' => [
                        ['title' => 'Kelola Courses', 'url' => '/admin/courses'],
                        ['title' => 'Kelola Students', 'url' => '/admin/students'],
                        ['title' => 'Kelola Dosen', 'url' => '/admin/dosens'],
                    ]
                ],
                ['title' => 'Data Anggota', 'url' => '/admin/dpr/anggota'],
                ['title' => 'Komponen Gaji', 'url' => '/admin/dpr/komponengaji'],
                ['title' => 'Penggajian', 'url' => '/dpr/penggajian'],
            ],
            'student' => [ // <-- BAGIAN INI UNTUK STUDENT
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Courses', 'url' => '/courses'],
            ],
            'dpr' => [ // <-- BAGIAN INI UNTUK USER DPR
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Daftar Anggota', 'url' => '/dpr/anggota'],
                ['title' => 'Data Penggajian', 'url' => '/dpr/penggajian'],
            ],
            'public' => [ // <-- BAGIAN INI UNTUK PUBLIC
                ['title' => 'Dashboard', 'url' => '/dashboard'],
            ],
            // Anda bisa menambahkan role lain di sini
        ];

        $session = session();
        $user_role = $session->get('user_role');
        $is_logged_in = $session->get('isLoggedIn');

        // Debug log untuk troubleshooting
        log_message('debug', 'Menu Helper - User Role: ' . ($user_role ?? 'null'));
        log_message('debug', 'Menu Helper - Is Logged In: ' . ($is_logged_in ? 'true' : 'false'));

        $current_path = '/' . service('uri')->getPath();

        // Memulai pembuatan navbar dengan collapse yang benar
        $html = '<div class="collapse navbar-collapse" id="navbarNav">';
        
        // Menu utama di kiri
        $html .= '<ul class="navbar-nav me-auto">';
        
        // Tampilkan menu default bahkan jika belum login (untuk testing)
        if (!$is_logged_in) {
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link" href="' . site_url('/login') . '">Login untuk melihat menu lengkap</a>';
            $html .= '</li>';
        }
        
        if ($is_logged_in) {
            $current_menu = $menu_items[$user_role] ?? $menu_items['dosen'];

            // Loop untuk setiap item menu
            foreach ($current_menu as $item) {
                if (isset($item['type']) && $item['type'] === 'dropdown') {
                    // Dropdown menu
                    $html .= '<li class="nav-item dropdown">';
                    $html .= '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown' . str_replace(' ', '', $item['title']) . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                    $html .= esc($item['title']);
                    $html .= '</a>';
                    $html .= '<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . str_replace(' ', '', $item['title']) . '">';
                    
                    foreach ($item['items'] as $subitem) {
                        $active_class = ($current_path === $subitem['url']) ? 'active' : '';
                        $html .= '<li><a class="dropdown-item ' . $active_class . '" href="' . site_url($subitem['url']) . '">' . esc($subitem['title']) . '</a></li>';
                    }
                    
                    $html .= '</ul>';
                    $html .= '</li>';
                } else {
                    // Regular menu item
                    $active_class = ($current_path === $item['url']) ? 'active' : '';
                    $html .= '<li class="nav-item">';
                    $html .= '<a class="nav-link ' . $active_class . '" href="' . site_url($item['url']) . '">' . esc($item['title']) . '</a>';
                    $html .= '</li>';
                }
            }
        } else {
            // Debug: Tampilkan info jika tidak ada menu
            log_message('debug', 'Menu Helper - No menu items found. User role: ' . ($user_role ?? 'null') . ', Logged in: ' . ($is_logged_in ? 'true' : 'false'));
        }
        $html .= '</ul>';

        // Menu login/logout di kanan dengan info user
        $html .= '<ul class="navbar-nav ms-auto">';
        if ($is_logged_in) {
            $user_name = $session->get('user_name');
            $html .= '<li class="nav-item dropdown">';
            $html .= '<a class="nav-link dropdown-toggle" href="#" id="navbarUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
            $html .= '<i class="fas fa-user"></i> ' . esc($user_name);
            $html .= '</a>';
            $html .= '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUser">';
            $html .= '<li><a class="dropdown-item" href="' . site_url('dashboard') . '"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>';
            $html .= '<li><hr class="dropdown-divider"></li>';
            $html .= '<li><a class="dropdown-item" href="' . site_url('logout') . '"><i class="fas fa-sign-out-alt"></i> Logout</a></li>';
            $html .= '</ul>';
            $html .= '</li>';
        } else {
            $html .= '<li class="nav-item"><a class="nav-link" href="' . site_url('login') . '"><i class="fas fa-sign-in-alt"></i> Login</a></li>';
        }
        $html .= '</ul>';

        $html .= '</div>'; // Penutup div #navbarNav

        return $html;
    }
}

if (!function_exists('generate_menu_items')) {
    /**
     * Menghasilkan item-item menu sebagai <li> untuk navigasi.
     *
     * @return string HTML untuk item-item menu.
     */
    function generate_menu_items(): string
    {
        $menu_items = [
            'admin' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                [
                    'title' => 'Manajemen Akademik',
                    'type' => 'dropdown',
                    'items' => [
                        ['title' => 'Kelola Courses', 'url' => '/admin/courses'],
                        ['title' => 'Kelola Students', 'url' => '/admin/students'],
                        ['title' => 'Kelola Dosen', 'url' => '/admin/dosens'],
                    ]
                ],
                ['title' => 'Data Anggota', 'url' => '/admin/dpr/anggota'],
                ['title' => 'Komponen Gaji', 'url' => '/admin/dpr/komponengaji'],
                ['title' => 'Penggajian', 'url' => '/dpr/penggajian'],
            ],
            'student' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Courses', 'url' => '/courses'],
            ],
            'dpr' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Daftar Anggota', 'url' => '/dpr/anggota'],
                ['title' => 'Data Penggajian', 'url' => '/dpr/penggajian'],
            ],
            'dosen' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Data Anggota', 'url' => '/dpr/anggota'],
                ['title' => 'Komponen Gaji', 'url' => '/dpr/komponengaji'],
                ['title' => 'Penggajian', 'url' => '/dpr/penggajian'],
            ],
        ];

        $session = session();
        $user_role = $session->get('user_role');
        $is_logged_in = $session->get('isLoggedIn');
        $current_path = '/' . ltrim(service('uri')->getPath(), '/');

        $html = '';

        if ($is_logged_in) {
            $current_menu = $menu_items[$user_role] ?? $menu_items['dosen'];

            foreach ($current_menu as $item) {
                if (isset($item['type']) && $item['type'] === 'dropdown') {
                    $is_active_dropdown = false;
                    $sub_items_html = '';
                    foreach ($item['items'] as $subitem) {
                        $sub_url = rtrim($subitem['url'], '/');
                        $active_class = (strpos($current_path, $sub_url) === 0 && $sub_url !== '') || $current_path === $sub_url ? 'active' : '';
                        if ($active_class) {
                            $is_active_dropdown = true;
                        }
                        $sub_items_html .= '<li><a class="dropdown-item ' . $active_class . '" href="' . site_url($subitem['url']) . '">' . esc($subitem['title']) . '</a></li>';
                    }

                    $html .= '<li class="nav-item dropdown">';
                    $html .= '<a class="nav-link dropdown-toggle ' . ($is_active_dropdown ? 'active' : '') . '" href="#" id="navbarDropdown' . str_replace(' ', '', $item['title']) . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                    $html .= esc($item['title']);
                    $html .= '</a>';
                    $html .= '<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . str_replace(' ', '', $item['title']) . '">' . $sub_items_html . '</ul>';
                    $html .= '</li>';
                } else {
                    $item_url = rtrim($item['url'], '/');
                    $active_class = (strpos($current_path, $item_url) === 0 && $item_url !== '') || $current_path === $item_url ? 'active' : '';
                    $html .= '<li class="nav-item">';
                    $html .= '<a class="nav-link ' . $active_class . '" href="' . site_url($item['url']) . '">' . esc($item['title']) . '</a>';
                    $html .= '</li>';
                }
            }
        }

        return $html;
    }
}