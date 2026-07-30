<?php
// $pageTitle bisa di-set di halaman pemanggil sebelum include file ini
$pageTitle = $pageTitle ?? 'Sistem Informasi Akademik';
$user = function_exists('currentUser') ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | SIA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php if ($user && $user['id']): ?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/<?= h($user['role']) ?>/dashboard.php">
            <i class="bi bi-mortarboard-fill"></i> SIA Kampus
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/mahasiswa.php"><i class="bi bi-people"></i> Mahasiswa</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dosen.php"><i class="bi bi-person-badge"></i> Dosen</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/matakuliah.php"><i class="bi bi-journal-bookmark"></i> Mata Kuliah</a></li>
                <?php elseif ($user['role'] === 'dosen'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dosen/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dosen/nilai.php"><i class="bi bi-pencil-square"></i> Input Nilai</a></li>
                <?php elseif ($user['role'] === 'mahasiswa'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mahasiswa/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mahasiswa/krs.php"><i class="bi bi-card-checklist"></i> Isi KRS</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mahasiswa/nilai.php"><i class="bi bi-clipboard-data"></i> Nilai</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mahasiswa/ipk.php"><i class="bi bi-graph-up"></i> IPK</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text text-white-50 me-3">
                <i class="bi bi-person-circle"></i> <?= h($user['nama']) ?> (<?= h(ucfirst($user['role'])) ?>)
            </span>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container-fluid px-4 py-4">
