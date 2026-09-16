<?php

require_once __DIR__ . "/../config/app.php";


require_once __DIR__ . "/config/database.php";


/*
|--------------------------------------------------------------------------
| FUNGSI RUPIAH
|--------------------------------------------------------------------------
*/

function rupiah($number)
{
    return 'Rp ' . number_format(
        (float) $number,
        0,
        ',',
        '.'
    );
}


/*
|--------------------------------------------------------------------------
| TANGGAL HARI INI
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');


/*
|--------------------------------------------------------------------------
| STATISTIK PENJUALAN HARI INI
|--------------------------------------------------------------------------
*/

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

$todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

$today_transactions = (int) ($todayStats['total_transactions'] ?? 0);
$today_sales = (float) ($todayStats['total_sales'] ?? 0);


/*
|--------------------------------------------------------------------------
| TOTAL PRODUK AKTIF
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COUNT(*) 
    FROM products
    WHERE status = 'aktif'
");

$total_products = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| TOTAL STOK
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COALESCE(SUM(stock), 0)
    FROM products
    WHERE status = 'aktif'
");

$total_stock = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| STOK MENIPIS
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'aktif'
      AND stock > 0
      AND stock <= min_stock
");

$low_stock = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| STOK HABIS
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM products
    WHERE status = 'aktif'
      AND stock <= 0
");

$out_of_stock = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| BATCH AKAN EXPIRED 7 HARI
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM batches
    WHERE remaining_quantity > 0
      AND expiry_date IS NOT NULL
      AND expiry_date >= CURDATE()
      AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");

$expiring_batches = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| BATCH SUDAH EXPIRED
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM batches
    WHERE remaining_quantity > 0
      AND expiry_date IS NOT NULL
      AND expiry_date < CURDATE()
");

$expired_batches = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| PENJUALAN 7 HARI TERAKHIR
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
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

$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| SIAPKAN DATA CHART
|--------------------------------------------------------------------------
*/

$chart_labels = [];
$chart_values = [];

$salesByDate = [];

foreach ($salesData as $sale) {
    $salesByDate[$sale['sale_date']] = (float) $sale['total'];
}

for ($i = 6; $i >= 0; $i--) {

    $date = date(
        'Y-m-d',
        strtotime("-{$i} days")
    );

    $chart_labels[] = date(
        'd/m',
        strtotime($date)
    );

    $chart_values[] = $salesByDate[$date] ?? 0;
}


/*
|--------------------------------------------------------------------------
| PRODUK TERLARIS
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
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

$best_products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| TRANSAKSI TERBARU
|--------------------------------------------------------------------------
*/

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
    ORDER BY t.transaction_id DESC
    LIMIT 5
");

$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRODUK STOK MENIPIS
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
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

$low_stock_products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| BATCH AKAN EXPIRED
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
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

$expiring_list = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| BATCH EXPIRED
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
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

$expired_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - BEKUKU POS</title>

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

    <!-- CSS Global -->
    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div class="container-fluid p-4">


            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div class="mb-4">

                <h1 class="fw-bold mb-1">
                    Dashboard
                </h1>

                <p class="text-secondary mb-0">
                    Selamat datang di sistem Point of Sale BEKUKU.
                </p>

            </div>


            <!-- =====================================================
                 STATISTIK
            ====================================================== -->

            <div class="row g-3 mb-4">


                <!-- PENJUALAN HARI INI -->

                <div class="col-lg-3 col-md-6">

                    <div class="card h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <div class="text-secondary small">
                                        Penjualan Hari Ini
                                    </div>

                                    <div class="fs-4 fw-bold mt-2">
                                        <?= rupiah($today_sales) ?>
                                    </div>

                                </div>

                                <div class="fs-2 text-warning">
                                    <i class="bi bi-cash-stack"></i>
                                </div>

                            </div>

                            <div class="small text-secondary mt-3">

                                <?= number_format(
                                    $today_transactions,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                                transaksi

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUK -->

                <div class="col-lg-3 col-md-6">

                    <div class="card h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <div class="text-secondary small">
                                        Produk Aktif
                                    </div>

                                    <div class="fs-4 fw-bold mt-2">
                                        <?= number_format(
                                            $total_products,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </div>

                                </div>

                                <div class="fs-2 text-primary">
                                    <i class="bi bi-box-seam"></i>
                                </div>

                            </div>

                            <div class="small text-secondary mt-3">

                                Total stok:
                                <?= number_format(
                                    $total_stock,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- STOK MENIPIS -->

                <div class="col-lg-3 col-md-6">

                    <div class="card h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <div class="text-secondary small">
                                        Stok Menipis
                                    </div>

                                    <div class="fs-4 fw-bold mt-2">
                                        <?= number_format(
                                            $low_stock,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </div>

                                </div>

                                <div class="fs-2 text-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>

                            </div>

                            <div class="small text-secondary mt-3">

                                <?= number_format(
                                    $out_of_stock,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                                produk habis

                            </div>

                        </div>

                    </div>

                </div>


                <!-- EXPIRED -->

                <div class="col-lg-3 col-md-6">

                    <div class="card h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <div class="text-secondary small">
                                        Expired
                                    </div>

                                    <div class="fs-4 fw-bold mt-2">
                                        <?= number_format(
                                            $expired_batches,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </div>

                                </div>

                                <div class="fs-2 text-danger">
                                    <i class="bi bi-calendar-x"></i>
                                </div>

                            </div>

                            <div class="small text-secondary mt-3">

                                <?= number_format(
                                    $expiring_batches,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                                batch akan expired

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =====================================================
                 QUICK ACTION
            ====================================================== -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="d-flex flex-wrap gap-2">

                        <a
                            href="<?= bekuku_url('transactions/create.php') ?>"
                            class="btn btn-warning"
                        >
                            <i class="bi bi-cart-plus me-1"></i>
                            Transaksi Baru
                        </a>

                        <a
                            href="/Bekuku/products/create.php"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-box-seam me-1"></i>
                            Tambah Produk
                        </a>

                        <a
                            href="<?= bekuku_url('purchases/') ?>"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-bag-plus me-1"></i>
                            Pembelian
                        </a>

                        <a
                            href="<?= bekuku_url('transactions/') ?>"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-clock-history me-1"></i>
                            Riwayat Transaksi
                        </a>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 CHART + PRODUK TERLARIS
            ====================================================== -->

            <div class="row g-4 mb-4">


                <!-- CHART -->

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-3">

                            <h5 class="fw-bold mb-1">
                                Penjualan 7 Hari Terakhir
                            </h5>

                            <p class="text-secondary small mb-0">
                                Ringkasan penjualan berdasarkan transaksi selesai.
                            </p>

                        </div>

                        <div class="card-body">

                            <canvas id="salesChart"></canvas>

                        </div>

                    </div>

                </div>


                <!-- PRODUK TERLARIS -->

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-3">

                            <h5 class="fw-bold mb-1">
                                Produk Terlaris
                            </h5>

                            <p class="text-secondary small mb-0">
                                5 produk dengan penjualan tertinggi.
                            </p>

                        </div>

                        <div class="card-body">

                            <?php if (empty($best_products)): ?>

                                <div class="text-center text-secondary py-4">

                                    <i class="bi bi-box-seam fs-2"></i>

                                    <div class="mt-2">
                                        Belum ada data.
                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="list-group list-group-flush">

                                    <?php foreach ($best_products as $product): ?>

                                        <div class="list-group-item px-0 d-flex justify-content-between">

                                            <span>
                                                <?= htmlspecialchars(
                                                    $product['product_name']
                                                ) ?>
                                            </span>

                                            <strong>
                                                <?= number_format(
                                                    (int) $product['total_sold'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </strong>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =====================================================
                 TRANSAKSI + STOK
            ====================================================== -->

            <div class="row g-4 mb-4">


                <!-- TRANSAKSI TERBARU -->

                <div class="col-lg-7">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white border-0 pt-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">
                                        Transaksi Terbaru
                                    </h5>

                                    <p class="text-secondary small mb-0">
                                        5 transaksi terakhir.
                                    </p>

                                </div>

                                <a
                                    href="<?= bekuku_url('transactions/') ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Lihat Semua
                                </a>

                            </div>

                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover mb-0">

                                    <thead>

                                        <tr>

                                            <th>ID</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                    <?php if (empty($recent_transactions)): ?>

                                        <tr>

                                            <td
                                                colspan="5"
                                                class="text-center text-secondary py-4"
                                            >
                                                Belum ada transaksi.
                                            </td>

                                        </tr>

                                    <?php else: ?>

                                        <?php foreach ($recent_transactions as $transaction): ?>

                                            <tr>

                                                <td>
                                                    #<?= (int) $transaction['transaction_id'] ?>
                                                </td>

                                                <td>

                                                    <?= !empty(
                                                        $transaction['transaction_date']
                                                    )
                                                        ? htmlspecialchars(
                                                            date(
                                                                'd/m/Y H:i',
                                                                strtotime(
                                                                    $transaction['transaction_date']
                                                                )
                                                            )
                                                        )
                                                        : '-'
                                                    ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $transaction['customer_name']
                                                        ?: 'Umum'
                                                    ) ?>

                                                </td>

                                                <td>

                                                    <strong>
                                                        <?= rupiah(
                                                            $transaction['total_amount']
                                                        ) ?>
                                                    </strong>

                                                </td>

                                                <td>

                                                    <?php if (
                                                        strtolower(
                                                            trim(
                                                                $transaction['status'] ?? ''
                                                            )
                                                        ) === 'selesai'
                                                    ): ?>

                                                        <span class="badge text-bg-success">
                                                            Selesai
                                                        </span>

                                                    <?php elseif (
                                                        strtolower(
                                                            trim(
                                                                $transaction['status'] ?? ''
                                                            )
                                                        ) === 'batal'
                                                    ): ?>

                                                        <span class="badge text-bg-danger">
                                                            Batal
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge text-bg-warning">
                                                            Pending
                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- STOK MENIPIS -->

                <div class="col-lg-5">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white border-0 pt-3">

                            <h5 class="fw-bold mb-1">
                                Stok Menipis
                            </h5>

                            <p class="text-secondary small mb-0">
                                Produk yang perlu diperhatikan.
                            </p>

                        </div>

                        <div class="card-body">

                            <?php if (empty($low_stock_products)): ?>

                                <div class="text-center text-secondary py-3">

                                    <i class="bi bi-check-circle fs-2"></i>

                                    <div class="mt-2">
                                        Semua stok aman.
                                    </div>

                                </div>

                            <?php else: ?>

                                <?php foreach ($low_stock_products as $product): ?>

                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                        <div>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $product['product_name']
                                                ) ?>

                                            </div>

                                            <small class="text-secondary">

                                                Minimum:
                                                <?= number_format(
                                                    (int) $product['min_stock'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </small>

                                        </div>

                                        <?php if (
                                            (int) $product['stock'] <= 0
                                        ): ?>

                                            <span class="badge text-bg-danger">
                                                Habis
                                            </span>

                                        <?php else: ?>

                                            <span class="badge text-bg-warning">

                                                <?= number_format(
                                                    (int) $product['stock'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </span>

                                        <?php endif; ?>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =====================================================
                 EXPIRED
            ====================================================== -->

            <div class="row g-4 mb-4">


                <!-- AKAN EXPIRED -->

                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white border-0 pt-3">

                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-calendar-event text-warning me-1"></i>
                                Akan Expired
                            </h5>

                            <p class="text-secondary small mb-0">
                                Batch yang akan expired dalam 7 hari.
                            </p>

                        </div>

                        <div class="card-body">

                            <?php if (empty($expiring_list)): ?>

                                <div class="text-center text-secondary py-3">
                                    Tidak ada batch yang akan expired.
                                </div>

                            <?php else: ?>

                                <?php foreach ($expiring_list as $batch): ?>

                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                        <div>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $batch['product_name']
                                                ) ?>

                                            </div>

                                            <small class="text-secondary">

                                                Batch:
                                                <?= htmlspecialchars(
                                                    $batch['batch_number']
                                                ) ?>

                                            </small>

                                        </div>

                                        <div class="text-end">

                                            <div class="text-warning fw-semibold">

                                                <?= htmlspecialchars(
                                                    date(
                                                        'd/m/Y',
                                                        strtotime(
                                                            $batch['expiry_date']
                                                        )
                                                    )
                                                ) ?>

                                            </div>

                                            <small class="text-secondary">

                                                Stok:
                                                <?= number_format(
                                                    (int) $batch['remaining_quantity'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </small>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- EXPIRED -->

                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white border-0 pt-3">

                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-calendar-x text-danger me-1"></i>
                                Batch Expired
                            </h5>

                            <p class="text-secondary small mb-0">
                                Batch expired yang masih memiliki stok.
                            </p>

                        </div>

                        <div class="card-body">

                            <?php if (empty($expired_list)): ?>

                                <div class="text-center text-secondary py-3">
                                    Tidak ada batch expired.
                                </div>

                            <?php else: ?>

                                <?php foreach ($expired_list as $batch): ?>

                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                        <div>

                                            <div class="fw-semibold">

                                                <?= htmlspecialchars(
                                                    $batch['product_name']
                                                ) ?>

                                            </div>

                                            <small class="text-secondary">

                                                Batch:
                                                <?= htmlspecialchars(
                                                    $batch['batch_number']
                                                ) ?>

                                            </small>

                                        </div>

                                        <div class="text-end">

                                            <div class="text-danger fw-semibold">

                                                <?= htmlspecialchars(
                                                    date(
                                                        'd/m/Y',
                                                        strtotime(
                                                            $batch['expiry_date']
                                                        )
                                                    )
                                                ) ?>

                                            </div>

                                            <small class="text-secondary">

                                                Stok:
                                                <?= number_format(
                                                    (int) $batch['remaining_quantity'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </small>

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


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- AdminLTE -->

<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>


<!-- Chart.js -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

window.bekukuChartLabels =
    <?= json_encode($chart_labels) ?>;

window.bekukuChartValues =
    <?= json_encode($chart_values) ?>;

</script>


<!-- Dashboard JS -->

<script src="<?= bekuku_url('assets/dashboard/js/dashboard.js') ?>?v=2026091721"></script>


</body>

</html>