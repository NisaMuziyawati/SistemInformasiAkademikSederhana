<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('dosen');

$user = currentUser();

$stmt = $pdo->prepare('
    SELECT mk.*,
           (SELECT COUNT(*) FROM krs WHERE matakuliah_id = mk.id) AS jumlah_mahasiswa
    FROM matakuliah mk
    WHERE mk.dosen_id = ?
    ORDER BY mk.kode_mk
');
$stmt->execute([$user['ref_id']]);
$mataKuliah = $stmt->fetchAll();

$pageTitle = 'Dashboard Dosen';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-speedometer2"></i> Dashboard Dosen</h4>

<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Mata Kuliah yang Diampu</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Jumlah Mahasiswa (KRS)</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$mataKuliah): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Kamu belum ditugaskan mengampu mata kuliah apapun.</td></tr>
            <?php endif; ?>
            <?php foreach ($mataKuliah as $mk): ?>
                <tr>
                    <td><?= h($mk['kode_mk']) ?></td>
                    <td><?= h($mk['nama_mk']) ?></td>
                    <td><?= h($mk['sks']) ?></td>
                    <td><span class="badge bg-secondary"><?= h($mk['semester_ajaran']) ?></span></td>
                    <td><?= (int)$mk['jumlah_mahasiswa'] ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/dosen/nilai.php?mk=<?= $mk['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square"></i> Input Nilai
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
