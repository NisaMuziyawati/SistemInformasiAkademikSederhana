<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('admin');

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id       = $_POST['id'] ?? '';
    $kode     = trim($_POST['kode_mk'] ?? '');
    $nama     = trim($_POST['nama_mk'] ?? '');
    $sks      = (int)($_POST['sks'] ?? 0);
    $dosenId  = $_POST['dosen_id'] ?: null;
    $semester = $_POST['semester_ajaran'] ?? 'Ganjil';

    if ($kode === '' || $nama === '' || $sks <= 0) {
        $error = 'Kode, nama mata kuliah, dan SKS wajib diisi dengan benar.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE matakuliah SET kode_mk=?, nama_mk=?, sks=?, dosen_id=?, semester_ajaran=? WHERE id=?');
                $stmt->execute([$kode, $nama, $sks, $dosenId, $semester, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO matakuliah (kode_mk, nama_mk, sks, dosen_id, semester_ajaran) VALUES (?,?,?,?,?)');
                $stmt->execute([$kode, $nama, $sks, $dosenId, $semester]);
            }
            $message = 'Data mata kuliah berhasil disimpan.';
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: kode mata kuliah mungkin sudah dipakai.';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM matakuliah WHERE id=?');
    $stmt->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/matakuliah.php?deleted=1');
    exit;
}
if (isset($_GET['deleted'])) {
    $message = 'Data mata kuliah berhasil dihapus.';
}

$dosenList = $pdo->query('SELECT id, nama FROM dosen ORDER BY nama')->fetchAll();
$list = $pdo->query('
    SELECT mk.*, d.nama AS nama_dosen
    FROM matakuliah mk
    LEFT JOIN dosen d ON mk.dosen_id = d.id
    ORDER BY mk.kode_mk ASC
')->fetchAll();

$pageTitle = 'Kelola Mata Kuliah';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-bookmark"></i> Kelola Mata Kuliah</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Tambah Mata Kuliah
    </button>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Dosen Pengampu</th>
                    <th>Semester</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data mata kuliah.</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= h($row['kode_mk']) ?></td>
                    <td><?= h($row['nama_mk']) ?></td>
                    <td><?= h($row['sks']) ?></td>
                    <td><?= h($row['nama_dosen'] ?? '-') ?></td>
                    <td><span class="badge bg-secondary"><?= h($row['semester_ajaran']) ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick='editForm(<?= json_encode($row) ?>)'
                                data-bs-toggle="modal" data-bs-target="#formModal">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="f_id">
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Kuliah</label>
                        <input type="text" name="kode_mk" id="f_kode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" id="f_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SKS</label>
                        <input type="number" name="sks" id="f_sks" class="form-control" min="1" max="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dosen Pengampu</label>
                        <select name="dosen_id" id="f_dosen" class="form-select">
                            <option value="">-- Belum ditentukan --</option>
                            <?php foreach ($dosenList as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= h($d['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester_ajaran" id="f_semester" class="form-select">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Tambah Mata Kuliah';
    document.getElementById('f_id').value = '';
    document.getElementById('f_kode').value = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_sks').value = '';
    document.getElementById('f_dosen').value = '';
    document.getElementById('f_semester').value = 'Ganjil';
}
function editForm(data) {
    document.getElementById('modalTitle').innerText = 'Edit Mata Kuliah';
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_kode').value = data.kode_mk;
    document.getElementById('f_nama').value = data.nama_mk;
    document.getElementById('f_sks').value = data.sks;
    document.getElementById('f_dosen').value = data.dosen_id || '';
    document.getElementById('f_semester').value = data.semester_ajaran;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
