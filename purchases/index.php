<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

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

$per_page = 10;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$total_pages = max(1, (int) ceil(count($purchases) / $per_page));
$current_page = min($current_page, $total_pages);
$page_offset = ($current_page - 1) * $per_page;
$display_purchases = array_slice($purchases, $page_offset, $per_page);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pembelian - BEKUKU POS</title>

    "
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >
    >
    >
    >
    >

    <link rel="stylesheet" href="<?= bekuku_url('assets/css/purchases.css') ?>?v=202609221642">
</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div class="app-content-header">

        </div>


        <div class="app-content">

            <div class="container-fluid">


                <div class="card">


                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <h3 class="card-title">

                                <i class="bi bi-cart-check me-2"></i>

                                Riwayat Pembelian

                            </h3>


                            <?php if (bekuku_can('purchases.write')): ?>

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-popup-pembelian
                                    data-popup-url="<?= bekuku_url('purchases/create.php?popup=1') ?>"
                                >

                                    <i class="bi bi-plus-lg me-1"></i>

                                    Pembelian Baru

                                </button>

                            <?php endif; ?>

                        </div>

                    </div>


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


                                    <?php foreach ($display_purchases as $index => $purchase): ?>


                                        <tr>


                                            <td>
                                                <?= $page_offset + $index + 1; ?>
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

                        <?php if ($total_pages > 1): ?>
                            <div class="purchases-pagination">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Menampilkan <?= $page_offset + 1; ?>-<?= min($page_offset + $per_page, count($purchases)); ?>
                                    dari <?= count($purchases); ?> pembelian.
                                </small>
                                <nav aria-label="Navigasi halaman riwayat pembelian">
                                    <ul class="pagination justify-content-center flex-wrap mb-0">
                                        <li class="page-item <?= $current_page === 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $current_page - 1; ?>" aria-label="Halaman sebelumnya">
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
                                            <a class="page-link" href="?page=<?= $current_page + 1; ?>" aria-label="Halaman berikutnya">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>


                    </div>


                </div>


            </div>

        </div>


    </main>


</div>




    <script src="<?= bekuku_url('assets/js/purchases.js') ?>?v=202609221642"></script>
</body>

</html>




