<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";

$stmt = $conn->query("
    SELECT
        s.supplier_id,
        s.supplier_name,
        COUNT(p.product_id) AS total_products
    FROM suppliers s
    LEFT JOIN products p
        ON s.supplier_id = p.supplier_id
    GROUP BY
        s.supplier_id,
        s.supplier_name
    ORDER BY s.supplier_id DESC
");

$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_products = array_sum(
    array_map(
        static fn (array $supplier): int => (int) $supplier['total_products'],
        $suppliers
    )
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Supplier - BEKUKU POS</title>

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
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091643"
    >

    <!-- CSS KHUSUS POPUP SUPPLIER -->
    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup-supplier.css') ?>?v=20260919"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div class="container-fluid dashboard-wrapper suppliers-page">

            <!-- HERO -->

            <section class="dashboard-hero">

                <div class="dashboard-hero-content">

                    <div>

                        <div class="dashboard-hero-title">

                            <i class="bi bi-truck me-2"></i>
                            Supplier

                        </div>

                        <p class="dashboard-hero-subtitle">

                            Kelola pemasok dan hubungan produk BEKUKU
                            dengan lebih teratur.

                        </p>

                    </div>


                    <div class="dashboard-hero-actions">

                        <!-- TOMBOL POPUP SUPPLIER -->

                        <button
                            type="button"
                            class="btn dashboard-product-add-button"
                            data-popup-supplier
                            data-popup-url="<?= bekuku_url('suppliers/popup-tambah.php?popup=1') ?>"
                        >

                            <i class="bi bi-plus-circle me-1"></i>
                            Tambah Supplier

                        </button>

                    </div>

                </div>

            </section>


            <!-- STATISTIK -->

            <div class="row">

                <div class="col-xl-4 col-md-6">

                    <div class="dashboard-stat stat-blue">

                        <div class="stat-icon">
                            <i class="bi bi-truck"></i>
                        </div>

                        <div class="stat-label">
                            Total Supplier
                        </div>

                        <div class="stat-value">
                            <?= number_format(count($suppliers)); ?>
                        </div>

                        <div class="stat-description">
                            Pemasok terdaftar
                        </div>

                    </div>

                </div>


                <div class="col-xl-4 col-md-6">

                    <div class="dashboard-stat stat-green">

                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <div class="stat-label">
                            Produk Terhubung
                        </div>

                        <div class="stat-value">
                            <?= number_format($total_products); ?>
                        </div>

                        <div class="stat-description">
                            Produk dari supplier
                        </div>

                    </div>

                </div>


                <div class="col-xl-4 col-md-6">

                    <div class="dashboard-stat stat-yellow">

                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>

                        <div class="stat-label">
                            Supplier Aktif
                        </div>

                        <div class="stat-value">
                            <?= number_format(count($suppliers)); ?>
                        </div>

                        <div class="stat-description">
                            Memiliki data pemasok
                        </div>

                    </div>

                </div>

            </div>


            <!-- DAFTAR SUPPLIER -->

            <section class="card products-list-card suppliers-list-card">


                <div class="card-header d-flex align-items-center justify-content-between">

                    <h3 class="card-title">

                        <i class="bi bi-list-ul me-2"></i>
                        Daftar Supplier

                    </h3>

                    <span class="products-count">

                        <?= number_format(count($suppliers)); ?>
                        supplier

                    </span>

                </div>


                <div class="card-body p-0">

                    <?php if (count($suppliers) > 0): ?>

                        <div class="table-responsive">

                            <table
                                class="table table-hover align-middle mb-0"
                            >

                                <thead>

                                    <tr>

                                        <th style="width: 80px;">
                                            No
                                        </th>

                                        <th>
                                            Nama Supplier
                                        </th>

                                        <th style="width: 180px;">
                                            Jumlah Produk
                                        </th>

                                        <th
                                            class="text-end"
                                            style="width: 180px;"
                                        >
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php

                                    $no = 1;

                                    foreach ($suppliers as $supplier):

                                    ?>

                                        <tr>

                                            <td class="text-muted">

                                                <?= $no++; ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $supplier["supplier_name"]
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <span class="category-product-count">

                                                    <?= $supplier["total_products"]; ?>

                                                    produk

                                                </span>

                                            </td>


                                            <td class="text-end">


                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= $supplier["supplier_id"]; ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                >

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                <!-- HAPUS -->

                                                <form
                                                    method="post"
                                                    action="delete.php"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus supplier ini?')"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $supplier["supplier_id"]; ?>"
                                                    >

                                                    <?= bekuku_csrf_field() ?>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                    >

                                                        <i class="bi bi-trash"></i>

                                                    </button>

                                                </form>


                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>


                    <?php else: ?>


                        <!-- KOSONG -->

                        <div class="products-empty">

                            <i class="bi bi-truck"></i>

                            <h4>
                                Belum ada supplier
                            </h4>

                            <p>
                                Tambahkan supplier pertama untuk mulai
                                mengelola pemasok.
                            </p>

                            <button
                                type="button"
                                class="btn btn-primary"
                                data-popup-supplier
                                data-popup-url="<?= bekuku_url('suppliers/popup-tambah.php?popup=1') ?>"
                            >

                                <i class="bi bi-plus-circle me-1"></i>

                                Tambah Supplier

                            </button>

                        </div>


                    <?php endif; ?>


                </div>

            </section>

        </div>

    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<script src="<?= bekuku_url('assets/js/ui.js') ?>"></script>

<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

<!-- JS KHUSUS POPUP SUPPLIER -->
<script src="<?= bekuku_url('assets/js/popup-supplier.js') ?>?v=20260919"></script>


</body>

</html>