<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

if (isset($_GET['id'])) {
    $transaction_id = (int) ($_GET['id'] ?? 0);

    if ($transaction_id > 0) {
        header('Location: ' . bekuku_url('transactions/detail.php?id=' . $transaction_id));
        exit;
    }
}

$stmt = $conn->query("
    SELECT
        t.transaction_id,
        t.transaction_date,
        t.total_amount,
        t.payment_method,
        t.status,
        c.customer_name
    FROM transactions t
    LEFT JOIN customers c
        ON t.customer_id = c.customer_id
    ORDER BY t.transaction_date DESC, t.transaction_id DESC
");

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function rupiah($number)
{
    return 'Rp ' . number_format(
        (float) $number,
        0,
        ',',
        '.'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - BEKUKU POS</title>

    <link rel="stylesheet" href="<?= bekuku_url('assets/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091612">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <?php require_once __DIR__ . "/../includes/header.php"; ?>

    <main class="app-main">

        <div class="container-fluid dashboard-wrapper">
            <div class="dashboard-hero">
                <div class="dashboard-hero-content">
                    <div>
                        <div class="dashboard-hero-title">
                            <i class="bi bi-receipt"></i>
                            Riwayat Transaksi
                        </div>

                        <p class="dashboard-hero-subtitle">
                            Data semua transaksi penjualan yang telah dilakukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-receipt-cutoff"></i>
                            Data Transaksi
                        </h3>

                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i>
                                Transaksi Baru
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <?php if (!empty($transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="60">No</th>
                                            <th width="130">ID</th>
                                            <th width="150">Tanggal</th>
                                            <th>Customer</th>
                                            <th width="170">Total</th>
                                            <th width="120">Metode</th>
                                            <th width="120">Status</th>
                                            <th width="120">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $no = 1; foreach ($transactions as $transaction): ?>
                                            <?php
                                            $status = strtolower($transaction['status'] ?? 'selesai');
                                            $paymentMethod = strtolower($transaction['payment_method'] ?? 'cash');

                                            if ($status === 'selesai') {
                                                $statusClass = 'bg-success';
                                                $statusLabel = 'Selesai';
                                            } else {
                                                $statusClass = 'bg-danger';
                                                $statusLabel = 'Batal';
                                            }

                                            if ($paymentMethod === 'cash') {
                                                $paymentLabel = 'Cash';
                                            } elseif ($paymentMethod === 'transfer') {
                                                $paymentLabel = 'Transfer';
                                            } elseif ($paymentMethod === 'qris') {
                                                $paymentLabel = 'QRIS Midtrans';
                                            } elseif ($paymentMethod === 'qris_image') {
                                                $paymentLabel = 'QRIS Gambar';
                                            } else {
                                                $paymentLabel = strtoupper($transaction['payment_method'] ?? '-');
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td>
                                                    <strong>#<?= (int) $transaction['transaction_id']; ?></strong>
                                                </td>
                                                <td>
                                                    <?= date('d M Y', strtotime($transaction['transaction_date'])); ?><br>
                                                    <small class="text-muted">
                                                        <?= date('H:i', strtotime($transaction['transaction_date'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($transaction['customer_name'] ?? 'Umum', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td>
                                                    <strong><?= rupiah($transaction['total_amount']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?= htmlspecialchars($paymentLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $statusClass; ?>">
                                                        <?= $statusLabel; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="detail.php?id=<?= (int) $transaction['transaction_id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Belum ada riwayat transaksi.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </main>

</div>

</body>
</html>
