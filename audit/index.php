<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
bekuku_require_permission('users.manage');

$action = trim((string) ($_GET['action'] ?? ''));
$entity = trim((string) ($_GET['entity'] ?? ''));
$username = trim((string) ($_GET['username'] ?? ''));
$startDate = trim((string) ($_GET['start_date'] ?? ''));
$endDate = trim((string) ($_GET['end_date'] ?? ''));

$isDate = static function (string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
};

if (!$isDate($startDate)) {
    $startDate = '';
}
if (!$isDate($endDate)) {
    $endDate = '';
}

$conditions = [];
$params = [];

if ($action !== '') {
    $conditions[] = 'action = :action';
    $params[':action'] = $action;
}
if ($entity !== '') {
    $conditions[] = 'entity = :entity';
    $params[':entity'] = $entity;
}
if ($username !== '') {
    $conditions[] = 'username LIKE :username';
    $params[':username'] = '%' . $username . '%';
}
if ($startDate !== '') {
    $conditions[] = 'created_at >= :start_date';
    $params[':start_date'] = $startDate . ' 00:00:00';
}
if ($endDate !== '') {
    $conditions[] = 'created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)';
    $params[':end_date'] = $endDate;
}

$where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
$stmt = $conn->prepare(
    "SELECT audit_id, username, action, entity, entity_id, details, ip_address, created_at
     FROM audit_logs
     $where
     ORDER BY audit_id DESC
     LIMIT 200"
);
$stmt->execute($params);
$auditLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$actions = $conn->query('SELECT DISTINCT action FROM audit_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);
$entities = $conn->query('SELECT DISTINCT entity FROM audit_logs ORDER BY entity')->fetchAll(PDO::FETCH_COLUMN);

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$actionBadge = static function (string $action): array {
    return match ($action) {
        'delete' => ['danger', 'bi-x-circle'],
        'create' => ['success', 'bi-plus-circle'],
        'update' => ['primary', 'bi-pencil-square'],
        'login' => ['info', 'bi-box-arrow-in-right'],
        'logout' => ['info', 'bi-box-arrow-right'],
        'login_failed' => ['danger', 'bi-shield-exclamation'],
        default => ['secondary', 'bi-activity'],
    };
};

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Aktivitas - BEKUKU POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .audit-action-badge {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 76px;
            padding: 6px 12px;
            border-radius: 8px;
            color: #fff !important;
            font-weight: 700;
        }
        .audit-action-primary { color: #7ea7ff !important; background: #102d68 !important; border: 1px solid #315cff !important; }
        .audit-action-success { color: #4c75ff !important; background: #102d68 !important; border: 1px solid rgba(98, 214, 197, .35) !important; }
        .audit-action-info { color: #7ea7ff !important; background: #102d68 !important; border: 1px solid #315cff !important; }
        .audit-action-danger { color: #ff8792 !important; background: rgba(220, 53, 69, .12) !important; border: 1px solid rgba(255, 117, 131, .4) !important; }
        .audit-action-secondary { color: #c7d4f2 !important; background: #102d68 !important; border: 1px solid #315cff !important; }
        .audit-detail {
            display: grid;
            gap: 3px;
            min-width: 220px;
            color: var(--bekuku-text-body);
            font-size: .78rem;
            line-height: 1.35;
        }
        .audit-detail-row {
            display: flex;
            gap: 8px;
        }
        .audit-detail-label {
            flex: 0 0 105px;
            color: var(--bekuku-text-muted);
            font-weight: 700;
        }
        .audit-detail-value {
            overflow-wrap: anywhere;
        }
        .audit-ip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .audit-ip code {
            color: var(--bekuku-text-body);
            font-size: .78rem;
        }
        .audit-ip-local {
            padding: 2px 6px;
            border-radius: 5px;
            background: rgba(98, 214, 197, .14);
            color: var(--bekuku-accent);
            font-size: .68rem;
            font-weight: 700;
        }
    </style>
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/audit.css') ?>?v=202609221642">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="app-main">
        <div class="app-content p-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1">Audit Aktivitas</h3>
                        <p class="text-muted mb-0">Riwayat aktivitas penting pengguna dalam sistem.</p>
                        <small class="text-muted">Alamat IP menunjukkan perangkat atau jaringan asal aktivitas dan membantu pemeriksaan keamanan.</small>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label" for="action">Aksi</label>
                                <select class="form-select" id="action" name="action">
                                    <option value="">Semua aksi</option>
                                    <?php foreach ($actions as $option): ?>
                                        <option value="<?= $escape($option) ?>" <?= $action === $option ? 'selected' : '' ?>><?= $escape($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="entity">Entitas</label>
                                <select class="form-select" id="entity" name="entity">
                                    <option value="">Semua entitas</option>
                                    <?php foreach ($entities as $option): ?>
                                        <option value="<?= $escape($option) ?>" <?= $entity === $option ? 'selected' : '' ?>><?= $escape($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="username">Pengguna</label>
                                <input class="form-control" id="username" name="username" value="<?= $escape($username) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="start_date">Dari</label>
                                <input class="form-control" type="date" id="start_date" name="start_date" value="<?= $escape($startDate) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="end_date">Sampai</label>
                                <input class="form-control" type="date" id="end_date" name="end_date" value="<?= $escape($endDate) ?>">
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
                                <a class="btn btn-outline-secondary" href="<?= bekuku_url('audit/') ?>">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aksi</th>
                                    <th>Entitas</th>
                                    <th>ID</th>
                                    <th>Detail</th>
                                    <th>Alamat IP</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($auditLogs === []): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas yang sesuai filter.</td></tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <?php
                                    $details = $log['details'] !== null ? json_decode((string) $log['details'], true) : null;
                                    $detailText = is_array($details)
                                        ? (json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                                        : (string) ($log['details'] ?? '');
                                    ?>
                                    <tr>
                                        <td><?= $escape($log['created_at']) ?></td>
                                        <td><?= $escape($log['username'] ?? '-') ?></td>
                                        <?php [$actionColor, $actionIcon] = $actionBadge((string) $log['action']); ?>
                                        <td><span class="badge audit-action-badge audit-action-<?= $actionColor ?>"><i class="bi <?= $actionIcon ?>"></i> <?= $escape($log['action']) ?></span></td>
                                        <td><?= $escape($log['entity']) ?></td>
                                        <td><?= $log['entity_id'] === null ? '-' : (int) $log['entity_id'] ?></td>
                                        <td>
                                            <?php if (is_array($details) && $details !== []): ?>
                                                <div class="audit-detail">
                                                    <?php foreach ($details as $key => $value): ?>
                                                        <?php
                                                        $label = ucwords(str_replace('_', ' ', (string) $key));
                                                        $displayValue = is_scalar($value)
                                                            ? (string) $value
                                                            : (json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '-');
                                                        ?>
                                                        <div class="audit-detail-row">
                                                            <span class="audit-detail-label"><?= $escape($label) ?></span>
                                                            <span class="audit-detail-value"><?= $escape($displayValue) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted"><?= $escape($detailText !== '' ? $detailText : '-') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php
                                        $ipAddress = trim((string) ($log['ip_address'] ?? ''));
                                        $isLocalIp = $ipAddress === '::1' || $ipAddress === '127.0.0.1';
                                        ?>
                                        <td>
                                            <?php if ($ipAddress !== ''): ?>
                                                <span class="audit-ip" title="Alamat IP perangkat atau jaringan asal aktivitas">
                                                    <i class="bi bi-hdd-network"></i>
                                                    <code><?= $escape($ipAddress) ?></code>
                                                    <?php if ($isLocalIp): ?>
                                                        <span class="audit-ip-local">Lokal</span>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Tidak tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted">Menampilkan maksimal 200 aktivitas terbaru.</div>
                </div>
            </div>
        </div>
    </main>
</div>
    <script src="<?= bekuku_url('assets/js/audit.js') ?>?v=202609221642"></script>
</body>
</html>




