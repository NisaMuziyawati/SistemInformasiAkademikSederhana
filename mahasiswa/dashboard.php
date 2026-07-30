<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('mahasiswa');

$user = currentUser();

$stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ?');
$stmt->execute([$user['ref_id']]);
$mhs = $stmt->fetch();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM krs WHERE mahasiswa_id = ?');
$stmt->execute([$user['ref_id']]);
$totalKrs = $stmt->fetchColumn();

$stmt = $pdo->prepare('
    SELECT COUNT(*) FROM krs k
    JOIN nilai n ON n.krs_id = k.id
    WHERE k.mahasiswa_id = ? AND n.nilai_huruf IS NOT NULL
');
$stmt->execute([$user['ref_id']]);
$totalDinilai = $stmt->fetchColumn();

// Hitung IPK sederhana (sks * bobot) / total sks, dari nilai yang sudah masuk
$stmt = $pdo->prepare('
    SELECT mk.sks, n.bobot
    FROM krs k
    JOIN matakuliah mk ON k.matakuliah_id = mk.id
    JOIN nilai n ON n.krs_id = k.id
    WHERE k.mahasiswa_id = ? AND n.bobot IS NOT NULL
');
$stmt->execute([$user['ref_id']]);
$rows = $stmt->fetchAll();

$totalSks = 0;
$totalMutu = 0;
foreach ($rows as $r) {
    $totalSks  += $r['sks'];
    $totalMutu += $r['sks'] * $r['bobot'];
}
$ipk = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0;

$pageTitle = 'Dashboard Mahasiswa';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2"></i> Dashboard Mahasiswa</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card blue">
            <div class="small">NIM</div>
            <div class="fs-5 fw-bold"><?= h($mhs['nim']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card green">
            <div class="small">Mata Kuliah Diambil</div>
            <div class="fs-3 fw-bold"><?= (int)$totalKrs ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card orange">
            <div class="small">Sudah Dinilai</div>
            <div class="fs-3 fw-bold"><?= (int)$totalDinilai ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card purple">
            <div class="small">IPK Saat Ini</div>
            <div class="fs-3 fw-bold"><?= number_format($ipk, 2) ?></div>
        </div>
    </div>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Data Diri</h6>
    <table class="table table-borderless mb-0">
        <tr><td class="text-muted" style="width:180px;">Nama</td><td>: <?= h($mhs['nama']) ?></td></tr>
        <tr><td class="text-muted">NIM</td><td>: <?= h($mhs['nim']) ?></td></tr>
        <tr><td class="text-muted">Program Studi</td><td>: <?= h($mhs['prodi']) ?></td></tr>
        <tr><td class="text-muted">Angkatan</td><td>: <?= h($mhs['angkatan']) ?></td></tr>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
