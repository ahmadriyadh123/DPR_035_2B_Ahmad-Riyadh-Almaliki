<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bagian head: meta tags untuk charset, viewport, CSRF tokens, title dinamis, Bootstrap CSS, SweetAlert JS, dan gaya khusus untuk navigasi -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="X-CSRF-TOKEN" content="<?= csrf_token() ?>">
    <meta name="X-CSRF-HEADER" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'CI News' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #main-nav .navbar-nav .nav-link.active {
            font-weight: bold;
            color: #ffffff !important; 
            border-bottom: 3px solid #0d6efd; 
            padding-bottom: 5px; /* (Opsional) beri sedikit ruang agar garis tidak terlalu mepet */
        }

        #main-nav .navbar-nav .nav-link:hover {
            color: #e9ecef !important; 
        }
    </style>
</head>
<body>

<!-- Navigasi utama dengan Bootstrap navbar, brand, toggler, dan menu yang digenerate -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('/') ?>">CI-APP</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <?= generate_menu() ?>
        
    </div>
</nav>

<!-- Container utama untuk konten halaman, merender section 'content' -->
<div class="container mt-4">
    <?= $this->renderSection('content') ?>
</div>

<!-- Script eksternal: Bootstrap JS bundle dan utils.js, diikuti section 'pageScripts' -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('js/utils.js') ?>"></script> <?= $this->renderSection('pageScripts') ?>
<?= $this->renderSection('pageScripts') ?>
<!-- Placeholder untuk modal yang mungkin dimuat secara dinamis -->
<div id="modal-placeholder"></div>

</body>
</html>