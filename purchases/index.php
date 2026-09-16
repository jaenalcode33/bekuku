<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";

$stmt = $conn->query("
    SELECT
        p.purchase_id,
        p.purchase_date,
        p.invoice_number,
        p.total_amount,
        p.payment_amount,
        p.remaining_amount,
        p.status,
        s.supplier_name
    FROM purchases p
    INNER JOIN suppliers s
        ON p.supplier_id = s.supplier_id
    ORDER BY p.purchase_id DESC
");

$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pembelian - BEKUKU POS</title>

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


    <!-- CONTENT -->

    <main class="app-main">


        <!-- HEADER -->

        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-6">

                        <h3 class="mb-0">
                            Pembelian
                        </h3>

                    </div>


                    <div class="col-sm-6">

                        <ol class="breadcrumb float-sm-end">

                            <li class="breadcrumb-item">

                                <a href="<?= bekuku_url() ?>">
                                    Dashboard
                                </a>

                            </li>

                            <li class="breadcrumb-item active">
                                Pembelian
                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </div>


        <!-- CONTENT BODY -->

        <div class="app-content">

            <div class="container-fluid">


                <!-- CARD -->

                <div class="card">


                    <!-- CARD HEADER -->

                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <h3 class="card-title">

                                <i class="bi bi-cart-check me-2"></i>

                                Riwayat Pembelian

                            </h3>


                            <a
                                href="create.php"
                                class="btn btn-primary"
                                data-modal-url="create.php"
                                data-modal-title="Tambah Pembelian"
                            >

                                <i class="bi bi-plus-lg me-1"></i>

                                Pembelian Baru

                            </a>

                        </div>

                    </div>


                    <!-- CARD BODY -->

                    <div class="card-body">


                        <div class="table-responsive">


                            <table class="table table-bordered table-striped">


                                <thead>

                                    <tr>

                                        <th width="60">
                                            No
                                        </th>

                                        <th>
                                            Tanggal
                                        </th>

                                        <th>
                                            Supplier
                                        </th>

                                        <th>
                                            No. Invoice
                                        </th>

                                        <th>
                                            Total
                                        </th>

                                        <th>
                                            Dibayar
                                        </th>

                                        <th>
                                            Sisa
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th width="100">
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php if (count($purchases) > 0): ?>


                                    <?php foreach ($purchases as $index => $purchase): ?>


                                        <tr>


                                            <td>
                                                <?= $index + 1; ?>
                                            </td>


                                            <td>

                                                <?= date(
                                                    'd-m-Y H:i',
                                                    strtotime($purchase['purchase_date'])
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $purchase['supplier_name']
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $purchase['invoice_number'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>

                                                Rp
                                                <?= number_format(
                                                    $purchase['total_amount'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                Rp
                                                <?= number_format(
                                                    $purchase['payment_amount'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                Rp
                                                <?= number_format(
                                                    $purchase['remaining_amount'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>


                                                <?php if ($purchase['status'] === 'lunas'): ?>

                                                    <span class="badge text-bg-success">
                                                        Lunas
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge text-bg-warning">
                                                        Hutang
                                                    </span>

                                                <?php endif; ?>


                                            </td>


                                            <td>

                                                <a
                                                    href="detail.php?id=<?= $purchase['purchase_id']; ?>"
                                                    class="btn btn-sm btn-info"
                                                >

                                                    <i class="bi bi-eye"></i>

                                                </a>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="9"
                                            class="text-center text-muted"
                                        >

                                            Belum ada data pembelian.

                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>


                            </table>

                        </div>


                    </div>


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