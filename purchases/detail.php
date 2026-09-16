<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Ambil ID pembelian
|--------------------------------------------------------------------------
*/

$purchase_id = (int) ($_GET['id'] ?? 0);


if ($purchase_id <= 0) {
    die("ID pembelian tidak valid.");
}


/*
|--------------------------------------------------------------------------
| Ambil data pembelian
|--------------------------------------------------------------------------
*/

$stmtPurchase = $conn->prepare("
    SELECT
        p.purchase_id,
        p.purchase_date,
        p.invoice_number,
        p.total_amount,
        p.payment_amount,
        p.remaining_amount,
        p.status,
        p.note,
        s.supplier_name
    FROM purchases p
    INNER JOIN suppliers s
        ON p.supplier_id = s.supplier_id
    WHERE p.purchase_id = :purchase_id
");

$stmtPurchase->execute([
    ':purchase_id' => $purchase_id
]);

$purchase = $stmtPurchase->fetch(PDO::FETCH_ASSOC);


if (!$purchase) {
    die("Data pembelian tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| Ambil detail produk + batch
|--------------------------------------------------------------------------
*/

$stmtDetails = $conn->prepare("
    SELECT
        pd.purchase_detail_id,
        pd.product_id,
        pd.quantity,
        pd.purchase_price,
        pd.subtotal,

        pr.product_name,
        pr.sku,

        b.batch_number,
        b.expiry_date,
        b.remaining_quantity

    FROM purchase_details pd

    INNER JOIN products pr
        ON pd.product_id = pr.product_id

    LEFT JOIN batches b
        ON pd.purchase_detail_id = b.purchase_detail_id

    WHERE pd.purchase_id = :purchase_id

    ORDER BY pd.purchase_detail_id ASC
");

$stmtDetails->execute([
    ':purchase_id' => $purchase_id
]);

$details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

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
        Detail Pembelian #<?= $purchase_id; ?> - BEKUKU POS
    </title>


    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/adminlte.min.css') ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <!-- HEADER -->

        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-6">

                        <h3 class="mb-0">

                            Detail Pembelian

                            #<?= $purchase_id; ?>

                        </h3>

                    </div>


                    <div class="col-sm-6">

                        <ol class="breadcrumb float-sm-end">

                            <li class="breadcrumb-item">

                                <a href="<?= bekuku_url() ?>">
                                    Dashboard
                                </a>

                            </li>

                            <li class="breadcrumb-item">

                                <a href="index.php">
                                    Pembelian
                                </a>

                            </li>

                            <li class="breadcrumb-item active">

                                Detail

                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </div>


        <!-- CONTENT -->

        <div class="app-content">

            <div class="container-fluid">


                <!-- INFORMASI PEMBELIAN -->

                <div class="card mb-3">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-info-circle me-2"></i>

                            Informasi Pembelian

                        </h3>

                    </div>


                    <div class="card-body">


                        <div class="row">


                            <div class="col-md-4 mb-3">

                                <strong>
                                    Supplier
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $purchase['supplier_name']
                                ); ?>

                            </div>


                            <div class="col-md-4 mb-3">

                                <strong>
                                    Tanggal Pembelian
                                </strong>

                                <br>

                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime($purchase['purchase_date'])
                                ); ?>

                            </div>


                            <div class="col-md-4 mb-3">

                                <strong>
                                    No. Invoice
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $purchase['invoice_number'] ?? '-'
                                ); ?>

                            </div>


                            <div class="col-md-4 mb-3">

                                <strong>
                                    Status
                                </strong>

                                <br>


                                <?php if ($purchase['status'] === 'lunas'): ?>

                                    <span class="badge text-bg-success">

                                        Lunas

                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-warning">

                                        Hutang

                                    </span>

                                <?php endif; ?>


                            </div>


                            <div class="col-md-8 mb-3">

                                <strong>
                                    Catatan
                                </strong>

                                <br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $purchase['note'] ?? '-'
                                    )
                                ); ?>

                            </div>


                        </div>


                    </div>

                </div>


                <!-- DETAIL PRODUK -->

                <div class="card mb-3">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-box-seam me-2"></i>

                            Produk yang Dibeli

                        </h3>

                    </div>


                    <div class="card-body">


                        <div class="table-responsive">


                            <table
                                class="table table-bordered table-striped align-middle"
                            >


                                <thead>

                                    <tr>

                                        <th width="50">
                                            No
                                        </th>

                                        <th>
                                            Produk
                                        </th>

                                        <th>
                                            SKU
                                        </th>

                                        <th>
                                            Jumlah
                                        </th>

                                        <th>
                                            Harga Beli
                                        </th>

                                        <th>
                                            Subtotal
                                        </th>

                                        <th>
                                            Batch
                                        </th>

                                        <th>
                                            Expired
                                        </th>

                                        <th>
                                            Sisa Batch
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php if (count($details) > 0): ?>


                                    <?php foreach ($details as $index => $detail): ?>


                                        <tr>


                                            <td>

                                                <?= $index + 1; ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $detail['product_name']
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $detail['sku'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= number_format(
                                                    $detail['quantity'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                Rp
                                                <?= number_format(
                                                    $detail['purchase_price'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                Rp
                                                <?= number_format(
                                                    $detail['subtotal'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $detail['batch_number'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>


                                                <?php if (!empty($detail['expiry_date'])): ?>

                                                    <?= date(
                                                        'd-m-Y',
                                                        strtotime(
                                                            $detail['expiry_date']
                                                        )
                                                    ); ?>

                                                <?php else: ?>

                                                    -

                                                <?php endif; ?>


                                            </td>


                                            <td>

                                                <?= number_format(
                                                    $detail['remaining_quantity'] ?? 0,
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="9"
                                            class="text-center"
                                        >

                                            Tidak ada detail produk.

                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>


                            </table>

                        </div>


                    </div>

                </div>


                <!-- PEMBAYARAN -->

                <div class="card mb-3">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-cash-stack me-2"></i>

                            Pembayaran

                        </h3>

                    </div>


                    <div class="card-body">


                        <div class="row justify-content-end">


                            <div class="col-md-4">


                                <table class="table table-borderless">


                                    <tr>

                                        <th>
                                            Total Pembelian
                                        </th>

                                        <td class="text-end">

                                            Rp
                                            <?= number_format(
                                                $purchase['total_amount'],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Dibayar
                                        </th>

                                        <td class="text-end">

                                            Rp
                                            <?= number_format(
                                                $purchase['payment_amount'],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Sisa Hutang
                                        </th>

                                        <td class="text-end fw-bold">

                                            Rp
                                            <?= number_format(
                                                $purchase['remaining_amount'],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </td>

                                    </tr>


                                </table>


                            </div>

                        </div>


                    </div>

                </div>


                <!-- BUTTON -->

                <div class="d-flex justify-content-between mb-4">


                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >

                        <i class="bi bi-arrow-left me-1"></i>

                        Kembali

                    </a>


                    <button
                        type="button"
                        class="btn btn-primary"
                        data-print
                    >

                        <i class="bi bi-printer me-1"></i>

                        Cetak

                    </button>


                </div>


            </div>

        </div>


    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<script src="<?= bekuku_url('assets/js/ui.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

</body>

</html>
