<?php
/*
|--------------------------------------------------------------------------
| BEKUKU POS - PUBLIC DEMO
|--------------------------------------------------------------------------
| DEMO ONLY
| - Tidak menggunakan database
| - Tidak menggunakan config/database.php
| - Tidak menggunakan session login
| - Tidak melakukan INSERT / UPDATE / DELETE
| - Semua data adalah dummy
|--------------------------------------------------------------------------
*/

/* =========================================================
   BASE URL
   ========================================================= */

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/landing/demo.php');
$projectBase = dirname(dirname($scriptName));

if ($projectBase === '/' || $projectBase === '\\' || $projectBase === '.') {
    $projectBase = '';
}

$projectBase = rtrim($projectBase, '/');

function demo_url(string $path = ''): string
{
    global $projectBase;

    if ($path === '') {
        return $projectBase === '' ? '/' : $projectBase . '/';
    }

    return $projectBase . '/' . ltrim($path, '/');
}

/* =========================================================
   DEMO HELPER
   ========================================================= */

function rupiah_demo($number): string
{
    return 'Rp ' . number_format(
        (float) $number,
        0,
        ',',
        '.'
    );
}

function e_demo($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/* =========================================================
   DEMO USER
   Tidak mengambil session/database
   ========================================================= */

$demoUser = [
    'name' => 'Demo User',
    'role' => 'admin'
];

function demo_role_label(string $role): string
{
    return [
        'admin'  => 'Administrator',
        'kasir'  => 'Kasir',
        'gudang' => 'Gudang'
    ][$role] ?? ucfirst($role);
}

/* =========================================================
   DEMO DATA
   ========================================================= */

$total_sales_today = 4850000;
$total_transactions_today = 32;

$total_active_products = 128;

$total_stock = 1647;

$low_stock_count = 6;
$out_of_stock_count = 2;

$expiring_batches = 4;
$expired_batches = 2;

/* =========================================================
   CHART DATA
   ========================================================= */

$chart_labels = [
    date('d/m', strtotime('-6 days')),
    date('d/m', strtotime('-5 days')),
    date('d/m', strtotime('-4 days')),
    date('d/m', strtotime('-3 days')),
    date('d/m', strtotime('-2 days')),
    date('d/m', strtotime('-1 day')),
    date('d/m')
];

$chart_values = [
    2750000,
    3900000,
    3150000,
    5200000,
    4300000,
    6100000,
    4850000
];

/* =========================================================
   PRODUK TERLARIS
   ========================================================= */

$best_products = [
    [
        'product_name' => 'Nugget Ayam Fiesta',
        'total_sold' => 186
    ],
    [
        'product_name' => 'Sosis Kanzler Beef',
        'total_sold' => 154
    ],
    [
        'product_name' => 'Kentang Goreng Shoestring',
        'total_sold' => 137
    ],
    [
        'product_name' => 'Bakso Sapi Premium',
        'total_sold' => 121
    ],
    [
        'product_name' => 'Dimsum Ayam',
        'total_sold' => 98
    ]
];

/* =========================================================
   TRANSAKSI TERBARU
   ========================================================= */

$recent_transactions = [
    [
        'transaction_id' => 1028,
        'transaction_date' => date('Y-m-d H:i:s', strtotime('-12 minutes')),
        'customer_name' => 'Budi',
        'total_amount' => 185000
    ],
    [
        'transaction_id' => 1027,
        'transaction_date' => date('Y-m-d H:i:s', strtotime('-28 minutes')),
        'customer_name' => 'Siti',
        'total_amount' => 275000
    ],
    [
        'transaction_id' => 1026,
        'transaction_date' => date('Y-m-d H:i:s', strtotime('-41 minutes')),
        'customer_name' => 'Umum',
        'total_amount' => 125000
    ],
    [
        'transaction_id' => 1025,
        'transaction_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'customer_name' => 'Andi',
        'total_amount' => 342000
    ],
    [
        'transaction_id' => 1024,
        'transaction_date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'customer_name' => 'Umum',
        'total_amount' => 95000
    ]
];

/* =========================================================
   STOK MENIPIS
   ========================================================= */

$low_stock_products = [
    [
        'product_name' => 'Chicken Karage',
        'stock' => 4,
        'min_stock' => 10
    ],
    [
        'product_name' => 'French Fries 1 Kg',
        'stock' => 5,
        'min_stock' => 12
    ],
    [
        'product_name' => 'Nugget Ayam 500gr',
        'stock' => 6,
        'min_stock' => 15
    ],
    [
        'product_name' => 'Sosis Ayam',
        'stock' => 3,
        'min_stock' => 10
    ],
    [
        'product_name' => 'Dimsum Udang',
        'stock' => 7,
        'min_stock' => 10
    ]
];

/* =========================================================
   AKAN EXPIRED
   ========================================================= */

$expiring_batch_list = [
    [
        'product_name' => 'Nugget Ayam Fiesta',
        'expiry_date' => date('Y-m-d', strtotime('+2 days')),
        'remaining_quantity' => 18
    ],
    [
        'product_name' => 'Sosis Kanzler',
        'expiry_date' => date('Y-m-d', strtotime('+3 days')),
        'remaining_quantity' => 12
    ],
    [
        'product_name' => 'Dimsum Ayam',
        'expiry_date' => date('Y-m-d', strtotime('+5 days')),
        'remaining_quantity' => 8
    ],
    [
        'product_name' => 'Kentang Goreng',
        'expiry_date' => date('Y-m-d', strtotime('+7 days')),
        'remaining_quantity' => 15
    ]
];

/* =========================================================
   SUDAH EXPIRED
   ========================================================= */

$expired_batch_list = [
    [
        'product_name' => 'Bakso Sapi',
        'expiry_date' => date('Y-m-d', strtotime('-2 days')),
        'remaining_quantity' => 6
    ],
    [
        'product_name' => 'Chicken Wings',
        'expiry_date' => date('Y-m-d', strtotime('-5 days')),
        'remaining_quantity' => 4
    ]
];

/* =========================================================
   DEMO NAV ACTIVE
   ========================================================= */

$currentRequestPath = '/landing/demo.php';

function demo_nav_active(string $path): bool
{
    if ($path === '') {
        return false;
    }

    return false;
}

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
        Demo Dashboard - BEKUKU POS
    </title>

    <!-- AdminLTE -->
    <link
        rel="stylesheet"
        href="<?= demo_url('assets/css/adminlte.min.css') ?>"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <!-- BEKUKU CSS -->
    <link
        rel="stylesheet"
        href="<?= demo_url('assets/css/style.css') ?>?v=2026091722"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary dashboard-page">


<div class="app-wrapper">


    <!-- =====================================================
         NAVBAR
    ====================================================== -->

    <nav class="app-header navbar navbar-expand bg-body">

        <div class="container-fluid">

            <ul class="navbar-nav">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        data-lte-toggle="sidebar"
                        href="#"
                        role="button"
                    >

                        <i class="bi bi-list"></i>

                    </a>

                </li>

            </ul>


            <ul class="navbar-nav ms-auto">

                <li class="nav-item">

                    <span class="nav-link bekuku-user-pill">

                        <i class="bi bi-person-circle"></i>

                        <span>
                            Demo User
                        </span>

                        <small>
                            Administrator
                        </small>

                    </span>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link bekuku-logout-link"
                        href="<?= demo_url('landing/index.php') ?>"
                        title="Kembali"
                        data-demo-back
                    >

                        <i class="bi bi-box-arrow-right"></i>

                    </a>

                </li>

            </ul>

        </div>

    </nav>


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside
        class="app-sidebar bg-body-secondary shadow role-admin"
        data-bs-theme="dark"
    >

        <!-- Brand -->

        <div class="sidebar-brand">

            <a
                href="<?= demo_url('landing/demo.php') ?>"
                class="brand-link"
            >

                <span class="brand-text fw-light">
                    BEKUKU
                </span>

            </a>

        </div>


        <!-- Sidebar Wrapper -->

        <div class="sidebar-wrapper">

            <nav class="mt-2">

                <ul
                    class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    role="menu"
                    data-accordion="false"
                >


                    <!-- DASHBOARD -->

                    <li class="nav-item">

                        <a
                            href="<?= demo_url('landing/demo.php') ?>"
                            class="nav-link active"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-speedometer2"></i>

                            <p>
                                Dashboard
                            </p>

                        </a>

                    </li>


                    <!-- TRANSAKSI -->

                    <li class="nav-header">
                        TRANSAKSI
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-cart-plus"></i>

                            <p>
                                Transaksi Baru
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-receipt"></i>

                            <p>
                                Riwayat Transaksi
                            </p>

                        </a>

                    </li>


                    <!-- MASTER DATA -->

                    <li class="nav-header">
                        MASTER DATA
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-tags"></i>

                            <p>
                                Kategori
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-truck"></i>

                            <p>
                                Supplier
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-people"></i>

                            <p>
                                Customer
                            </p>

                        </a>

                    </li>


                    <!-- PRODUK -->

                    <li class="nav-header">
                        PRODUK
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-box-seam"></i>

                            <p>
                                Data Produk
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-arrow-left-right"></i>

                            <p>
                                Riwayat Stok
                            </p>

                        </a>

                    </li>


                    <!-- PERSEDIAAN -->

                    <li class="nav-header">
                        PERSEDIAAN
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-cart-check"></i>

                            <p>
                                Pembelian
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-calendar-x"></i>

                            <p>
                                Batch &amp; Expired
                            </p>

                        </a>

                    </li>


                    <!-- LAPORAN -->

                    <li class="nav-header">
                        LAPORAN
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-bar-chart"></i>

                            <p>
                                Laporan Penjualan
                            </p>

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-boxes"></i>

                            <p>
                                Laporan Stok
                            </p>

                        </a>

                    </li>


                    <!-- ADMINISTRASI -->

                    <li class="nav-header">
                        ADMINISTRASI
                    </li>


                    <li class="nav-item">

                        <a
                            href="#"
                            class="nav-link"
                            data-demo-action
                        >

                            <i class="nav-icon bi bi-person-gear"></i>

                            <p>
                                Pengguna
                            </p>

                        </a>

                    </li>


                </ul>

            </nav>

        </div>

    </aside>


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

                            Administrator

                        </div>


                        <p class="dashboard-hero-subtitle">

                            Selamat datang, Demo User.
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

                            <?= rupiah_demo($total_sales_today) ?>

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


                <!-- Perlu Perhatian -->

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
                            href="#"
                            class="dashboard-action-button"
                            data-demo-action
                        >

                            <i class="bi bi-cart-plus"></i>

                            Transaksi Baru

                        </a>


                        <a
                            href="#"
                            class="dashboard-action-button"
                            data-demo-action
                        >

                            <i class="bi bi-box-seam"></i>

                            Tambah Produk

                        </a>


                        <a
                            href="#"
                            class="dashboard-action-button"
                            data-demo-action
                        >

                            <i class="bi bi-truck"></i>

                            Pembelian

                        </a>


                        <a
                            href="#"
                            class="dashboard-action-button"
                            data-demo-action
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
                                    data-labels="<?= e_demo(
                                        json_encode(
                                            $chart_labels
                                        )
                                    ) ?>"
                                    data-values="<?= e_demo(
                                        json_encode(
                                            $chart_values
                                        )
                                    ) ?>"
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

                                                        <?= e_demo(
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

                                                    <?= e_demo(
                                                        $transaction[
                                                            'customer_name'
                                                        ] ?? 'Umum'
                                                    ) ?>

                                                </td>


                                                <td class="text-end">

                                                    <strong>

                                                        <?= rupiah_demo(
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

                                            <?= e_demo(
                                                $product['product_name']
                                            ) ?>

                                        </div>


                                        <div class="alert-text">

                                            Stok:

                                            <strong>

                                                <?= (int)
                                                    $product['stock'] ?>

                                            </strong>

                                            /

                                            minimum

                                            <?= (int)
                                                $product['min_stock'] ?>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>


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

                                            <?= e_demo(
                                                $batch['product_name']
                                            ) ?>

                                        </div>


                                        <div class="alert-text">

                                            Expired:

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $batch['expiry_date']
                                                )
                                            ) ?>

                                            · Stok:

                                            <?= (int)
                                                $batch[
                                                    'remaining_quantity'
                                                ] ?>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>


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

                                            <?= e_demo(
                                                $batch['product_name']
                                            ) ?>

                                        </div>


                                        <div class="alert-text">

                                            Expired:

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $batch['expiry_date']
                                                )
                                            ) ?>

                                            · Stok:

                                            <?= (int)
                                                $batch[
                                                    'remaining_quantity'
                                                ] ?>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>


                        </div>

                    </div>

                </div>


            </div>


        </div>


    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="app-footer">

        <strong>
            BEKUKU POS
        </strong>

        &nbsp;&mdash;&nbsp;

        Sistem Point of Sale Frozen Food

    </footer>


</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="<?= demo_url('assets/js/adminlte.min.js') ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= demo_url('assets/dashboard/js/dashboard.js') ?>?v=2026091721"></script>

<script src="<?= demo_url('assets/js/ui.js') ?>?v=2026091722"></script>


<script>

/*
|--------------------------------------------------------------------------
| DEMO ACTION
|--------------------------------------------------------------------------
| Semua menu demo tidak boleh masuk ke halaman aplikasi asli.
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-demo-action]').forEach(function (element) {

        element.addEventListener('click', function (event) {

            event.preventDefault();

            const oldNotice = document.querySelector('.bekuku-demo-notice');

            if (oldNotice) {
                oldNotice.remove();
            }

            const notice = document.createElement('div');

            notice.className = 'bekuku-demo-notice';

            notice.innerHTML =
                '<div class="bekuku-demo-notice-icon">' +
                    '<i class="bi bi-info-circle-fill"></i>' +
                '</div>' +
                '<div>' +
                    '<strong>Mode Demo</strong>' +
                    '<p>Fitur ini tersedia pada aplikasi BEKUKU POS setelah login.</p>' +
                '</div>' +
                '<button type="button" aria-label="Tutup">' +
                    '<i class="bi bi-x"></i>' +
                '</button>';

            document.body.appendChild(notice);

            notice.querySelector('button').addEventListener('click', function () {
                notice.remove();
            });

            setTimeout(function () {

                if (notice.parentNode) {
                    notice.remove();
                }

            }, 4000);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | KEMBALI KE LANDING
    |--------------------------------------------------------------------------
    */

    const backButton = document.querySelector('[data-demo-back]');

    if (backButton) {

        backButton.addEventListener('click', function (event) {

            event.preventDefault();

            window.location.href =
                <?= json_encode(
                    demo_url('landing/index.php'),
                    JSON_UNESCAPED_SLASHES
                ) ?>;

        });

    }

});


/*
|--------------------------------------------------------------------------
| DEMO NOTICE STYLE
|--------------------------------------------------------------------------
| Hanya untuk pesan interaksi demo.
| Tidak mengubah tampilan dashboard.
|--------------------------------------------------------------------------
*/

const demoStyle = document.createElement('style');

demoStyle.textContent = `

.bekuku-demo-notice {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 99999;

    display: flex;
    align-items: center;
    gap: 14px;

    min-width: 330px;
    max-width: 430px;

    padding: 16px 18px;

    border-radius: 14px;

    background: #ffffff;

    box-shadow:
        0 12px 40px rgba(0,0,0,.18);

    border: 1px solid rgba(0,0,0,.08);
}

.bekuku-demo-notice-icon {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 50%;

    background: rgba(98,214,197,.14);

    color: #62d6c5;

    font-size: 20px;
}

.bekuku-demo-notice strong {
    display: block;

    margin-bottom: 3px;

    font-size: 14px;
}

.bekuku-demo-notice p {
    margin: 0;

    font-size: 13px;

    color: #6c757d;
}

.bekuku-demo-notice button {
    margin-left: auto;

    border: 0;

    background: transparent;

    color: #6c757d;

    font-size: 20px;

    cursor: pointer;
}

@media (max-width: 576px) {

    .bekuku-demo-notice {
        left: 15px;
        right: 15px;
        bottom: 15px;

        min-width: 0;
        width: auto;
    }

}

`;

document.head.appendChild(demoStyle);

</script>


</body>

</html>