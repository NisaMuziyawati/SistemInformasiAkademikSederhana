<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('mahasiswa');

$user = currentUser();

$stmt = $pdo->prepare('
    SELECT mk.kode_mk, mk.nama_mk, mk.sks, n.nilai_huruf, n.bobot
    FROM krs k
    JOIN matakuliah mk ON k.matakuliah_id = mk.id
    JOIN nilai n ON n.krs_id = k.id
    WHERE k.mahasiswa_id = ? AND n.bobot IS NOT NULL
    ORDER BY mk.kode_mk
');
$stmt->execute([$user['ref_id']]);
$rows = $stmt->fetchAll();

$totalSks  = 0;
$totalMutu = 0;
foreach ($rows as $r) {
    $totalSks  += $r['sks'];
    $totalMutu += $r['sks'] * $r['bobot'];
}
$ipk = $totalSks > 0 ? round($totalMutu / $totalSks, 2) : 0;

$pageTitle = 'Hitung IPK';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-graph-up"></i> Perhitungan IPK</h4>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card purple">
            <div class="small">IPK Kumulatif</div>
            <div class="fs-1 fw-bold"><?= number_format($ipk, 2) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card blue">
            <div class="small">Total SKS Ditempuh</div>
            <div class="fs-1 fw-bold"><?= (int)$totalSks ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card green">
            <div class="small">Mata Kuliah Dinilai</div>
            <div class="fs-1 fw-bold"><?= count($rows) ?></div>
        </div>
    </div>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Rincian Perhitungan</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Nilai Huruf</th>
                    <th>Bobot</th>
                    <th>SKS &times; Bobot</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada nilai yang bisa dihitung.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= h($r['kode_mk']) ?></td>
                    <td><?= h($r['nama_mk']) ?></td>
                    <td><?= h($r['sks']) ?></td>
                    <td><span class="badge bg-info text-dark badge-huruf"><?= h($r['nilai_huruf']) ?></span></td>
                    <td><?= number_format($r['bobot'], 2) ?></td>
                    <td><?= number_format($r['sks'] * $r['bobot'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($rows): ?>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="2">Total</td>
                    <td><?= (int)$totalSks ?></td>
                    <td colspan="2"></td>
                    <td><?= number_format($totalMutu, 2) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <p class="text-muted small mt-3 mb-0">
        <i class="bi bi-info-circle"></i>
        IPK dihitung dengan rumus: <strong>IPK = (&Sigma; SKS &times; Bobot) &divide; (&Sigma; SKS)</strong>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
