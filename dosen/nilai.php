<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/grade.php';
requireLogin('dosen');

$user = currentUser();
$message = '';
$error   = '';

// Pastikan mata kuliah yang dipilih benar-benar diampu oleh dosen ini
$mkId = (int)($_GET['mk'] ?? $_POST['mk_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM matakuliah WHERE id = ? AND dosen_id = ?');
$stmt->execute([$mkId, $user['ref_id']]);
$matakuliah = $stmt->fetch();

// Daftar mata kuliah yang diampu (untuk dropdown pilih)
$stmt = $pdo->prepare('SELECT * FROM matakuliah WHERE dosen_id = ? ORDER BY kode_mk');
$stmt->execute([$user['ref_id']]);
$daftarMk = $stmt->fetchAll();

// ---- SIMPAN NILAI ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $matakuliah) {
    $nilaiInput = $_POST['nilai'] ?? []; // [krs_id => nilai_angka]

    foreach ($nilaiInput as $krsId => $angka) {
        $krsId = (int)$krsId;
        if ($angka === '') continue;
        $angka = (float)$angka;
        if ($angka < 0 || $angka > 100) continue;

        [$huruf, $bobot] = convertNilai($angka);

        // Upsert nilai
        $stmt = $pdo->prepare('SELECT id FROM nilai WHERE krs_id = ?');
        $stmt->execute([$krsId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE nilai SET nilai_angka=?, nilai_huruf=?, bobot=? WHERE krs_id=?');
            $stmt->execute([$angka, $huruf, $bobot, $krsId]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO nilai (krs_id, nilai_angka, nilai_huruf, bobot) VALUES (?,?,?,?)');
            $stmt->execute([$krsId, $angka, $huruf, $bobot]);
        }
    }
    $message = 'Nilai berhasil disimpan.';
}

// ---- Ambil daftar mahasiswa yang mengambil MK ini beserta nilainya ----
$mahasiswaList = [];
if ($matakuliah) {
    $stmt = $pdo->prepare('
        SELECT k.id AS krs_id, m.nim, m.nama, n.nilai_angka, n.nilai_huruf
        FROM krs k
        JOIN mahasiswa m ON k.mahasiswa_id = m.id
        LEFT JOIN nilai n ON n.krs_id = k.id
        WHERE k.matakuliah_id = ?
        ORDER BY m.nama
    ');
    $stmt->execute([$matakuliah['id']]);
    $mahasiswaList = $stmt->fetchAll();
}

$pageTitle = 'Input Nilai';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-pencil-square"></i> Input Nilai Mahasiswa</h4>

<div class="card p-4 mb-4">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Pilih Mata Kuliah</label>
            <select name="mk" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php foreach ($daftarMk as $mk): ?>
                    <option value="<?= $mk['id'] ?>" <?= ($matakuliah && $matakuliah['id'] == $mk['id']) ? 'selected' : '' ?>>
                        <?= h($mk['kode_mk']) ?> - <?= h($mk['nama_mk']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<?php if ($matakuliah): ?>
<div class="card p-4">
    <h6 class="fw-bold mb-3"><?= h($matakuliah['kode_mk']) ?> - <?= h($matakuliah['nama_mk']) ?> (<?= h($matakuliah['sks']) ?> SKS)</h6>
    <form method="post">
        <input type="hidden" name="mk_id" value="<?= $matakuliah['id'] ?>">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th style="width:150px;">Nilai Angka (0-100)</th>
                        <th>Nilai Huruf Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$mahasiswaList): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada mahasiswa yang mengambil KRS untuk mata kuliah ini.</td></tr>
                <?php endif; ?>
                <?php foreach ($mahasiswaList as $mhs): ?>
                    <tr>
                        <td><?= h($mhs['nim']) ?></td>
                        <td><?= h($mhs['nama']) ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100"
                                   name="nilai[<?= $mhs['krs_id'] ?>]"
                                   value="<?= h($mhs['nilai_angka']) ?>"
                                   class="form-control form-control-sm">
                        </td>
                        <td>
                            <?php if ($mhs['nilai_huruf']): ?>
                                <span class="badge bg-info text-dark badge-huruf"><?= h($mhs['nilai_huruf']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Belum dinilai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($mahasiswaList): ?>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Nilai</button>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
