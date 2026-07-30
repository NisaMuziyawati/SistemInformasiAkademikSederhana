<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('mahasiswa');

$user = currentUser();

$stmt = $pdo->prepare('
    SELECT mk.kode_mk, mk.nama_mk, mk.sks, k.tahun_ajaran, k.semester,
           n.nilai_angka, n.nilai_huruf, n.bobot
    FROM krs k
    JOIN matakuliah mk ON k.matakuliah_id = mk.id
    LEFT JOIN nilai n ON n.krs_id = k.id
    WHERE k.mahasiswa_id = ?
    ORDER BY k.tahun_ajaran DESC, k.semester, mk.kode_mk
');
$stmt->execute([$user['ref_id']]);
$list = $stmt->fetchAll();

$pageTitle = 'Lihat Nilai';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-clipboard-data"></i> Daftar Nilai</h4>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Nilai Angka</th>
                    <th>Nilai Huruf</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data nilai.</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= h($row['kode_mk']) ?></td>
                    <td><?= h($row['nama_mk']) ?></td>
                    <td><?= h($row['sks']) ?></td>
                    <td><?= h($row['tahun_ajaran']) ?></td>
                    <td><?= h($row['semester']) ?></td>
                    <td><?= $row['nilai_angka'] !== null ? h($row['nilai_angka']) : '-' ?></td>
                    <td>
                        <?php if ($row['nilai_huruf']): ?>
                            <span class="badge bg-info text-dark badge-huruf"><?= h($row['nilai_huruf']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">Belum dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['bobot'] !== null ? h($row['bobot']) : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
