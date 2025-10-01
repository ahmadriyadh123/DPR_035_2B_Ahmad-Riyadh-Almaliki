<?php
// File: public/buat_hash.php

// Masukkan password yang ingin Anda enkripsi di sini
$passwordAsli = 'admin';

// Membuat hash menggunakan algoritma default (bcrypt)
$hash = password_hash($passwordAsli, PASSWORD_DEFAULT);

echo "<h1>Password Hash Generator</h1>";
echo "<p>Password Asli: " . htmlspecialchars($passwordAsli) . "</p>";
echo "<p><strong>Hash yang Dihasilkan:</strong></p>";
echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($hash) . "</textarea>";
echo "<p><em>Salin hash di atas dan gunakan di database Anda.</em></p>";