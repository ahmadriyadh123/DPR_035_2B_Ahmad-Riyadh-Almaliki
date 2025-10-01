<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Test extends Controller
{
    public function db()
    {
        try {
            $db = Database::connect();
            
            $db->connect();
            
            echo "<h1> Koneksi Berhasil!</h1>";
            echo "Selamat! Aplikasi Anda sudah terhubung ke database: '<strong>" . $db->getDatabase() . "</strong>'.";

        } catch (\Throwable $e) {
            echo "<h1> Koneksi Gagal!</h1>";
            echo "Gagal terhubung ke database. Silakan periksa kembali konfigurasi di file <strong>.env</strong> Anda.";
            echo "<hr>";
            echo "Pesan Error: " . $e->getMessage();
        }
    }
}