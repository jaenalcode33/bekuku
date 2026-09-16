<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| AMBIL DATA STOK
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT
        p.product_id,
        p.sku,
        p.product_name,
        p.purchase_price,
        p.selling_price,
        p.unit,
        p.stock,
        p.min_stock,
        p.status,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.category_id
    ORDER BY p.product_name ASC
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HITUNG RINGKASAN STOK
|--------------------------------------------------------------------------
*/

$total_products = count($products);

$total_stock = 0;

$low_stock = 0;

$empty_stock = 0;

$active_products = 0;

$inactive_products = 0;
$printed_at = date('d-m-Y H:i');
$currentUser = bekuku_user();
$attention_products = array_values(array_filter($products, static function (array $product): bool {
    return (int) $product['stock'] <= (int) $product['min_stock'];
}));
$empty_products = array_values(array_filter($products, static function (array $product): bool {
    return (int) $product['stock'] <= 0;
}));


foreach ($products as $product) {

    $stock = (int) $product['stock'];

    $total_stock += $stock;


    if ($stock <= (int) $product['min_stock']) {

        $low_stock++;

    }


    if ($stock <= 0) {

        $empty_stock++;

    }


    if ($product['status'] === 'aktif') {

        $active_products++;

    } else {

        $inactive_products++;

    }
}


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

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laporan Stok - BEKUKU</title>


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
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091623"
    >
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/report-document.css') ?>?v=2026091645">

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary report-page">


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
            <section class="dashboard-hero">
                <div class="dashboard-hero-content">
                    <div>
                        <div class="dashboard-hero-title">
                            <i class="bi bi-box-seam me-2"></i>Laporan Stok
                        </div>
                        <p class="dashboard-hero-subtitle">
                            Pantau kondisi stok seluruh produk BEKUKU.
                        </p>
                    </div>
                    <div class="dashboard-hero-actions">
                        <a href="../products/" class="btn dashboard-product-add-button">
                            <i class="bi bi-box-seam me-1"></i>Data Produk
                        </a>
                        <button type="button" data-print class="btn dashboard-product-add-button">
                            <i class="bi bi-printer me-1"></i>Cetak Laporan
                        </button>
                    </div>
                </div>
            </section>
        </div>


        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <div class="app-content report-content">

            <div class="container-fluid dashboard-wrapper">


                <!-- =================================================
                     HEADER CARD
                ================================================== -->

                <div class="d-none">

                    <div class="card-body">

                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
                        >

                            <div>

                                <h5 class="mb-1">

                                    <i class="bi bi-clipboard-data me-2"></i>

                                    Laporan Persediaan Stok

                                </h5>

                                <p class="text-muted mb-0">

                                    Pantau kondisi stok seluruh produk
                                    BEKUKU secara keseluruhan.

                                </p>

                            </div>


                            <div
                                class="d-flex gap-2"
                            >

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     RINGKASAN
                ================================================== -->

                <div class="row">


                    <!-- Total Produk -->

                    <div class="col-xl-4 col-md-6">

                        <div class="dashboard-stat stat-blue">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Produk

                                        </p>

                                        <h3 class="mb-0">

                                            <?= $total_products; ?>

                                        </h3>

                                        <small class="text-muted">

                                            produk terdaftar

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-box-seam fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Total Stok -->

                    <div class="col-xl-4 col-md-6">

                        <div class="dashboard-stat stat-green">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Stok

                                        </p>

                                        <h3 class="mb-0 text-primary">

                                            <?= number_format(
                                                $total_stock,
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </h3>

                                        <small class="text-muted">

                                            seluruh produk

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-stack fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Stok Menipis -->

                    <div class="col-xl-4 col-md-6">

                        <div class="dashboard-stat stat-yellow">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Stok Menipis

                                        </p>

                                        <h3 class="mb-0 text-warning">

                                            <?= $low_stock; ?>

                                        </h3>

                                        <small class="text-muted">

                                            perlu diperhatikan

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-exclamation-triangle fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Stok Habis -->

                    <div class="col-xl-4 col-md-6 d-none">

                        <div class="dashboard-stat stat-blue">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Stok Habis

                                        </p>

                                        <h3 class="mb-0 text-danger">

                                            <?= $empty_stock; ?>

                                        </h3>

                                        <small class="text-muted">

                                            perlu restock

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-x-circle fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     STATUS PRODUK
                ================================================== -->

                <div class="row d-none">


                    <div class="col-md-6 mb-3">

                        <div class="alert alert-success mb-0">

                            <div
                                class="d-flex align-items-center"
                            >

                                <i
                                    class="bi bi-check-circle fs-4 me-3"
                                ></i>

                                <div>

                                    <strong>
                                        Produk Aktif
                                    </strong>

                                    <div>

                                        <?= $active_products; ?>
                                        produk aktif.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-6 mb-3">

                        <div class="alert alert-secondary mb-0">

                            <div
                                class="d-flex align-items-center"
                            >

                                <i
                                    class="bi bi-pause-circle fs-4 me-3"
                                ></i>

                                <div>

                                    <strong>
                                        Produk Nonaktif
                                    </strong>

                                    <div>

                                        <?= $inactive_products; ?>
                                        produk nonaktif.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     DAFTAR PRODUK
                ================================================== -->

                <div class="card products-list-card categories-list-card">


                    <div class="card-header d-flex align-items-center justify-content-between">

                        <h3 class="card-title">

                            <i class="bi bi-list-ul me-2"></i>

                            Daftar Stok Produk

                        </h3>


                        <span class="products-count"><?= number_format($total_products); ?> produk</span>

                    </div>


                    <div class="card-body p-0">


                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead>

                                    <tr>

                                        <th
                                            class="text-center"
                                            style="width: 60px;"
                                        >

                                            No

                                        </th>


                                        <th
                                            style="width: 120px;"
                                        >

                                            SKU

                                        </th>


                                        <th>

                                            Produk

                                        </th>


                                        <th>

                                            Kategori

                                        </th>


                                        <th class="text-end">

                                            Harga Beli

                                        </th>


                                        <th class="text-end">

                                            Harga Jual

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 110px;"
                                        >

                                            Stok

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 110px;"
                                        >

                                            Min. Stok

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 120px;"
                                        >

                                            Status

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php if (empty($products)): ?>


                                        <tr>

                                            <td
                                                colspan="9"
                                                class="text-center py-5"
                                            >

                                                <div class="text-muted">

                                                    <i
                                                        class="bi bi-box-seam"
                                                        style="
                                                            font-size: 45px;
                                                        "
                                                    ></i>


                                                    <div class="mt-2">

                                                        Belum ada produk.

                                                    </div>


                                                    <small>

                                                        Tambahkan produk
                                                        terlebih dahulu.

                                                    </small>

                                                </div>

                                            </td>

                                        </tr>


                                    <?php else: ?>


                                        <?php $no = 1; ?>


                                        <?php foreach ($products as $product): ?>


                                            <?php

                                            $stock =
                                                (int) $product['stock'];

                                            $min_stock =
                                                (int) $product['min_stock'];

                                            ?>


                                            <tr>


                                                <!-- No -->

                                                <td
                                                    class="text-center text-muted"
                                                >

                                                    <?= $no++; ?>

                                                </td>


                                                <!-- SKU -->

                                                <td>

                                                    <?php if (
                                                        !empty(
                                                            $product['sku']
                                                        )
                                                    ): ?>

                                                        <span
                                                            class="badge bg-light text-dark border"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $product['sku']
                                                            ); ?>

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="text-muted">

                                                            -

                                                        </span>

                                                    <?php endif; ?>

                                                </td>


                                                <!-- Produk -->

                                                <td>

                                                    <div
                                                        class="d-flex align-items-center"
                                                    >


                                                        <div
                                                            class="d-flex align-items-center justify-content-center rounded-circle bg-light text-primary me-2"
                                                            style="
                                                                width: 38px;
                                                                height: 38px;
                                                            "
                                                        >

                                                            <i class="bi bi-box"></i>

                                                        </div>


                                                        <div>

                                                            <div class="fw-semibold">

                                                                <?= htmlspecialchars(
                                                                    $product['product_name']
                                                                ); ?>

                                                            </div>


                                                            <small class="text-muted">

                                                                Satuan:
                                                                <?= htmlspecialchars(
                                                                    $product['unit']
                                                                ); ?>

                                                            </small>

                                                        </div>


                                                    </div>

                                                </td>


                                                <!-- Kategori -->

                                                <td>

                                                    <?php if (
                                                        !empty(
                                                            $product['category_name']
                                                        )
                                                    ): ?>

                                                        <span
                                                            class="badge bg-light text-dark border"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $product['category_name']
                                                            ); ?>

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="text-muted">

                                                            -

                                                        </span>

                                                    <?php endif; ?>

                                                </td>


                                                <!-- Harga Beli -->

                                                <td class="text-end">

                                                    <?= rupiah(
                                                        $product['purchase_price']
                                                    ); ?>

                                                </td>


                                                <!-- Harga Jual -->

                                                <td class="text-end">

                                                    <span class="fw-semibold">

                                                        <?= rupiah(
                                                            $product['selling_price']
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Stok -->

                                                <td class="text-center">


                                                    <?php if ($stock <= 0): ?>


                                                        <span
                                                            class="badge bg-danger px-3 py-2"
                                                        >

                                                            <i
                                                                class="bi bi-x-circle me-1"
                                                            ></i>

                                                            <?= number_format(
                                                                $stock,
                                                                0,
                                                                ',',
                                                                '.'
                                                            ); ?>

                                                            <?= htmlspecialchars(
                                                                $product['unit']
                                                            ); ?>

                                                        </span>


                                                    <?php elseif (
                                                        $stock <= $min_stock
                                                    ): ?>


                                                        <span
                                                            class="badge bg-warning text-dark px-3 py-2"
                                                        >

                                                            <i
                                                                class="bi bi-exclamation-triangle me-1"
                                                            ></i>

                                                            <?= number_format(
                                                                $stock,
                                                                0,
                                                                ',',
                                                                '.'
                                                            ); ?>

                                                            <?= htmlspecialchars(
                                                                $product['unit']
                                                            ); ?>

                                                        </span>


                                                    <?php else: ?>


                                                        <span
                                                            class="badge bg-success px-3 py-2"
                                                        >

                                                            <i
                                                                class="bi bi-check-circle me-1"
                                                            ></i>

                                                            <?= number_format(
                                                                $stock,
                                                                0,
                                                                ',',
                                                                '.'
                                                            ); ?>

                                                            <?= htmlspecialchars(
                                                                $product['unit']
                                                            ); ?>

                                                        </span>


                                                    <?php endif; ?>


                                                </td>


                                                <!-- Minimum Stok -->

                                                <td class="text-center">

                                                    <span class="text-muted">

                                                        <?= number_format(
                                                            $min_stock,
                                                            0,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                        <?= htmlspecialchars(
                                                            $product['unit']
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- Status -->

                                                <td class="text-center">


                                                    <?php if ($stock <= 0): ?>


                                                        <span
                                                            class="badge bg-danger px-2 py-1"
                                                        >

                                                            <i
                                                                class="bi bi-x-circle me-1"
                                                            ></i>

                                                            HABIS

                                                        </span>


                                                    <?php elseif (
                                                        $stock <= $min_stock
                                                    ): ?>


                                                        <span
                                                            class="badge bg-warning text-dark px-2 py-1"
                                                        >

                                                            <i
                                                                class="bi bi-exclamation-triangle me-1"
                                                            ></i>

                                                            MENIPIS

                                                        </span>


                                                    <?php else: ?>


                                                        <span
                                                            class="badge bg-success px-2 py-1"
                                                        >

                                                            <i
                                                                class="bi bi-check-circle me-1"
                                                            ></i>

                                                            AMAN

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


                    <?php if (!empty($products)): ?>

                        <div class="card-footer">

                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
                            >

                                <small class="text-muted">

                                    <i class="bi bi-info-circle me-1"></i>

                                    Menampilkan
                                    <?= $total_products; ?>
                                    produk.

                                </small>


                                <div>

                                    <span class="text-success me-3">

                                        <i class="bi bi-check-circle me-1"></i>

                                        <?= $active_products; ?> Aktif

                                    </span>


                                    <span class="text-secondary">

                                        <i class="bi bi-pause-circle me-1"></i>

                                        <?= $inactive_products; ?> Nonaktif

                                    </span>

                                </div>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>


            </div>

        </div>

        <div class="report-document-toolbar no-print">
            <a href="javascript:history.back()" class="btn dashboard-secondary-action">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <button type="button" data-print class="btn dashboard-product-add-button">
                <i class="bi bi-printer me-1"></i>Cetak Laporan
            </button>
        </div>

        <article class="print-report" aria-label="Dokumen laporan stok">
            <header class="print-report-header">
                <div>
                    <div class="print-report-brand">BEKUKU</div>
                    <div>FROZEN FOOD</div>
                    <div>POINT OF SALE</div>
                </div>
                <div class="print-report-title">
                    <h1>LAPORAN STOK PRODUK</h1>
                    <p>Ringkasan kondisi persediaan produk BEKUKU</p>
                </div>
            </header>
            <div class="print-report-meta">
                <span>Tanggal Cetak: <?= htmlspecialchars($printed_at); ?></span>
                <span>Dicetak Oleh: <?= htmlspecialchars($currentUser['name'] ?? $currentUser['username'] ?? 'Administrator'); ?></span>
            </div>
            <section class="print-report-summary">
                <div><span>TOTAL PRODUK</span><strong><?= number_format($total_products); ?> produk</strong></div>
                <div><span>TOTAL STOK</span><strong><?= number_format($total_stock); ?> pcs</strong></div>
                <div><span>STOK MENIPIS</span><strong><?= number_format($low_stock); ?> produk</strong></div>
                <div><span>STOK HABIS</span><strong><?= number_format($empty_stock); ?> produk</strong></div>
            </section>
            <h2 class="print-report-section-title">DATA STOK PRODUK</h2>
            <table class="print-report-table">
                <thead>
                    <tr>
                        <th>No</th><th>Kode</th><th>Nama Produk</th><th>Kategori</th>
                        <th>Harga Modal</th><th>Harga Jual</th><th>Stok</th><th>Satuan</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): ?>
                        <?php
                        $stock = (int) $product['stock'];
                        $statusText = $stock <= 0 ? '✕ HABIS' : ($stock <= (int) $product['min_stock'] ? '⚠ MENIPIS' : '✓ AMAN');
                        ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($product['sku'] ?: '-'); ?></td>
                            <td><?= htmlspecialchars($product['product_name']); ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?: '-'); ?></td>
                            <td><?= rupiah($product['purchase_price']); ?></td>
                            <td><?= rupiah($product['selling_price']); ?></td>
                            <td><?= number_format($stock); ?></td>
                            <td><?= htmlspecialchars($product['unit']); ?></td>
                            <td><?= $statusText; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($attention_products): ?>
                <section class="print-report-callout">
                    <h2>PERHATIAN STOK</h2>
                    <p>Beberapa produk memerlukan perhatian karena stok berada pada atau di bawah batas minimum.</p>
                    <?php foreach ($attention_products as $product): ?>
                        <div><span><?= htmlspecialchars($product['product_name']); ?></span><span><?= (int) $product['stock']; ?> / min <?= (int) $product['min_stock']; ?></span></div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
            <?php if ($empty_products): ?>
                <section class="print-report-callout">
                    <h2>STOK HABIS</h2>
                    <?php foreach ($empty_products as $product): ?>
                        <div><span><?= htmlspecialchars($product['product_name']); ?></span><span><?= htmlspecialchars($product['sku'] ?: '-'); ?> · <?= htmlspecialchars($product['category_name'] ?: '-'); ?></span></div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
            <footer class="print-report-footer">
                <span>BEKUKU · POINT OF SALE</span>
                <span>Laporan Stok Produk · Dicetak <?= htmlspecialchars($printed_at); ?></span>
            </footer>
        </article>

    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- AdminLTE JS -->

<script src="<?= bekuku_url('assets/js/ui.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>


</body>

</html>
