<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - redirect to public homepage
$routes->get('/', 'Home::index');

// Debug route
$routes->get('debug/session', 'DebugController::sessionInfo');
$routes->get('help/login', function() {
    return view('login_instructions');
});

// Authentication routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::processLogin');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::processLogin');
$routes->get('/logout', 'AuthController::logout');

// ==========================================================================
// SISTEM AKADEMIK (TIDAK AKTIF - TELAH DIPINDAHKAN KE FOLDER TERPISAH)
// ==========================================================================
/*
// Academic Admin routes (COMMENTED OUT - MOVED TO Akademik folder)
$routes->group('admin/akademik', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\Akademik\Admin'], static function ($routes) {
    // Course Management
    $routes->get('courses', 'CourseController::spaShell');
    $routes->get('courses/(:any)', 'CourseController::spaShell');
    
    // Student Management
    $routes->get('students', 'StudentController::spaShell');
    $routes->get('students/(:any)', 'StudentController::spaShell');
});

// Academic Student routes (COMMENTED OUT - MOVED TO Akademik folder)
$routes->group('akademik', ['filter' => 'auth', 'namespace' => 'App\Controllers\Akademik'], static function ($routes) {
    // Student course routes
    $routes->get('courses', 'CourseController::index');
    $routes->post('courses/enroll', 'CourseController::enrollMultiple');
    
    // Mahasiswa routes
    $routes->get('mahasiswa', 'MahasiswaController::index');
    $routes->get('mahasiswa/create', 'MahasiswaController::create');
    $routes->post('mahasiswa/store', 'MahasiswaController::store');
    $routes->get('mahasiswa/edit/(:num)', 'MahasiswaController::edit/$1');
    $routes->post('mahasiswa/update/(:num)', 'MahasiswaController::update/$1');
    $routes->get('mahasiswa/(:num)', 'MahasiswaController::detail/$1');
    $routes->get('mahasiswa/delete/(:num)', 'MahasiswaController::delete/$1');
});

// Academic API routes (COMMENTED OUT - MOVED TO Akademik folder)
$routes->group('api/akademik', ['filter' => 'auth', 'namespace' => 'App\Controllers\Akademik\Api'], static function ($routes) {
    // Course API
    $routes->get('courses', 'CourseController::index');
    $routes->get('courses/(:num)', 'CourseController::show/$1');
    $routes->post('courses', 'CourseController::create');
    $routes->put('courses/(:num)', 'CourseController::update/$1');
    $routes->delete('courses/(:num)', 'CourseController::delete/$1');
    $routes->get('courses/available-for-student/(:num)', 'CourseController::availableForStudent/$1');
    
    // Student API
    $routes->get('students', 'StudentController::index');
    $routes->get('students/(:num)', 'StudentController::show/$1');
    $routes->post('students', 'StudentController::create');
    $routes->put('students/(:num)', 'StudentController::update/$1');
    $routes->delete('students/(:num)', 'StudentController::delete/$1');
    
    // Student view API
    $routes->get('student-courses', 'StudentViewController::getCoursesData');
    $routes->post('student-courses/enroll-batch', 'StudentViewController::enrollBatch');
    
    // Enrollment API
    $routes->post('enrollments', 'EnrollmentController::create');
    $routes->delete('enrollments/(:num)', 'EnrollmentController::delete/$1');
});
*/

// ==========================================================================
// SISTEM DPR (AKTIF)
// ==========================================================================

// === PUBLIC ROUTES (No Authentication Required) ===
// Public access to DPR member data (Read only)
$routes->group('public', ['namespace' => 'App\Controllers\Public'], static function ($routes) {
    // Public DPR member data view
    $routes->get('anggota', 'AnggotaController::index');
    $routes->get('anggota/(:any)', 'AnggotaController::index');
    
    // Public payroll data view  
    $routes->get('penggajian', 'PenggajianController::index');
    $routes->get('penggajian/(:any)', 'PenggajianController::index');
});

// Public API routes (Read only)
$routes->group('api/public', ['namespace' => 'App\Controllers\Api\Public'], static function ($routes) {
    // Anggota public API
    $routes->get('anggota', 'AnggotaController::index');
    $routes->get('anggota/(:num)', 'AnggotaController::show/$1');
    
    // Penggajian public API  
    $routes->get('penggajian', 'PenggajianController::index');
    $routes->get('penggajian/summary', 'PenggajianController::summary');
    $routes->get('penggajian/(:num)', 'PenggajianController::show/$1');
});

// DPR Admin routes with authentication filter
$routes->group('admin', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // DPR Admin routes
    $routes->group('dpr', ['namespace' => 'App\Controllers\DPR\Admin'], static function ($routes) {
        $routes->get('anggota', 'AnggotaController::spaShell');
        $routes->get('anggota/(:any)', 'AnggotaController::spaShell');
        $routes->get('komponengaji', 'KomponenGajiController::spaShell');
        $routes->get('komponengaji/(:any)', 'KomponenGajiController::spaShell');
    });
});

// Routes for authenticated DPR users
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Dashboard for all logged in users
    $routes->get('dashboard', 'DashboardController::index'); 
    
    // === DPR User & Admin Routes ===
    $routes->group('dpr', ['namespace' => 'App\Controllers\DPR'], static function ($routes) {
        $routes->get('penggajian', 'PenggajianController::spaShell');
        $routes->get('anggota', 'Admin\AnggotaController::spaShell');
        $routes->get('anggota/(:any)', 'Admin\AnggotaController::spaShell');
        $routes->get('komponengaji', 'Admin\KomponenGajiController::spaShell');
        $routes->get('komponengaji/(:any)', 'Admin\KomponenGajiController::spaShell');
        
        $routes->group('admin', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\DPR\Admin'], static function ($routes) {
            $routes->get('anggota', 'AnggotaController::spaShell');
            $routes->get('anggota/(:any)', 'AnggotaController::spaShell');
            $routes->get('komponengaji', 'KomponenGajiController::spaShell');
            $routes->get('komponengaji/(:any)', 'KomponenGajiController::spaShell');
        });
    });
});

// DPR API routes
$routes->group('api', ['filter' => 'auth', 'namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Penggajian API
    $routes->get('penggajian/summary', 'PenggajianController::summary');
    $routes->get('penggajian/available-anggota', 'PenggajianController::availableAnggota');
    $routes->get('penggajian/calculate/(:num)', 'PenggajianController::calculate/$1');
    $routes->get('penggajian/(:num)', 'PenggajianController::show/$1'); // Untuk detail
    $routes->post('penggajian', 'PenggajianController::create');     // Untuk menyimpan
    $routes->put('penggajian/(:num)', 'PenggajianController::update/$1'); // Untuk update
    $routes->delete('penggajian/(:num)', 'PenggajianController::delete/$1'); // Untuk menghapus
    
    // Komponen Gaji API
    $routes->get('komponengaji', 'KomponenGajiController::index');
    $routes->get('komponengaji/(:num)', 'KomponenGajiController::show/$1');
    $routes->get('komponengaji/by-jabatan/(:any)', 'KomponenGajiController::getByJabatan/$1');
    $routes->post('komponengaji', 'KomponenGajiController::create');
    $routes->put('komponengaji/(:num)', 'KomponenGajiController::update/$1');
    $routes->delete('komponengaji/(:num)', 'KomponenGajiController::delete/$1');
});

// Testing API routes without auth (temporary)
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Anggota API (temporary without auth for testing)
    $routes->get('anggota', 'AnggotaController::index');
    $routes->get('anggota/(:num)', 'AnggotaController::show/$1');
    $routes->post('anggota', 'AnggotaController::create');
    $routes->put('anggota/(:num)', 'AnggotaController::update/$1');
    $routes->delete('anggota/(:num)', 'AnggotaController::delete/$1');
    
    // Penggajian API (temporary without auth for testing)
    $routes->get('penggajian/summary', 'PenggajianController::summary');
    $routes->get('penggajian/available-anggota', 'PenggajianController::availableAnggota');
    $routes->get('penggajian/calculate/(:num)', 'PenggajianController::calculate/$1');
    $routes->get('penggajian/(:num)', 'PenggajianController::show/$1');
    $routes->post('penggajian', 'PenggajianController::create');
    $routes->put('penggajian/(:num)', 'PenggajianController::update/$1');
    $routes->delete('penggajian/(:num)', 'PenggajianController::delete/$1');
    
    // Komponen Gaji API (temporary without auth for testing)
    $routes->get('komponengaji', 'KomponenGajiController::index');
    $routes->get('komponengaji/(:num)', 'KomponenGajiController::show/$1');
    $routes->get('komponengaji/by-jabatan/(:any)', 'KomponenGajiController::getByJabatan/$1');
    $routes->post('komponengaji', 'KomponenGajiController::create');
    $routes->put('komponengaji/(:num)', 'KomponenGajiController::update/$1');
    $routes->delete('komponengaji/(:num)', 'KomponenGajiController::delete/$1');
});
