<?php

require_once __DIR__ . "/../config/app.php";

/* =========================================================
   ERROR REPORTING
   Aktif sementara agar error PHP terlihat jelas.
========================================================= */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/../config/database.php";


/* =========================================================
   FUNCTION
========================================================= */

function rupiah($number)
{
    return 'Rp ' . number_format(
        (float) $number,
        0,
        ',',
        '.'
    );
}


/* =========================================================
   DASHBOARD DATA
========================================================= */

$today = date('Y-m-d');


/* ---------------------------------------------------------
   1. PENJUALAN HARI INI
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_transactions,
        COALESCE(SUM(total_amount), 0) AS total_sales
    FROM transactions
    WHERE status = 'selesai'
      AND DATE(transaction_date) = :today
");

$stmt->execute([
    ':today' => $today
]);

$today_sales = $stmt->fetch(PDO::FETCH_ASSOC);

$total_transactions_today =
    (int) ($today_sales['total_transactions'] ?? 0);

$total_sales_today =
    (float) ($today_sales['total_sales'] ?? 0);


/* ---------------------------------------------------------
   2. PRODUK AKTIF
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'aktif'
");

$stmt->execute();

$total_active_products =
    (int) $stmt->fetchColumn();


/* ---------------------------------------------------------
   3. TOTAL STOK
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(stock), 0)
    FROM products
    WHERE status = 'aktif'
");

$stmt->execute();

$total_stock =
    (int) $stmt->fetchColumn();


/* ---------------------------------------------------------
   4. STOK MENIPIS
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'aktif'
      AND stock > 0
      AND stock <= min_stock
");

$stmt->execute();

$low_stock_count =
    (int) $stmt->fetchColumn();


/* ---------------------------------------------------------
   5. STOK HABIS
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'aktif'
      AND stock <= 0
");

$stmt->execute();

$out_of_stock_count =
    (int) $stmt->fetchColumn();


/* ---------------------------------------------------------
   6. BATCH AKAN EXPIRED
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM batches
    WHERE remaining_quantity > 0
      AND expiry_date IS NOT NULL
      AND expiry_date >= CURDATE()
      AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");

$stmt->execute();

$expiring_batches =
    (int) $stmt->fetchColumn();


/* ---------------------------------------------------------
   7. BATCH SUDAH EXPIRED
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM batches
    WHERE remaining_quantity > 0
      AND expiry_date IS NOT NULL
      AND expiry_date < CURDATE()
");

$stmt->execute();

$expired_batches =
    (int) $stmt->fetchColumn();


/* =========================================================
   GRAFIK PENJUALAN 7 HARI
========================================================= */

$stmt = $conn->prepare("
    SELECT
        DATE(transaction_date) AS sale_date,
        COALESCE(SUM(total_amount), 0) AS total
    FROM transactions
    WHERE status = 'selesai'
      AND DATE(transaction_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      AND DATE(transaction_date) <= CURDATE()
    GROUP BY DATE(transaction_date)
    ORDER BY sale_date ASC
");

$stmt->execute();

$sales_chart_rows =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ---------------------------------------------------------
   Buat 7 tanggal lengkap
--------------------------------------------------------- */

$chart_labels = [];
$chart_values = [];

for ($i = 6; $i >= 0; $i--) {

    $date = date(
        'Y-m-d',
        strtotime("-{$i} days")
    );

    $chart_labels[] =
        date('d/m', strtotime($date));

    $chart_values[$date] = 0;
}


/* ---------------------------------------------------------
   Masukkan data penjualan
--------------------------------------------------------- */

foreach ($sales_chart_rows as $row) {

    $sale_date =
        $row['sale_date'];

    $total =
        (float) $row['total'];

    if (array_key_exists($sale_date, $chart_values)) {

        $chart_values[$sale_date] =
            $total;
    }
}


/* ---------------------------------------------------------
   Ubah values menjadi array biasa
--------------------------------------------------------- */

$chart_values =
    array_values($chart_values);


/* =========================================================
   PRODUK TERLARIS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        p.product_name,
        COALESCE(SUM(td.quantity), 0) AS total_sold
    FROM transaction_details td
    INNER JOIN transactions t
        ON td.transaction_id = t.transaction_id
    INNER JOIN products p
        ON td.product_id = p.product_id
    WHERE t.status = 'selesai'
    GROUP BY
        p.product_id,
        p.product_name
    ORDER BY total_sold DESC
    LIMIT 5
");

$stmt->execute();

$best_products =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   TRANSAKSI TERBARU
========================================================= */

$stmt = $conn->prepare("
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
    ORDER BY t.transaction_id DESC
    LIMIT 5
");

$stmt->execute();

$recent_transactions =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   PRODUK STOK MENIPIS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        product_id,
        product_name,
        stock,
        min_stock
    FROM products
    WHERE status = 'aktif'
      AND stock <= min_stock
    ORDER BY stock ASC, product_name ASC
    LIMIT 5
");

$stmt->execute();

$low_stock_products =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   BATCH AKAN EXPIRED
========================================================= */

$stmt = $conn->prepare("
    SELECT
        b.batch_id,
        b.batch_number,
        b.expiry_date,
        b.remaining_quantity,
        p.product_name
    FROM batches b
    INNER JOIN products p
        ON b.product_id = p.product_id
    WHERE b.remaining_quantity > 0
      AND b.expiry_date IS NOT NULL
      AND b.expiry_date >= CURDATE()
      AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY b.expiry_date ASC
    LIMIT 5
");

$stmt->execute();

$expiring_batch_list =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   BATCH SUDAH EXPIRED
========================================================= */

$stmt = $conn->prepare("
    SELECT
        b.batch_id,
        b.batch_number,
        b.expiry_date,
        b.remaining_quantity,
        p.product_name
    FROM batches b
    INNER JOIN products p
        ON b.product_id = p.product_id
    WHERE b.remaining_quantity > 0
      AND b.expiry_date IS NOT NULL
      AND b.expiry_date < CURDATE()
    ORDER BY b.expiry_date ASC
    LIMIT 5
");

$stmt->execute();

$expired_batch_list =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - BEKUKU POS
    </title>


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


    <!-- BEKUKU CSS -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091722"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary dashboard-page">


<div class="app-wrapper">


    <!-- =====================================================
         NAVBAR / SIDEBAR
    ====================================================== -->

    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="app-main">


        <div class="container-fluid dashboard-wrapper">


            <!-- =================================================
                 HERO
            ================================================== -->

            <div class="dashboard-hero">

                <div class="dashboard-hero-content">

                    <div>

                        <div class="dashboard-hero-title">

                            <i class="bi bi-grid-1x2-fill me-2"></i>

                            <?= htmlspecialchars(bekuku_role_label(bekuku_user()['role'] ?? 'admin'), ENT_QUOTES, 'UTF-8') ?>

                        </div>


                        <p class="dashboard-hero-subtitle">

                            Selamat datang, <?= htmlspecialchars(bekuku_user()['name'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8') ?>.
                            Berikut ringkasan operasional BEKUKU hari ini.

                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STAT CARDS
            ================================================== -->

            <div class="row">


                <!-- Penjualan -->

                <div class="col-lg-3 col-md-6">

                    <div class="dashboard-stat stat-orange">

                        <div class="stat-icon">

                            <i class="bi bi-cash-stack"></i>

                        </div>


                        <div class="stat-label">

                            Penjualan Hari Ini

                        </div>


                        <div class="stat-value">

                            <?= rupiah($total_sales_today) ?>

                        </div>


                        <div class="stat-description">

                            <?= $total_transactions_today ?>
                            transaksi selesai

                        </div>

                    </div>

                </div>


                <!-- Produk -->

                <div class="col-lg-3 col-md-6">

                    <div class="dashboard-stat stat-blue">

                        <div class="stat-icon">

                            <i class="bi bi-box-seam"></i>

                        </div>


                        <div class="stat-label">

                            Produk Aktif

                        </div>


                        <div class="stat-value">

                            <?= number_format(
                                $total_active_products,
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>


                        <div class="stat-description">

                            Produk yang tersedia

                        </div>

                    </div>

                </div>


                <!-- Stok -->

                <div class="col-lg-3 col-md-6">

                    <div class="dashboard-stat stat-yellow">

                        <div class="stat-icon">

                            <i class="bi bi-boxes"></i>

                        </div>


                        <div class="stat-label">

                            Total Stok

                        </div>


                        <div class="stat-value">

                            <?= number_format(
                                $total_stock,
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>


                        <div class="stat-description">

                            Unit stok aktif

                        </div>

                    </div>

                </div>


                <!-- Stok Menipis -->

                <div class="col-lg-3 col-md-6">

                    <div class="dashboard-stat stat-red">

                        <div class="stat-icon">

                            <i class="bi bi-exclamation-triangle"></i>

                        </div>


                        <div class="stat-label">

                            Perlu Perhatian

                        </div>


                        <div class="stat-value">

                            <?= number_format(
                                $low_stock_count +
                                $out_of_stock_count +
                                $expired_batches,
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>


                        <div class="stat-description">

                            Stok menipis,
                            habis atau expired

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 QUICK ACTION
            ================================================== -->

            <div class="card dashboard-actions mb-4">

                <div class="card-body">


                    <div class="dashboard-actions-heading">

                        <div>

                            <div class="dashboard-section-kicker">

                                MENU CEPAT

                            </div>


                            <div class="dashboard-section-description">

                                Akses fitur utama BEKUKU.

                            </div>

                        </div>


                        <i class="bi bi-lightning-charge-fill"></i>

                    </div>


                    <div class="dashboard-action-list">


                        <a
                            href="<?= bekuku_url('transactions/create.php') ?>"
                            class="dashboard-action-button"
                            data-no-modal
                        >

                            <i class="bi bi-cart-plus"></i>

                            Transaksi Baru

                        </a>


                        <a
                            href="/Bekuku/products/create.php"
                            class="dashboard-action-button"
                            data-modal-url="/Bekuku/products/create.php"
                            data-modal-title="Tambah Produk"
                        >

                            <i class="bi bi-box-seam"></i>

                            Tambah Produk

                        </a>


                        <a
                            href="<?= bekuku_url('purchases/') ?>"
                            class="dashboard-action-button"
                        >

                            <i class="bi bi-truck"></i>

                            Pembelian

                        </a>


                        <a
                            href="<?= bekuku_url('transactions/') ?>"
                            class="dashboard-action-button"
                        >

                            <i class="bi bi-clock-history"></i>

                            Riwayat Transaksi

                        </a>


                    </div>

                </div>

            </div>


            <!-- =================================================
                 GRAFIK
            ================================================== -->

            <div class="row mb-4">


                <div class="col-12">

                    <div class="card">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-bar-chart-line-fill me-2"></i>

                                Penjualan 7 Hari Terakhir

                            </h5>

                        </div>


                        <div class="card-body">


                            <div class="chart-container">

                                <canvas
                                    id="salesChart"
                                    data-labels="<?= htmlspecialchars(
                                        json_encode(
                                            $chart_labels
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-values="<?= htmlspecialchars(
                                        json_encode(
                                            $chart_values
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8')
                                    ?>"
                                ></canvas> 

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 PRODUK TERLARIS + TRANSAKSI TERBARU
            ================================================== -->

            <div class="row dashboard-section">


                <!-- PRODUK TERLARIS -->

                <div class="col-lg-6 mb-4">

                    <div class="card h-100">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-trophy-fill me-2"></i>

                                Produk Terlaris

                            </h5>

                        </div>


                        <div class="card-body p-0">


                            <?php if (empty($best_products)): ?>

                                <div class="dashboard-empty">

                                    <i class="bi bi-box-seam d-block"></i>

                                    Belum ada data penjualan.

                                </div>

                            <?php else: ?>


                                <div class="table-responsive">

                                    <table class="table dashboard-table">

                                        <thead>

                                            <tr>

                                                <th width="60">
                                                    #
                                                </th>

                                                <th>
                                                    Produk
                                                </th>

                                                <th class="text-end">
                                                    Terjual
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach (
                                                $best_products
                                                as $index => $product
                                            ): ?>

                                                <tr>

                                                    <td>

                                                        <div class="product-rank">

                                                            <?= $index + 1 ?>

                                                        </div>

                                                    </td>


                                                    <td>

                                                        <div class="product-name">

                                                            <?= htmlspecialchars(
                                                                $product['product_name']
                                                            ) ?>

                                                        </div>

                                                    </td>


                                                    <td class="text-end">

                                                        <strong>

                                                            <?= number_format(
                                                                (int) $product['total_sold'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ) ?>

                                                        </strong>

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                    </table>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- TRANSAKSI TERBARU -->

                <div class="col-lg-6 mb-4">

                    <div class="card h-100">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-receipt-cutoff me-2"></i>

                                Transaksi Terbaru

                            </h5>

                        </div>


                        <div class="card-body p-0">


                            <?php if (empty($recent_transactions)): ?>

                                <div class="dashboard-empty">

                                    <i class="bi bi-receipt d-block"></i>

                                    Belum ada transaksi.

                                </div>

                            <?php else: ?>


                                <div class="table-responsive">

                                    <table class="table dashboard-table">

                                        <thead>

                                            <tr>

                                                <th>
                                                    ID
                                                </th>

                                                <th>
                                                    Tanggal
                                                </th>

                                                <th>
                                                    Customer
                                                </th>

                                                <th class="text-end">
                                                    Total
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach (
                                                $recent_transactions
                                                as $transaction
                                            ): ?>

                                                <tr>

                                                    <td>

                                                        #<?= (int)
                                                            $transaction[
                                                                'transaction_id'
                                                            ] ?>

                                                    </td>


                                                    <td>

                                                        <?= date(
                                                            'd/m/Y H:i',
                                                            strtotime(
                                                                $transaction[
                                                                    'transaction_date'
                                                                ]
                                                            )
                                                        ) ?>

                                                    </td>


                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $transaction[
                                                                'customer_name'
                                                            ] ??
                                                            'Umum'
                                                        ) ?>

                                                    </td>


                                                    <td class="text-end">

                                                        <strong>

                                                            <?= rupiah(
                                                                $transaction[
                                                                    'total_amount'
                                                                ]
                                                            ) ?>

                                                        </strong>

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                    </table>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 STOCK + EXPIRED
            ================================================== -->

            <div class="row dashboard-section">


                <!-- STOK MENIPIS -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-exclamation-circle-fill me-2"></i>

                                Stok Menipis

                            </h5>

                        </div>


                        <div class="card-body">


                            <?php if (empty($low_stock_products)): ?>

                                <div class="dashboard-empty">

                                    <i class="bi bi-check-circle d-block"></i>

                                    Semua stok aman.

                                </div>

                            <?php else: ?>


                                <?php foreach (
                                    $low_stock_products
                                    as $product
                                ): ?>

                                    <div class="alert-item">


                                        <div class="alert-icon alert-warning">

                                            <i class="bi bi-box"></i>

                                        </div>


                                        <div>

                                            <div class="alert-title">

                                                <?= htmlspecialchars(
                                                    $product[
                                                        'product_name'
                                                    ]
                                                ) ?>

                                            </div>


                                            <div class="alert-text">

                                                Stok:

                                                <strong>

                                                    <?= (int)
                                                        $product[
                                                            'stock'
                                                        ] ?>

                                                </strong>

                                                /
                                                minimum

                                                <?= (int)
                                                    $product[
                                                        'min_stock'
                                                    ] ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- AKAN EXPIRED -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-calendar-event me-2"></i>

                                Akan Expired

                            </h5>

                        </div>


                        <div class="card-body">


                            <?php if (empty($expiring_batch_list)): ?>

                                <div class="dashboard-empty">

                                    <i class="bi bi-check-circle d-block"></i>

                                    Tidak ada batch yang
                                    akan expired dalam 7 hari.

                                </div>

                            <?php else: ?>


                                <?php foreach (
                                    $expiring_batch_list
                                    as $batch
                                ): ?>

                                    <div class="alert-item">


                                        <div class="alert-icon alert-warning">

                                            <i class="bi bi-clock-history"></i>

                                        </div>


                                        <div>

                                            <div class="alert-title">

                                                <?= htmlspecialchars(
                                                    $batch[
                                                        'product_name'
                                                    ]
                                                ) ?>

                                            </div>


                                            <div class="alert-text">

                                                Expired:

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $batch[
                                                            'expiry_date'
                                                        ]
                                                    )
                                                ) ?>

                                                Â· Stok:

                                                <?= (int)
                                                    $batch[
                                                        'remaining_quantity'
                                                    ] ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- EXPIRED -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">


                        <div class="card-header">

                            <h5 class="mb-0">

                                <i class="bi bi-calendar-x-fill me-2"></i>

                                Sudah Expired

                            </h5>

                        </div>


                        <div class="card-body">


                            <?php if (empty($expired_batch_list)): ?>

                                <div class="dashboard-empty">

                                    <i class="bi bi-check-circle d-block"></i>

                                    Tidak ada batch expired.

                                </div>

                            <?php else: ?>


                                <?php foreach (
                                    $expired_batch_list
                                    as $batch
                                ): ?>

                                    <div class="alert-item">


                                        <div class="alert-icon alert-danger">

                                            <i class="bi bi-x-circle"></i>

                                        </div>


                                        <div>

                                            <div class="alert-title">

                                                <?= htmlspecialchars(
                                                    $batch[
                                                        'product_name'
                                                    ]
                                                ) ?>

                                            </div>


                                            <div class="alert-text">

                                                Expired:

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $batch[
                                                            'expiry_date'
                                                        ]
                                                    )
                                                ) ?>

                                                Â· Stok:

                                                <?= (int)
                                                    $batch[
                                                        'remaining_quantity'
                                                    ] ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


            </div>


        </div>


    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= bekuku_url('assets/dashboard/js/dashboard.js') ?>?v=2026091721"></script>
<script src="<?= bekuku_url('assets/js/ui.js') ?>?v=2026091722"></script>


</body>

</html>
