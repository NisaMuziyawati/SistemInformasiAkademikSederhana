<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('admin');

$totalMahasiswa  = $pdo->query('SELECT COUNT(*) FROM mahasiswa')->fetchColumn();
$totalDosen      = $pdo->query('SELECT COUNT(*) FROM dosen')->fetchColumn();
$totalMatakuliah = $pdo->query('SELECT COUNT(*) FROM matakuliah')->fetchColumn();
$totalKrs        = $pdo->query('SELECT COUNT(*) FROM krs')->fetchColumn();

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2"></i> Dashboard Admin</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card blue">
            <div class="small">Total Mahasiswa</div>
            <div class="fs-3 fw-bold"><?= (int)$totalMahasiswa ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card green">
            <div class="small">Total Dosen</div>
            <div class="fs-3 fw-bold"><?= (int)$totalDosen ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card orange">
            <div class="small">Total Mata Kuliah</div>
            <div class="fs-3 fw-bold"><?= (int)$totalMatakuliah ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card purple">
            <div class="small">Total Entri KRS</div>
            <div class="fs-3 fw-bold"><?= (int)$totalKrs ?></div>
        </div>
    </div>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Menu Cepat</h6>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/mahasiswa.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-people"></i> Kelola Mahasiswa</a>
        <a href="<?= BASE_URL ?>/admin/dosen.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-badge"></i> Kelola Dosen</a>
        <a href="<?= BASE_URL ?>/admin/matakuliah.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-journal-bookmark"></i> Kelola Mata Kuliah</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
