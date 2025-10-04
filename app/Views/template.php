<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bagian head: meta tags untuk charset, viewport, CSRF tokens, title dinamis, Bootstrap CSS, SweetAlert JS, dan gaya khusus untuk navigasi -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="X-CSRF-TOKEN" content="<?= csrf_token() ?>">
    <meta name="X-CSRF-HEADER" content="<?= csrf_hash() ?>">
    <meta name="api-url" content="<?= site_url('api') ?>">
    <title><?= $title ?? 'CI News' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        /* Pastikan navbar collapse terlihat pada layar besar */
        @media (min-width: 992px) {
            .navbar-collapse {
                display: flex !important;
                flex-basis: auto;
            }
        }
        
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, 0.7);
            border-radius: .25rem .25rem 0 0;
        }

        #main-nav .navbar-nav .nav-link.active {
            font-weight: bold;
            color: #ffffff !important; 
            background-color: #495057;
        }

        #main-nav .navbar-nav .nav-link:hover {
            color: #e9ecef !important; 
            background-color: #343a40;
        }
        
        .dropdown-item.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .navbar-nav .dropdown-menu {
            background-color: #343a40;
        }
        .dropdown-item {
            color: rgba(255, 255, 255, 0.7);
        }
        .dropdown-item:hover {
            background-color: #495057;
            color: white;
        }
    </style>
</head>
<body data-api-url="<?= site_url('api') ?>">

<!-- Navigasi utama dengan Bootstrap navbar, brand, toggler, dan menu yang digenerate -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="main-nav">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('/') ?>">
            <i class="fas fa-graduation-cap"></i> CI-APP
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav nav-tabs border-0 ms-auto">
                <!-- Menu yang digenerate dari helper akan dimasukkan di sini -->
                <?= generate_menu_items() ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
    </div>
</nav>

<!-- Container utama untuk konten halaman, merender section 'content' -->
<div class="container mt-4">
    <?= $this->renderSection('content') ?>
</div>

<!-- Script eksternal: Bootstrap JS bundle dan utils.js, diikuti section 'pageScripts' -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('js/utils.js') ?>"></script>
<?= $this->renderSection('pageScripts') ?>
<div id="modal-placeholder"></div>

</body>
</html>