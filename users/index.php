<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
bekuku_require_permission('users.manage');

$error = '';
$editing = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare('SELECT user_id, username, name, role, status FROM users WHERE user_id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (int) ($_POST['user_id'] ?? 0);

        if ($action === 'delete') {
            $id = bekuku_validate_int($id, 'Pengguna', 1);
            if ($id === (int) (bekuku_user()['id'] ?? 0)) {
                throw new InvalidArgumentException('Pengguna yang sedang digunakan tidak dapat dihapus.');
            }
            $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
            $stmt->execute([$id]);
            bekuku_audit('delete', 'user', $id);
        } else {
            $name = bekuku_validate_string($_POST['name'] ?? '', 'Nama', 100);
            $username = strtolower(bekuku_validate_string($_POST['username'] ?? '', 'Username', 50));
            if (!preg_match('/^[a-z0-9._-]+$/', $username)) {
                throw new InvalidArgumentException('Username hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.');
            }
            $role = bekuku_validate_enum($_POST['role'] ?? '', 'Peran', ['admin', 'kasir', 'gudang']);
            $status = bekuku_validate_enum($_POST['status'] ?? 'aktif', 'Status', ['aktif', 'nonaktif']);
            $password = (string) ($_POST['password'] ?? '');
            if ($id > 0) {
                $sql = 'UPDATE users SET username = ?, name = ?, role = ?, status = ?';
                $params = [$username, $name, $role, $status];
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        throw new InvalidArgumentException('Password minimal 8 karakter.');
                    }
                    $sql .= ', password_hash = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE user_id = ?';
                $params[] = $id;
                $conn->prepare($sql)->execute($params);
                bekuku_audit('update', 'user', $id);
            } else {
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('Password minimal 8 karakter.');
                }
                $stmt = $conn->prepare(
                    'INSERT INTO users (username, name, password_hash, role, status) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$username, $name, password_hash($password, PASSWORD_DEFAULT), $role, $status]);
                bekuku_audit('create', 'user', (int) $conn->lastInsertId());
            }
        }
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e instanceof InvalidArgumentException ? $e->getMessage() : 'Data pengguna gagal disimpan.';
        bekuku_log('User management failed', ['error' => $e->getMessage()]);
    }
}

$users = $conn->query('SELECT user_id, username, name, role, status, created_at FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengguna - BEKUKU POS</title>
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="app-main"><div class="app-content p-4">
        <div class="container-fluid">
            <h3>Manajemen Pengguna</h3>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div class="card mb-4"><div class="card-header"><h5><?= $editing ? 'Ubah pengguna' : 'Tambah pengguna' ?></h5></div>
                <form method="post"><input type="hidden" name="action" value="save">
                    <?php if ($editing): ?><input type="hidden" name="user_id" value="<?= (int) $editing['user_id'] ?>"><?php endif; ?>
                    <?= bekuku_csrf_field() ?>
                    <div class="card-body row g-3">
                        <div class="col-md-3"><label class="form-label">Username</label><input class="form-control" name="username" required value="<?= htmlspecialchars($editing['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Nama</label><input class="form-control" name="name" required value="<?= htmlspecialchars($editing['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Peran</label><select class="form-select" name="role"><?php foreach (['admin', 'kasir', 'gudang'] as $role): ?><option value="<?= $role ?>" <?= ($editing['role'] ?? '') === $role ? 'selected' : '' ?>><?= bekuku_role_label($role) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="aktif" <?= ($editing['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= ($editing['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
                        <div class="col-md-2"><label class="form-label">Password <?= $editing ? '(opsional)' : '' ?></label><input class="form-control" type="password" name="password" <?= $editing ? '' : 'required' ?> minlength="8"></div>
                    </div><div class="card-footer"><button class="btn btn-primary">Simpan</button><?php if ($editing): ?> <a class="btn btn-secondary" href="index.php">Batal</a><?php endif; ?></div>
                </form>
            </div>
            <div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Username</th><th>Nama</th><th>Peran</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
            <?php foreach ($users as $user): ?><tr><td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= bekuku_role_label($user['role']) ?></td><td><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?></td><td><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $user['user_id'] ?>">Ubah</a> <?php if ((int) $user['user_id'] !== (int) (bekuku_user()['id'] ?? 0)): ?><form method="post" class="d-inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>"><?= bekuku_csrf_field() ?><button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengguna ini?')">Hapus</button></form><?php endif; ?></td></tr><?php endforeach; ?>
            </tbody></table></div></div>
        </div>
    </div></main>
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
</div>
</body></html>
