<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('mahasiswa');

$user = currentUser();
$message = '';
$error   = '';

$tahunAjaranDefault = date('Y') . '/' . (date('Y') + 1);

// ---- TAMBAH KRS ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $mkId        = (int)($_POST['matakuliah_id'] ?? 0);
    $tahunAjaran = trim($_POST['tahun_ajaran'] ?? $tahunAjaranDefault);
    $semester    = $_POST['semester'] ?? 'Ganjil';

    if (!$mkId) {
        $error = 'Silakan pilih mata kuliah.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO krs (mahasiswa_id, matakuliah_id, tahun_ajaran, semester) VALUES (?,?,?,?)');
            $stmt->execute([$user['ref_id'], $mkId, $tahunAjaran, $semester]);
            $message = 'Mata kuliah berhasil ditambahkan ke KRS.';
        } catch (PDOException $e) {
            $error = 'Mata kuliah ini sudah ada di KRS kamu untuk tahun ajaran & semester tersebut.';
        }
    }
}

// ---- HAPUS KRS (hanya jika belum dinilai) ----
if (isset($_GET['delete'])) {
    $krsId = (int)$_GET['delete'];

    $stmt = $pdo->prepare('SELECT k.id FROM krs k
        LEFT JOIN nilai n ON n.krs_id = k.id
        WHERE k.id = ? AND k.mahasiswa_id = ? AND n.id IS NULL');
    $stmt->execute([$krsId, $user['ref_id']]);

    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM krs WHERE id = ?');
        $stmt->execute([$krsId]);
        header('Location: ' . BASE_URL . '/mahasiswa/krs.php?deleted=1');
        exit;
    } else {
        header('Location: ' . BASE_URL . '/mahasiswa/krs.php?error=hasgrade');
        exit;
    }
}
if (isset($_GET['deleted'])) $message = 'Mata kuliah berhasil dihapus dari KRS.';
if (($_GET['error'] ?? '') === 'hasgrade') $error = 'Tidak bisa menghapus, mata kuliah ini sudah memiliki nilai.';

// ---- Data mata kuliah yang belum diambil ----
$stmt = $pdo->prepare('
    SELECT mk.* FROM matakuliah mk
    WHERE mk.id NOT IN (SELECT matakuliah_id FROM krs WHERE mahasiswa_id = ?)
    ORDER BY mk.kode_mk
');
$stmt->execute([$user['ref_id']]);
$mkTersedia = $stmt->fetchAll();

// ---- Daftar KRS mahasiswa ----
$stmt = $pdo->prepare('
    SELECT k.id AS krs_id, k.tahun_ajaran, k.semester, mk.kode_mk, mk.nama_mk, mk.sks,
           n.nilai_huruf
    FROM krs k
    JOIN matakuliah mk ON k.matakuliah_id = mk.id
    LEFT JOIN nilai n ON n.krs_id = k.id
    WHERE k.mahasiswa_id = ?
    ORDER BY k.tahun_ajaran DESC, k.semester, mk.kode_mk
');
$stmt->execute([$user['ref_id']]);
$krsList = $stmt->fetchAll();

$pageTitle = 'Isi KRS';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-card-checklist"></i> Kartu Rencana Studi (KRS)</h4>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Tambah Mata Kuliah</h6>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-md-5">
            <label class="form-label">Mata Kuliah</label>
            <select name="matakuliah_id" class="form-select" required>
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php foreach ($mkTersedia as $mk): ?>
                    <option value="<?= $mk['id'] ?>"><?= h($mk['kode_mk']) ?> - <?= h($mk['nama_mk']) ?> (<?= $mk['sks'] ?> SKS)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tahun Ajaran</label>
            <input type="text" name="tahun_ajaran" class="form-control" value="<?= h($tahunAjaranDefault) ?>" placeholder="2025/2026" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select">
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Daftar KRS Kamu</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Nilai</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$krsList): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada mata kuliah di KRS.</td></tr>
            <?php endif; ?>
            <?php foreach ($krsList as $row): ?>
                <tr>
                    <td><?= h($row['kode_mk']) ?></td>
                    <td><?= h($row['nama_mk']) ?></td>
                    <td><?= h($row['sks']) ?></td>
                    <td><?= h($row['tahun_ajaran']) ?></td>
                    <td><?= h($row['semester']) ?></td>
                    <td>
                        <?php if ($row['nilai_huruf']): ?>
                            <span class="badge bg-info text-dark badge-huruf"><?= h($row['nilai_huruf']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">Belum dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if (!$row['nilai_huruf']): ?>
                            <a href="?delete=<?= $row['krs_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
