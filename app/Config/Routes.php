<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// $routes->get('/login', 'AuthController::login');
// $routes->post('/login', 'AuthController::processLogin');
// $routes->get('/logout', 'AuthController::logout');

// // --- PERBARUI BAGIAN INI ---
// $routes->group('admin', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
//     $routes->get('berita', '\App\Controllers\Berita::index');
//     $routes->get('berita/create', '\App\Controllers\Berita::create');
//     $routes->post('berita/create', '\App\Controllers\Berita::store');
//     $routes->get('berita/edit/(:num)', '\App\Controllers\Berita::edit/$1');
//     $routes->post('berita/edit/(:num)', '\App\Controllers\Berita::update/$1');
//     $routes->get('berita/delete/(:num)', '\App\Controllers\Berita::delete/$1');

//     // --- TAMBAHKAN RUTE BARU DI BAWAH INI ---

//     // Course Management
//     $routes->get('courses', 'CourseController::spaShell');
//     $routes->get('courses/(:any)', 'CourseController::spaShell');
//     // Student Management
//     $routes->get('students', 'StudentController::spaShell');
//     $routes->get('students/(:any)', 'StudentController::spaShell');


//     $routes->get('admin/resource', 'Admin\GenericAdminController::index');
//     $routes->get('admin/resource/create', 'Admin\GenericAdminController::create');
//     $routes->post('admin/resource/store', 'Admin\GenericAdminController::store');
//     $routes->get('admin/resource/edit/(:num)', 'Admin\GenericAdminController::edit/$1');
//     $routes->post('admin/resource/update/(:num)', 'Admin\GenericAdminController::update/$1');
//     $routes->get('admin/resource/delete/(:num)', 'Admin\GenericAdminController::delete/$1');

//     $routes->get('dosens', 'DosenController::spaShell');
//     $routes->get('dosens/(:any)', 'DosenController::spaShell');
// });

// // Route untuk pengguna yang sudah login (umum)
// $routes->group('', ['filter' => 'auth'], static function ($routes) {
//     // Contoh halaman dashboard untuk semua user yang login
//     $routes->get('dashboard', 'DashboardController::index'); 

//     // Rute untuk Courses Mahasiswa
//     $routes->get('courses', 'CourseController::index');
//     $routes->post('courses/enroll', 'CourseController::enrollMultiple');

// });

// $routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
//     // Rute ini akan menghasilkan:
//     // GET /api/students -> Api\StudentController::index()
//     // GET /api/students/1 -> Api\StudentController::show(1)
//     $routes->resource('students', ['controller' => 'StudentController']);
//     $routes->resource('courses', ['controller' => 'CourseController']);
//     $routes->get('available-courses/(:num)', 'CourseController::availableForStudent/$1');
//     $routes->resource('enrollments', ['controller' => 'EnrollmentController']);
//     $routes->get('student/courses-data', 'StudentViewController::getCoursesData');
//     $routes->post('student/enroll-batch', 'StudentViewController::enrollBatch');


//     // API
//     $routes->resource('api/resource', ['controller' => 'Api\GenericApiController']);

//     // Web
//     $routes->get('admin/resource', 'Admin\GenericAdminController::spaShell');
//     $routes->get('admin/resource/(:any)', 'Admin\GenericAdminController::spaShell');
// $routes->resource('dosens', ['controller' => 'DosenController']);


$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::processLogin');
$routes->get('/logout', 'AuthController::logout');

// --- PERBARUI BAGIAN INI ---
$routes->group('admin', ['filter' => 'auth:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {


    // Course Management
    $routes->get('courses', 'CourseController::spaShell');
    $routes->get('courses/(:any)', 'CourseController::spaShell');
    // Student Management
    $routes->get('students', 'StudentController::spaShell');
    $routes->get('students/(:any)', 'StudentController::spaShell');


    $routes->get('admin/resource', 'Admin\GenericAdminController::index');
    $routes->get('admin/resource/create', 'Admin\GenericAdminController::create');
    $routes->post('admin/resource/store', 'Admin\GenericAdminController::store');
    $routes->get('admin/resource/edit/(:num)', 'Admin\GenericAdminController::edit/$1');
    $routes->post('admin/resource/update/(:num)', 'Admin\GenericAdminController::update/$1');
    $routes->get('admin/resource/delete/(:num)', 'Admin\GenericAdminController::delete/$1');

    $routes->get('dosens', 'DosenController::spaShell');
    $routes->get('dosens/(:any)', 'DosenController::spaShell');
});

// Route untuk pengguna yang sudah login (umum)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Contoh halaman dashboard untuk semua user yang login
    $routes->get('dashboard', 'DashboardController::index'); 

    // Rute untuk Courses Mahasiswa
    $routes->get('courses', 'CourseController::index');
    $routes->post('courses/enroll', 'CourseController::enrollMultiple');

});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Rute ini akan menghasilkan:
    // GET /api/students -> Api\StudentController::index()
    // GET /api/students/1 -> Api\StudentController::show(1)
    $routes->resource('students', ['controller' => 'StudentController']);
    $routes->resource('courses', ['controller' => 'CourseController']);
    $routes->get('available-courses/(:num)', 'CourseController::availableForStudent/$1');
    $routes->resource('enrollments', ['controller' => 'EnrollmentController']);
    $routes->get('student/courses-data', 'StudentViewController::getCoursesData');
    $routes->post('student/enroll-batch', 'StudentViewController::enrollBatch');


    // API
    $routes->resource('api/resource', ['controller' => 'Api\GenericApiController']);

    // Web
    $routes->get('admin/resource', 'Admin\GenericAdminController::spaShell');
    $routes->get('admin/resource/(:any)', 'Admin\GenericAdminController::spaShell');
$routes->resource('dosens', ['controller' => 'DosenController']);
});
