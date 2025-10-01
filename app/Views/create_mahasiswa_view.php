<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('style.css') ?>"> 
</head>
<body>

    <h1><?= esc($title) ?></h1>

    <form action="<?= site_url('/mahasiswa/store') ?>" method="post">
        <?= csrf_field() ?> <label for="nim">NIM</label>
        <input type="text" name="nim" id="nim" required>

        <label for="nama">Nama</label>
        <input type="text" name="nama" id="nama" required>

        <label for="umur">Umur</label>
        <input type="text" name="umur" id="umur">

        <button type="submit">Simpan</button>
    </form>

    <br>
    <a href="<?= site_url('mahasiswa') ?>">Kembali</a>

</body>
</html>