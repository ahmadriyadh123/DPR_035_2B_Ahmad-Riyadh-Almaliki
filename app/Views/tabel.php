<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title> 
    <style>
        table { width: 50%; border-collapse: collapse; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>

    <h1><?= esc($title) ?></h1>

    <table>
        <thead>
            <tr>
                <th>NPM</th>
                <th>Nama</th>
                <th>Jurusan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa as $mhs) : ?>
                <tr>
                    <td><?= esc($mhs['npm']) ?></td>
                    <td><?= esc($mhs['nama']) ?></td>
                    <td><?= esc($mhs['jurusan']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>