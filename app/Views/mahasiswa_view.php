<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) // atau htmlspecialchars($title) untuk native PHP ?></title>
    
    <link rel="stylesheet" href="<?= base_url('style.css') // atau BASE_URL . '/style.css' ?>">
</head>
<body>
    <h1><?= esc($title) // atau htmlspecialchars($title) ?></h1>

    <div class="search-form">
        <form action="<?= site_url('mahasiswa') // atau BASE_URL . '/mahasiswa' ?>" method="get">
            <input type="text" name="keyword" placeholder="Cari NIM, Nama, atau Umur..." value="<?= esc($keyword ?? '', 'attr') // atau htmlspecialchars($keyword ?? '') ?>">
            <button type="submit">Cari</button>
        </form>
    </div>

    <a href="<?= site_url('/mahasiswa/create') // atau BASE_URL . '/mahasiswa/create' ?>" class="button">Tambah</a>

    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Umur</th>
                <th class="th-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa as $mhs) : ?>
                <tr>
                    <td><?= esc($mhs['nim']) // atau htmlspecialchars($mhs['nim']) ?></td>
                    <td><?= esc($mhs['nama']) // atau htmlspecialchars($mhs['nama']) ?></td>
                    <td><?= esc($mhs['umur']) // atau htmlspecialchars($mhs['umur']) ?></td>
                    
                    <td class="action-links">
                        <a href="<?= site_url('mahasiswa/detail/' . $mhs['id']) // atau BASE_URL . '/mahasiswa/detail/' . $mhs['id'] ?>" class="button">Detail</a>
                        <a href="<?= site_url('mahasiswa/edit/' . $mhs['id']) // atau BASE_URL . '/mahasiswa/edit/' . $mhs['id'] ?>" class="button">Edit</a>
                        
                        <form action="<?= site_url('mahasiswa/delete/' . $mhs['id']) // atau BASE_URL . '/mahasiswa/delete/' . $mhs['id'] ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            <?= csrf_field() // Hapus baris ini jika menggunakan PHP Native ?>
                            <button type="submit" class="button button-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>