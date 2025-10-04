<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-url" content="<?= site_url('api/public') ?>">
    <title><?= $title ?? 'Akses Publik - Data DPR' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, 0.7);
            border-radius: .25rem .25rem 0 0;
        }

        .navbar-nav .nav-link.active {
            font-weight: bold;
            color: #ffffff !important; 
            background-color: #495057;
        }

        .navbar-nav .nav-link:hover {
            color: #e9ecef !important; 
            background-color: #343a40;
        }
        
        .public-notice {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .read-only-badge {
            background-color: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="main-nav">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url('/') ?>">
                <i class="fas fa-building"></i> DPR Data Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('public/anggota') ?>">
                            <i class="fas fa-users"></i> Data Anggota DPR
                            <span class="read-only-badge">Read Only</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('public/penggajian') ?>">
                            <i class="fas fa-money-bill-wave"></i> Data Penggajian
                            <span class="read-only-badge">Read Only</span>
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('login') ?>">
                            <i class="fas fa-sign-in-alt"></i> Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Public Notice -->
    <div class="container mt-3">
        <div class="public-notice">
            <h6 class="mb-2"><i class="fas fa-info-circle"></i> Akses Publik - Informasi Penting</h6>
            <p class="mb-0">
                Anda sedang mengakses portal data publik DPR. Data yang ditampilkan bersifat <strong>read-only</strong> 
                dan tidak memerlukan autentikasi. Untuk melakukan pengelolaan data, silakan login sebagai administrator.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('pageScripts') ?>
    
    <script>
        // Set active navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>