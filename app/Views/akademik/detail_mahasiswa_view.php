<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('style.css') ?>"> 
</head>
<body>
    <h1><?= esc($title) ?></h1>
    
    <ul>
        <li><strong>NIM:</strong> <?= esc($mahasiswa['nim']) ?></li>
        <li><strong>Nama:</strong> <?= esc($mahasiswa['nama']) ?></li>
        <li><strong>Umur:</strong> <?= esc($mahasiswa['umur']) ?></li>
    </ul>

    <br>
    <a href="<?= site_url('mahasiswa') ?>">Kembali</a>
</body>
</html>