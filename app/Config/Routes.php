<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - redirect to login page
$routes->get('/', 'AuthController::login');

// Authentication routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::processLogin');
$routes->get('/logout', 'AuthController::logout');

// Admin routes with authentication filter
$routes->group('admin', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Course Management
    $routes->get('courses', 'CourseController::spaShell');
    $routes->get('courses/(:any)', 'CourseController::spaShell');
    
    // Student Management
    $routes->get('students', 'StudentController::spaShell');
    $routes->get('students/(:any)', 'StudentController::spaShell');

    // Dosen Management
    $routes->get('dosens', 'DosenController::spaShell');
    $routes->get('dosens/(:any)', 'DosenController::spaShell');
    
    // === DPR Admin Routes ===
    $routes->get('dpr/anggota', '\App\Controllers\DPR\Admin\AnggotaController::spaShell');
    $routes->get('dpr/anggota/(:any)', '\App\Controllers\DPR\Admin\AnggotaController::spaShell');
    $routes->get('dpr/komponengaji', '\App\Controllers\DPR\Admin\GajiTunjanganController::spaShell');
    $routes->get('dpr/komponengaji/(:any)', '\App\Controllers\DPR\Admin\GajiTunjanganController::spaShell');
});

// Routes for authenticated users (general)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Dashboard for all logged in users
    $routes->get('dashboard', 'DashboardController::index'); 

    // Student course routes
    $routes->get('courses', 'CourseController::index');
    $routes->post('courses/enroll', 'CourseController::enrollMultiple');
    
    // === DPR User Routes ===
    $routes->get('dpr/anggota', '\App\Controllers\DPR\PenggajianController::spaShell');
    $routes->get('dpr/penggajian', '\App\Controllers\DPR\PenggajianController::spaShell');
});

// API routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Student API
    $routes->resource('students', ['controller' => 'StudentController']);
    $routes->resource('courses', ['controller' => 'CourseController']);
    $routes->get('available-courses/(:num)', 'CourseController::availableForStudent/$1');
    $routes->resource('enrollments', ['controller' => 'EnrollmentController']);
    $routes->get('student/courses-data', 'StudentViewController::getCoursesData');
    $routes->post('student/enroll-batch', 'StudentViewController::enrollBatch');

    // Dosen API
    $routes->resource('dosens', ['controller' => 'DosenController']);
    
    // === DPR API Routes ===
    $routes->resource('anggota', ['controller' => 'AnggotaController']);
    $routes->resource('komponen_gaji', ['controller' => 'KomponenGajiController']);
    $routes->resource('penggajian', ['controller' => 'PenggajianController']);
    $routes->get('penggajian/calculate/(:num)', 'PenggajianController::calculate/$1');
    $routes->get('penggajian/summary', 'PenggajianController::summary');
});
