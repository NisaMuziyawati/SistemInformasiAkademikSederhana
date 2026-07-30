<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('admin');

$message = '';
$error   = '';

// ---- TAMBAH / EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id     = $_POST['id'] ?? '';
    $nim    = trim($_POST['nim'] ?? '');
    $nama   = trim($_POST['nama'] ?? '');
    $prodi  = trim($_POST['prodi'] ?? '');
    $angkatan = (int)($_POST['angkatan'] ?? 0);

    if ($nim === '' || $nama === '' || $prodi === '' || $angkatan === 0) {
        $error = 'Semua field wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($id) {
                // Update
                $stmt = $pdo->prepare('UPDATE mahasiswa SET nim=?, nama=?, prodi=?, angkatan=? WHERE id=?');
                $stmt->execute([$nim, $nama, $prodi, $angkatan, $id]);

                // Sinkronkan username di tabel users jika NIM berubah
                $stmt = $pdo->prepare("UPDATE users SET username=? WHERE role='mahasiswa' AND ref_id=?");
                $stmt->execute([$nim, $id]);
            } else {
                // Insert mahasiswa baru
                $stmt = $pdo->prepare('INSERT INTO mahasiswa (nim, nama, prodi, angkatan) VALUES (?,?,?,?)');
                $stmt->execute([$nim, $nama, $prodi, $angkatan]);
                $newId = $pdo->lastInsertId();

                // Buat akun login otomatis (username = NIM, password default = NIM)
                $hash = password_hash($nim, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, ref_id) VALUES (?,?,'mahasiswa',?)");
                $stmt->execute([$nim, $hash, $newId]);
            }

            $pdo->commit();
            $message = 'Data mahasiswa berhasil disimpan. (Password akun baru = NIM)';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan: NIM mungkin sudah terdaftar.';
        }
    }
}

// ---- HAPUS ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id=?');
    $stmt->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/mahasiswa.php?deleted=1');
    exit;
}
if (isset($_GET['deleted'])) {
    $message = 'Data mahasiswa berhasil dihapus.';
}

// ---- Data untuk edit ----
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

// ---- List semua mahasiswa ----
$list = $pdo->query('SELECT * FROM mahasiswa ORDER BY nama ASC')->fetchAll();

$pageTitle = 'Kelola Mahasiswa';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-people"></i> Kelola Data Mahasiswa</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Tambah Mahasiswa
    </button>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Program Studi</th>
                    <th>Angkatan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= h($row['nim']) ?></td>
                    <td><?= h($row['nama']) ?></td>
                    <td><?= h($row['prodi']) ?></td>
                    <td><?= h($row['angkatan']) ?></td>
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

<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="f_id">
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" id="f_nim" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="f_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" name="prodi" id="f_prodi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Angkatan</label>
                        <input type="number" name="angkatan" id="f_angkatan" class="form-control" min="2000" max="2100" required>
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
    document.getElementById('modalTitle').innerText = 'Tambah Mahasiswa';
    document.getElementById('f_id').value = '';
    document.getElementById('f_nim').value = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_prodi').value = '';
    document.getElementById('f_angkatan').value = '';
}
function editForm(data) {
    document.getElementById('modalTitle').innerText = 'Edit Mahasiswa';
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_nim').value = data.nim;
    document.getElementById('f_nama').value = data.nama;
    document.getElementById('f_prodi').value = data.prodi;
    document.getElementById('f_angkatan').value = data.angkatan;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
