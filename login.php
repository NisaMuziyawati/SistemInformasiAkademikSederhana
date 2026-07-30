<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Jika sudah login, langsung arahkan ke dashboard masing-masing
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/' . $_SESSION['role'] . '/dashboard.php');
    exit;
}

$error = '';

if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'Kamu tidak memiliki akses ke halaman tersebut.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['ref_id']  = $user['ref_id'];

            // Ambil nama tampilan sesuai role
            if ($user['role'] === 'admin') {
                $_SESSION['nama'] = 'Administrator';
            } elseif ($user['role'] === 'dosen') {
                $s = $pdo->prepare('SELECT nama FROM dosen WHERE id = ?');
                $s->execute([$user['ref_id']]);
                $_SESSION['nama'] = $s->fetchColumn() ?: 'Dosen';
            } else {
                $s = $pdo->prepare('SELECT nama FROM mahasiswa WHERE id = ?');
                $s->execute([$user['ref_id']]);
                $_SESSION['nama'] = $s->fetchColumn() ?: 'Mahasiswa';
            }

            header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card p-4">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill" style="font-size:2.5rem;color:#2f5fdd;"></i>
                <h4 class="mt-2 fw-bold">Sistem Informasi Akademik</h4>
                <p class="text-muted small">Silakan masuk untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="admin / NIP / NIM" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>

            <hr>
            <p class="text-muted small mb-1">Akun demo (password: <code>password123</code>):</p>
            <ul class="text-muted small mb-0">
                <li>Admin &mdash; username: <code>admin</code></li>
                <li>Dosen &mdash; username: <code>dosen1</code> s/d <code>dosen6</code></li>
                <li>Mahasiswa &mdash; username: <code>21552001</code> s/d <code>21552006</code></li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
