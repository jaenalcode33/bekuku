<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| FILTER TANGGAL
|--------------------------------------------------------------------------
*/

$start_date = $_GET['start_date'] ?? date('Y-m-01');

$end_date = $_GET['end_date'] ?? date('Y-m-d');


/*
|--------------------------------------------------------------------------
| AMBIL DATA TRANSAKSI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        t.transaction_id,
        t.transaction_date,
        t.total_amount,
        t.payment_amount,
        t.change_amount,
        t.payment_method,
        t.status,
        c.customer_name
    FROM transactions t
    LEFT JOIN customers c
        ON t.customer_id = c.customer_id
    WHERE DATE(t.transaction_date)
        BETWEEN :start_date AND :end_date
    ORDER BY t.transaction_id DESC
");

$stmt->execute([
    ":start_date" => $start_date,
    ":end_date"   => $end_date
]);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HITUNG RINGKASAN
|--------------------------------------------------------------------------
*/

$total_transactions = count($transactions);

$total_sales = 0;

$total_payment = 0;

$total_change = 0;

$completed_transactions = 0;

foreach ($transactions as $transaction) {

    if ($transaction['status'] === 'selesai') {

        $total_sales += (float) $transaction['total_amount'];

        $total_payment += (float) $transaction['payment_amount'];

        $total_change += (float) $transaction['change_amount'];

        $completed_transactions++;
    }
}

$payment_summary = [];
foreach ($transactions as $transaction) {
    $method = strtolower((string) ($transaction['payment_method'] ?? ''));
    $payment_summary[$method] = [
        'count' => ($payment_summary[$method]['count'] ?? 0) + 1,
        'total' => ($payment_summary[$method]['total'] ?? 0) + (float) $transaction['total_amount'],
    ];
}

$top_products = [];
if ($transactions) {
    $transaction_ids = array_map(
        static fn (array $transaction): int => (int) $transaction['transaction_id'],
        $transactions
    );
    $placeholders = implode(',', array_fill(0, count($transaction_ids), '?'));
    $detail_stmt = $conn->prepare("
        SELECT p.product_name, SUM(td.quantity) AS quantity_sold, SUM(td.subtotal) AS total_sales
        FROM transaction_details td
        INNER JOIN products p ON p.product_id = td.product_id
        INNER JOIN transactions t ON t.transaction_id = td.transaction_id
        WHERE td.transaction_id IN ($placeholders)
          AND t.status = 'selesai'
        GROUP BY td.product_id, p.product_name
        ORDER BY quantity_sold DESC, total_sales DESC
        LIMIT 10
    ");
    $detail_stmt->execute($transaction_ids);
    $top_products = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$current_user = function_exists('bekuku_user') ? bekuku_user() : [];
$printed_date = date('d-m-Y');
$printed_time = date('H:i');


/*
|--------------------------------------------------------------------------
| FUNGSI RUPIAH
|--------------------------------------------------------------------------
*/

function rupiah($amount)
{
    return 'Rp ' . number_format(
        $amount,
        0,
        ',',
        '.'
    );
}

$printed_at = date('d-m-Y H:i');

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laporan Penjualan - BEKUKU</title>


    <!-- AdminLTE -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/adminlte.min.css') ?>"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- CSS BEKUKU -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091637"
    >

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/reports-sales.css') ?>?v=2026091655"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary report-page report-print">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="app-main">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="container-fluid dashboard-wrapper report-dashboard-heading">
            <section class="report-print-header" aria-hidden="true">
                <div class="report-print-brand">BEKUKU FROZEN FOOD</div>
                <h1>LAPORAN PENJUALAN</h1>
                <div class="report-print-period">
                    Periode:
                    <?= date('d-m-Y', strtotime($start_date)); ?>
                    s/d
                    <?= date('d-m-Y', strtotime($end_date)); ?>
                </div>
                <div class="report-print-date">Tanggal cetak: <?= htmlspecialchars($printed_at); ?></div>
            </section>
            <section class="dashboard-hero">
                <div class="dashboard-hero-content">
                <div class="report-hero-brand">
                    <div class="report-hero-mark">
                        <i class="bi bi-bar-chart-line"></i>
                        </div>
                    <div>
                    <div class="dashboard-hero-kicker">BEKUKU FROZEN FOOD</div>
                    <div class="dashboard-hero-title">
                        Laporan Penjualan
                    </div>
                    <p class="dashboard-hero-subtitle">
                        Pantau ringkasan dan detail penjualan berdasarkan periode tertentu.
                    </p>
                    </div>
                </div>
                    <div class="dashboard-hero-actions">
                        <button type="button" data-print class="btn dashboard-secondary-action">
                            <i class="bi bi-printer me-1"></i>Cetak Laporan
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <div class="app-content report-content">
            <div class="container-fluid dashboard-wrapper">

                <!-- FILTER -->

                <div class="card mb-4 report-filter-card report-print-hide">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-calendar-range me-2"></i>

                            Periode Laporan

                        </h3>

                    </div>


                    <div class="card-body">


                        <form method="GET">


                            <div class="row g-3 align-items-end">


                                <!-- Tanggal Mulai -->

                                <div class="col-lg-4 col-md-6">

                                    <label class="form-label">

                                        Tanggal Mulai

                                    </label>

                                    <input
                                        type="date"
                                        name="start_date"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $start_date
                                        ); ?>"
                                    >

                                </div>


                                <!-- Tanggal Akhir -->

                                <div class="col-lg-4 col-md-6">

                                    <label class="form-label">

                                        Tanggal Akhir

                                    </label>

                                    <input
                                        type="date"
                                        name="end_date"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $end_date
                                        ); ?>"
                                    >

                                </div>


                                <!-- Button -->

                                <div class="col-lg-4 col-md-12">

                                    <div class="d-flex gap-2">

                                        <button
                                            type="submit"
                                            class="btn dashboard-product-add-button"
                                        >

                                            <i class="bi bi-search me-1"></i>

                                            Tampilkan

                                        </button>


                                        <a
                                            href="sales.php"
                                            class="btn dashboard-secondary-action"
                                        >

                                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                                            Reset

                                        </a>

                                    </div>

                                </div>


                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     RINGKASAN
                ================================================== -->

                <div class="row g-3 mb-4 report-stat-grid">


                    <!-- Jumlah Transaksi -->

                    <div class="col-lg-3 col-md-6 mb-3">

                        <div class="card h-100 report-stat-card report-stat-total">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Transaksi

                                        </p>

                                        <h3 class="mb-0">

                                            <?= $total_transactions; ?>

                                        </h3>

                                        <small class="text-muted">

                                            transaksi

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-receipt fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Transaksi Selesai -->

                    <div class="col-lg-3 col-md-6 mb-3">

                        <div class="card h-100 report-stat-card report-stat-complete">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Transaksi Selesai

                                        </p>

                                        <h3 class="mb-0 text-success">

                                            <?= $completed_transactions; ?>

                                        </h3>

                                        <small class="text-muted">

                                            transaksi

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-check-circle fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Total Penjualan -->

                    <div class="col-lg-3 col-md-6 mb-3">

                        <div class="card h-100 report-stat-card report-stat-sales">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Penjualan

                                        </p>

                                        <h3 class="mb-0 text-primary">

                                            <?= rupiah($total_sales); ?>

                                        </h3>

                                        <small class="text-muted">

                                            transaksi selesai

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-cash-stack fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Total Kembalian -->

                    <div class="col-lg-3 col-md-6 mb-3">

                        <div class="card h-100 report-stat-card report-stat-change">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Kembalian

                                        </p>

                                        <h3 class="mb-0 text-warning">

                                            <?= rupiah($total_change); ?>

                                        </h3>

                                        <small class="text-muted">

                                            periode terpilih

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-arrow-return-left fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     INFORMASI PERIODE
                ================================================== -->

                <div class="alert alert-info report-period-banner d-flex align-items-center mb-4">

                    <i class="bi bi-calendar-check fs-4 me-3"></i>

                    <div>

                        Menampilkan laporan dari

                        <strong>
                            <?= date(
                                'd-m-Y',
                                strtotime($start_date)
                            ); ?>
                        </strong>

                        sampai

                        <strong>
                            <?= date(
                                'd-m-Y',
                                strtotime($end_date)
                            ); ?>
                        </strong>

                    </div>

                </div>


                <!-- =================================================
                     DATA PENJUALAN
                ================================================== -->

                <div class="card report-table-card">


                    <div class="card-header report-table-heading">

                        <h3 class="card-title">

                            <i class="bi bi-list-ul me-2"></i>

                            Data Penjualan

                        </h3>


                        <div class="card-tools">

                            <span class="badge text-bg-secondary">

                                <?= $total_transactions; ?> Data

                            </span>

                        </div>

                    </div>


                    <div class="card-body p-0">


                        <div class="table-responsive">

                            <table
                                class="table table-hover align-middle mb-0"
                            >

                                <thead>

                                    <tr>

                                        <th
                                            class="text-center"
                                            style="width: 60px;"
                                        >

                                            No

                                        </th>


                                        <th
                                            style="width: 110px;"
                                        >

                                            ID Transaksi

                                        </th>


                                        <th
                                            style="width: 165px;"
                                        >

                                            Tanggal

                                        </th>


                                        <th>

                                            Customer

                                        </th>


                                        <th class="text-end">

                                            Total

                                        </th>


                                        <th class="text-end">

                                            Pembayaran

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 120px;"
                                        >

                                            Metode

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 120px;"
                                        >

                                            Status

                                        </th>


                                        <th
                                            class="text-center report-action-column"
                                            style="width: 100px;"
                                        >

                                            Aksi

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php if (empty($transactions)): ?>


                                        <tr>

                                            <td
                                                colspan="9"
                                                class="text-center py-5"
                                            >

                                                <div class="text-muted">

                                                    <i
                                                        class="bi bi-bar-chart"
                                                        style="
                                                            font-size: 45px;
                                                        "
                                                    ></i>


                                                    <div class="mt-2">

                                                        Tidak ada transaksi
                                                        pada periode tersebut.

                                                    </div>


                                                    <small>

                                                        Coba ubah tanggal
                                                        laporan.

                                                    </small>

                                                </div>

                                            </td>

                                        </tr>


                                    <?php else: ?>


                                        <?php $no = 1; ?>


                                        <?php foreach ($transactions as $transaction): ?>


                                            <?php

                                            /*
                                            |--------------------------------------------------------------------------
                                            | PAYMENT BADGE
                                            |--------------------------------------------------------------------------
                                            */

                                            $paymentMethod =
                                                strtolower(
                                                    $transaction['payment_method']
                                                    ?? ''
                                                );


                                            switch ($paymentMethod) {

                                                case 'cash':

                                                    $paymentClass =
                                                        'bg-success';

                                                    $paymentIcon =
                                                        'bi-cash';

                                                    $paymentLabel =
                                                        'Cash';

                                                    break;


                                                case 'transfer':

                                                    $paymentClass =
                                                        'bg-primary';

                                                    $paymentIcon =
                                                        'bi-bank';

                                                    $paymentLabel =
                                                        'Transfer';

                                                    break;


                                                case 'qris':

                                                    $paymentClass =
                                                        'bg-info text-dark';

                                                    $paymentIcon =
                                                        'bi-qr-code';

                                                    $paymentLabel =
                                                        'QRIS Midtrans';

                                                    break;

                                                case 'qris_image':

                                                    $paymentClass =
                                                        'bg-info text-dark';

                                                    $paymentIcon =
                                                        'bi-image';

                                                    $paymentLabel =
                                                        'QRIS Gambar';

                                                    break;


                                                default:

                                                    $paymentClass =
                                                        'bg-secondary';

                                                    $paymentIcon =
                                                        'bi-credit-card';

                                                    $paymentLabel =
                                                        ucfirst(
                                                            $paymentMethod
                                                        );

                                                    break;

                                            }


                                            /*
                                            |--------------------------------------------------------------------------
                                            | STATUS BADGE
                                            |--------------------------------------------------------------------------
                                            */

                                            $status =
                                                strtolower(
                                                    $transaction['status']
                                                    ?? ''
                                                );


                                            if (
                                                $status === 'selesai'
                                            ) {

                                                $statusClass =
                                                    'bg-success';

                                                $statusIcon =
                                                    'bi-check-circle';

                                            } elseif (
                                                $status === 'batal'
                                            ) {

                                                $statusClass =
                                                    'bg-danger';

                                                $statusIcon =
                                                    'bi-x-circle';

                                            } else {

                                                $statusClass =
                                                    'bg-secondary';

                                                $statusIcon =
                                                    'bi-info-circle';

                                            }

                                            ?>


                                            <tr>


                                                <!-- No -->

                                                <td
                                                    class="text-center text-muted"
                                                >

                                                    <?= $no++; ?>

                                                </td>


                                                <!-- ID -->

                                                <td>

                                                    <span
                                                        class="fw-semibold text-primary"
                                                    >

                                                        #<?= htmlspecialchars(
                                                            $transaction['transaction_id']
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Tanggal -->

                                                <td>

                                                    <div class="fw-semibold">

                                                        <?= date(
                                                            'd-m-Y',
                                                            strtotime(
                                                                $transaction['transaction_date']
                                                            )
                                                        ); ?>

                                                    </div>


                                                    <small class="text-muted">

                                                        <i class="bi bi-clock me-1"></i>

                                                        <?= date(
                                                            'H:i',
                                                            strtotime(
                                                                $transaction['transaction_date']
                                                            )
                                                        ); ?>

                                                        WIB

                                                    </small>

                                                </td>


                                                <!-- Customer -->

                                                <td>

                                                    <?php

                                                    $customerName =
                                                        $transaction['customer_name']
                                                        ?? 'Umum';

                                                    ?>


                                                    <div
                                                        class="d-flex align-items-center"
                                                    >


                                                        <div
                                                            class="d-flex align-items-center justify-content-center rounded-circle bg-light text-primary me-2"
                                                            style="
                                                                width: 36px;
                                                                height: 36px;
                                                            "
                                                        >

                                                            <i class="bi bi-person"></i>

                                                        </div>


                                                        <span>

                                                            <?= htmlspecialchars(
                                                                $customerName
                                                            ); ?>

                                                        </span>


                                                    </div>

                                                </td>


                                                <!-- Total -->

                                                <td class="text-end">

                                                    <span class="fw-bold">

                                                        <?= rupiah(
                                                            $transaction['total_amount']
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Pembayaran -->

                                                <td class="text-end">

                                                    <?= rupiah(
                                                        $transaction['payment_amount']
                                                    ); ?>

                                                </td>


                                                <!-- Metode -->

                                                <td class="text-center report-action-column">

                                                    <span
                                                        class="badge <?= $paymentClass; ?> px-2 py-1"
                                                    >

                                                        <i
                                                            class="bi <?= $paymentIcon; ?> me-1"
                                                        ></i>

                                                        <?= htmlspecialchars(
                                                            $paymentLabel
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Status -->

                                                <td class="text-center">

                                                    <span
                                                        class="badge <?= $statusClass; ?> px-2 py-1"
                                                    >

                                                        <i
                                                            class="bi <?= $statusIcon; ?> me-1"
                                                        ></i>

                                                        <?= htmlspecialchars(
                                                            ucfirst($status)
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Detail -->

                                                <td class="text-center">

                                                    <a
                                                        href="../transactions/detail.php?id=<?= $transaction['transaction_id']; ?>"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Lihat Detail"
                                                    >

                                                        <i class="bi bi-eye"></i>

                                                        Detail

                                                    </a>

                                                </td>


                                            </tr>


                                        <?php endforeach; ?>


                                    <?php endif; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>


                    <?php if (!empty($transactions)): ?>

                        <div class="card-footer">

                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
                            >

                                <small class="text-muted">

                                    <i class="bi bi-info-circle me-1"></i>

                                    <?= $total_transactions; ?>
                                    transaksi ditemukan.

                                </small>


                                <strong>

                                    Total Penjualan:

                                    <span class="text-primary">

                                        <?= rupiah($total_sales); ?>

                                    </span>

                                </strong>

                            </div>

                        </div>

                    <?php endif; ?>

                    <div class="report-print-signature">
                        <div>
                            <div class="report-print-system">Dicetak dari Sistem POS BEKUKU</div>
                            Mengetahui,<br>
                            <span class="report-print-line">________________________</span>
                            Admin / Kasir
                        </div>
                    </div>


                </div>


            </div>

        </div>

        <div class="sales-print-preview-wrap">
            <article class="sales-print-report" aria-label="Dokumen laporan penjualan">
                <header class="sales-print-header">
                    <div>
                        <div class="sales-print-brand">BEKUKU</div>
                        <div>FROZEN FOOD</div>
                        <small>POINT OF SALE</small>
                    </div>
                    <div class="sales-print-title">
                        <h1>LAPORAN PENJUALAN</h1>
                        <div>Periode: <?= date('d-m-Y', strtotime($start_date)); ?> s/d <?= date('d-m-Y', strtotime($end_date)); ?></div>
                    </div>
                </header>

                <div class="sales-print-meta">
                    <span>Tanggal Cetak: <?= htmlspecialchars($printed_date); ?></span>
                    <span>Waktu: <?= htmlspecialchars($printed_time); ?></span>
                    <span>Dicetak Oleh: <?= htmlspecialchars($current_user['name'] ?? $current_user['username'] ?? 'Administrator'); ?></span>
                </div>

                <section class="sales-print-summary">
                    <div><span>TOTAL TRANSAKSI</span><strong><?= number_format($total_transactions); ?></strong></div>
                    <div><span>TRANSAKSI SELESAI</span><strong><?= number_format($completed_transactions); ?></strong></div>
                    <div><span>TOTAL PENJUALAN</span><strong><?= rupiah($total_sales); ?></strong></div>
                    <div><span>TOTAL KEMBALIAN</span><strong><?= rupiah($total_change); ?></strong></div>
                </section>

                <h2 class="sales-print-section-title">DATA TRANSAKSI</h2>
                <table class="sales-print-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Metode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <?php
                            $method = strtolower((string) ($transaction['payment_method'] ?? ''));
                            $method_labels = [
                                'cash' => 'Tunai',
                                'qris' => 'QRIS',
                                'qris_image' => 'QRIS Gambar',
                                'transfer' => 'Transfer',
                            ];
                            $status = strtolower((string) ($transaction['status'] ?? ''));
                            $status_label = $status === 'selesai' ? 'SELESAI' : ($status === 'batal' ? 'DIBATALKAN' : strtoupper($status));
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1; ?></td>
                                <td>#<?= htmlspecialchars((string) $transaction['transaction_id']); ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                                <td><?= htmlspecialchars($transaction['customer_name'] ?: 'Umum'); ?></td>
                                <td class="amount"><?= rupiah((float) $transaction['total_amount']); ?></td>
                                <td class="amount"><?= rupiah((float) $transaction['payment_amount']); ?></td>
                                <td><?= htmlspecialchars($method_labels[$method] ?? ucfirst($method)); ?></td>
                                <td><span class="sales-print-status status-<?= htmlspecialchars($status); ?>"><?= $status === 'selesai' ? '✓ ' : ($status === 'batal' ? '✕ ' : '! '); ?><?= htmlspecialchars($status_label); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($payment_summary): ?>
                    <section class="sales-print-support-grid">
                        <div>
                            <h2 class="sales-print-section-title">RINGKASAN METODE PEMBAYARAN</h2>
                            <table class="sales-print-mini-table">
                                <thead><tr><th>Metode</th><th>Transaksi</th><th>Total</th></tr></thead>
                                <tbody>
                                    <?php foreach ($payment_summary as $method => $summary): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($method_labels[$method] ?? ucfirst($method)); ?></td>
                                            <td><?= number_format($summary['count']); ?></td>
                                            <td class="amount"><?= rupiah($summary['total']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($top_products): ?>
                            <div>
                                <h2 class="sales-print-section-title">PRODUK TERLARIS</h2>
                                <table class="sales-print-mini-table">
                                    <thead><tr><th>No</th><th>Produk</th><th>Qty</th><th>Total</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($top_products as $index => $product): ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td><?= htmlspecialchars($product['product_name']); ?></td>
                                                <td><?= number_format((int) $product['quantity_sold']); ?></td>
                                                <td class="amount"><?= rupiah((float) $product['total_sales']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="sales-print-total">
                    <div><span>TOTAL TRANSAKSI</span><strong><?= number_format($total_transactions); ?></strong></div>
                    <div><span>TRANSAKSI SELESAI</span><strong><?= number_format($completed_transactions); ?></strong></div>
                    <div><span>TOTAL PENJUALAN</span><strong><?= rupiah($total_sales); ?></strong></div>
                </section>

                <footer class="sales-print-footer">
                    <span>BEKUKU · POINT OF SALE</span>
                    <span>Laporan Penjualan · Dicetak <?= htmlspecialchars($printed_date . ' ' . $printed_time); ?></span>
                </footer>
            </article>
        </div>


    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- AdminLTE JS -->

<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/reports-sales.js') ?>?v=2026091655"></script>


</body>

</html>
