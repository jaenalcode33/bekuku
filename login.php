<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

if (bekuku_is_authenticated()) {
    header('Location: ' . bekuku_url());
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim((string) ($_POST['username'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare(
        "SELECT user_id, username, name, password_hash, role
         FROM users
         WHERE username = :username AND status = 'aktif'
         LIMIT 1"
    );
    $stmt->execute([':username' => $username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['bekuku_user'] = [
            'id' => (int) $account['user_id'],
            'username' => $account['username'],
            'name' => $account['name'],
            'role' => $account['role'],
        ];
        bekuku_audit('login', 'auth', (int) $account['user_id']);

        header('Location: ' . bekuku_url());
        exit;
    }

    bekuku_log('Failed login attempt', ['username' => $username]);
    bekuku_audit('login_failed', 'auth', null, ['username' => $username]);
    $error = 'Username atau password tidak sesuai.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BEKUKU POS</title>
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091717">
</head>
<body class="bekuku-auth-page">
    <main class="bekuku-auth-card">
        <a class="bekuku-auth-brand" href="<?= bekuku_url() ?>">
            <span class="bekuku-brand-mark"><i class="bi bi-snow2"></i></span>
            <span>BEKUKU <small>POINT OF SALE</small></span>
        </a>
        <div class="bekuku-auth-heading">
            <p class="bekuku-eyebrow">RUANG KERJA BEKUKU</p>
            <h1>Selamat datang kembali</h1>
            <p>Masuk sesuai peran untuk membuka menu kerja Anda.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="bekuku-auth-error"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" class="bekuku-login-form"><?= bekuku_csrf_field() ?>
            <label for="username">Peran pengguna</label>
            <select id="username" name="username" required>
                <option value="">Pilih peran</option>
                <option value="admin">Administrator</option>
                <option value="kasir">Kasir</option>
                <option value="gudang">Petugas Gudang</option>
            </select>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Masukkan password" required>
            <button type="submit"><span>Masuk ke dashboard</span><i class="bi bi-arrow-right"></i></button>
        </form>
        <a class="bekuku-auth-back" href="<?= bekuku_url() ?>"><i class="bi bi-arrow-left"></i> Kembali ke halaman utama</a>
    </main>
</body>
</html>
