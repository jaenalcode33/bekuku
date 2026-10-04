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

$per_page = 10;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$total_pages = max(1, (int) ceil(count($transactions) / $per_page));
$current_page = min($current_page, $total_pages);
$page_offset = ($current_page - 1) * $per_page;
$display_transactions = array_slice($transactions, $page_offset, $per_page);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/transactions.css') ?>?v=202609221642">
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

                <div class="card transaction-history-card">
                    <div class="card-header transaction-history-header">
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
                                <table class="table table-bordered table-hover align-middle transaction-history-table">
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
                                        <?php $no = $page_offset + 1; foreach ($display_transactions as $transaction): ?>
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
                                                    <span class="badge transaction-payment-badge">
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

                            <div class="transaction-history-pagination">
                                <small class="text-muted">
                                    Menampilkan <?= $page_offset + 1; ?>-<?= min($page_offset + $per_page, count($transactions)); ?>
                                    dari <?= count($transactions); ?> transaksi
                                </small>

                                <?php if ($total_pages > 1): ?>
                                    <nav aria-label="Navigasi riwayat transaksi">
                                        <ul class="pagination mb-0">
                                            <?php
                                            $previous_query = ['page' => $current_page - 1];
                                            $next_query = ['page' => $current_page + 1];
                                            ?>
                                            <li class="page-item <?= $current_page === 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?<?= htmlspecialchars(http_build_query($previous_query)); ?>" aria-label="Halaman sebelumnya">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>

                                            <?php
                                            $page_start = (int) (floor(($current_page - 1) / 10) * 10) + 1;
                                            $page_end = min($total_pages, $page_start + 9);
                                            for ($page = $page_start; $page <= $page_end; $page++):
                                            ?>
                                                <li class="page-item <?= $page === $current_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?= $page; ?>"><?= $page; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <li class="page-item <?= $current_page === $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?<?= htmlspecialchars(http_build_query($next_query)); ?>" aria-label="Halaman berikutnya">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
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

    <script src="<?= bekuku_url('assets/js/transactions.js') ?>?v=202609221642"></script>
</body>
</html>


