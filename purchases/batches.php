<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| Ambil semua batch
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT
        b.batch_id,
        b.batch_number,
        b.expiry_date,
        b.quantity,
        b.remaining_quantity,
        b.created_at,

        p.product_name,
        p.sku,

        s.supplier_name,

        pu.purchase_id,
        pu.invoice_number

    FROM batches b

    INNER JOIN products p
        ON b.product_id = p.product_id

    INNER JOIN purchase_details pd
        ON b.purchase_detail_id = pd.purchase_detail_id

    INNER JOIN purchases pu
        ON pd.purchase_id = pu.purchase_id

    INNER JOIN suppliers s
        ON pu.supplier_id = s.supplier_id

    ORDER BY
        CASE
            WHEN b.expiry_date IS NULL THEN 1
            ELSE 0
        END ASC,
        b.expiry_date ASC,
        b.batch_id DESC
");

$batches = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Fungsi menentukan status expired
|--------------------------------------------------------------------------
*/

function getBatchStatus($expiry_date, $remaining_quantity)
{
    if ($remaining_quantity <= 0) {
        return [
            'label' => 'Habis',
            'class' => 'text-bg-secondary'
        ];
    }

    if (empty($expiry_date)) {
        return [
            'label' => 'Tidak Ada Expired',
            'class' => 'text-bg-secondary'
        ];
    }

    $today = new DateTime();
    $expiry = new DateTime($expiry_date);

    /*
    | Sudah expired
    */

    if ($expiry < $today) {
        return [
            'label' => 'Expired',
            'class' => 'text-bg-danger'
        ];
    }

    /*
    | Hitung selisih hari
    */

    $difference = $today->diff($expiry)->days;


    /*
    | Expired dalam 30 hari
    */

    if ($difference <= 30) {
        return [
            'label' => 'Segera Expired',
            'class' => 'text-bg-warning'
        ];
    }


    /*
    | Masih aman
    */

    return [
        'label' => 'Aman',
        'class' => 'text-bg-success'
    ];
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
        Batch Produk - BEKUKU POS
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
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091620"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div class="container-fluid dashboard-wrapper">
            <section class="dashboard-hero">
                <div class="dashboard-hero-content">
                    <div>
                        <div class="dashboard-hero-title">
                            <i class="bi bi-calendar-x me-2"></i>Batch & Expired
                        </div>
                        <p class="dashboard-hero-subtitle">
                            Pantau jumlah stok dan tanggal kedaluwarsa setiap batch produk.
                        </p>
                    </div>
                    <div class="dashboard-hero-actions">
                        <a href="<?= bekuku_url('purchases/') ?>" class="btn dashboard-secondary-action">
                            <i class="bi bi-arrow-left me-1"></i>Pembelian
                        </a>
                    </div>
                </div>
            </section>


                <!-- CARD -->

                <div class="card batches-list-card">


                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center">


                            <h3 class="card-title">

                                <i class="bi bi-box-seam me-2"></i>

                                Daftar Batch Produk

                            </h3>


                            <a href="<?= bekuku_url('purchases/') ?>" class="btn btn-sm dashboard-secondary-action">

                                <i class="bi bi-arrow-left me-1"></i>

                                Kembali

                            </a>


                        </div>

                    </div>


                    <div class="card-body">


                        <div class="table-responsive">


                            <table
                                class="table table-hover align-middle"
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
                                            Batch
                                        </th>

                                        <th>
                                            Expired
                                        </th>

                                        <th>
                                            Qty Awal
                                        </th>

                                        <th>
                                            Sisa
                                        </th>

                                        <th>
                                            Supplier
                                        </th>

                                        <th>
                                            Invoice
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php if (count($batches) > 0): ?>


                                    <?php foreach ($batches as $index => $batch): ?>


                                        <?php

                                        $status = getBatchStatus(
                                            $batch['expiry_date'],
                                            $batch['remaining_quantity']
                                        );

                                        ?>


                                        <tr>


                                            <td>

                                                <?= $index + 1; ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $batch['product_name']
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $batch['sku'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $batch['batch_number'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>


                                                <?php if (!empty($batch['expiry_date'])): ?>

                                                    <?= date(
                                                        'd-m-Y',
                                                        strtotime(
                                                            $batch['expiry_date']
                                                        )
                                                    ); ?>

                                                <?php else: ?>

                                                    -

                                                <?php endif; ?>


                                            </td>


                                            <td>

                                                <?= number_format(
                                                    $batch['quantity'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= number_format(
                                                        $batch['remaining_quantity'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $batch['supplier_name']
                                                ); ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $batch['invoice_number'] ?? '-'
                                                ); ?>

                                            </td>


                                            <td>

                                                <span
                                                    class="badge <?= $status['class']; ?>"
                                                >

                                                    <?= $status['label']; ?>

                                                </span>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="10"
                                            class="text-center text-muted"
                                        >

                                            Belum ada data batch.

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


<script src="/Bekuku/js/adminlte.min.js"></script>


</body>

</html>