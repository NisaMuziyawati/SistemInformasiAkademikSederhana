<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireLogin('admin');

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id    = $_POST['id'] ?? '';
    $nip   = trim($_POST['nip'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nip === '' || $nama === '') {
        $error = 'NIP dan Nama wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($id) {
                $stmt = $pdo->prepare('UPDATE dosen SET nip=?, nama=?, email=? WHERE id=?');
                $stmt->execute([$nip, $nama, $email, $id]);

                $stmt = $pdo->prepare("UPDATE users SET username=? WHERE role='dosen' AND ref_id=?");
                $stmt->execute([$nip, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO dosen (nip, nama, email) VALUES (?,?,?)');
                $stmt->execute([$nip, $nama, $email]);
                $newId = $pdo->lastInsertId();

                $hash = password_hash($nip, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, ref_id) VALUES (?,?,'dosen',?)");
                $stmt->execute([$nip, $hash, $newId]);
            }

            $pdo->commit();
            $message = 'Data dosen berhasil disimpan. (Password akun baru = NIP)';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan: NIP mungkin sudah terdaftar.';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM dosen WHERE id=?');
    $stmt->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/dosen.php?deleted=1');
    exit;
}
if (isset($_GET['deleted'])) {
    $message = 'Data dosen berhasil dihapus.';
}

$list = $pdo->query('SELECT * FROM dosen ORDER BY nama ASC')->fetchAll();

$pageTitle = 'Kelola Dosen';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> Kelola Data Dosen</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Tambah Dosen
    </button>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data dosen.</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= h($row['nip']) ?></td>
                    <td><?= h($row['nama']) ?></td>
                    <td><?= h($row['email']) ?></td>
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
                    <h5 class="modal-title" id="modalTitle">Tambah Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="f_id">
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip" id="f_nip" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="f_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="f_email" class="form-control">
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
    document.getElementById('modalTitle').innerText = 'Tambah Dosen';
    document.getElementById('f_id').value = '';
    document.getElementById('f_nip').value = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_email').value = '';
}
function editForm(data) {
    document.getElementById('modalTitle').innerText = 'Edit Dosen';
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_nip').value = data.nip;
    document.getElementById('f_nama').value = data.nama;
    document.getElementById('f_email').value = data.email;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
