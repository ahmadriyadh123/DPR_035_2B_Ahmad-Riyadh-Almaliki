<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bagian head: meta tags, title dinamis, viewport, Bootstrap CSS, dan gaya khusus -->
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
            display: none;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .is-invalid + .error-message {
            display: block;
        }
    </style>
</head>
<body>

<!-- Container utama untuk halaman login dengan styling khusus -->
<div class="login-container">
    <h2 class="text-center mb-4">Login</h2>

    <!-- Tampilkan pesan error dari flashdata jika ada -->
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- Form login dengan validasi client-side -->
    <form id="login-form" action="/login" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control">
            <div class="error-message"></div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

<!-- Script JavaScript untuk validasi form login sebelum submit -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    loginForm.addEventListener('submit', function(event) {
        resetValidation();
        let isValid = true;

        if (usernameInput.value.trim() === '') {
            showError(usernameInput, 'Username tidak boleh kosong.');
            isValid = false;
        }

        if (passwordInput.value.trim() === '') {
            showError(passwordInput, 'Password tidak boleh kosong.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorContainer = inputElement.nextElementSibling;
        errorContainer.textContent = message;
    }

    function resetValidation() {
        loginForm.querySelectorAll('.is-invalid').forEach(f => f.classList.remove('is-invalid'));
        loginForm.querySelectorAll('.error-message').forEach(m => m.textContent = '');
    }
});
</script>

</body>
</html>