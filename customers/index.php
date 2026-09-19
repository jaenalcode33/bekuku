<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";


$stmt = $conn->query("
    SELECT
        customer_id,
        customer_name
    FROM customers
    ORDER BY customer_id DESC
");


$customers =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


$totalCustomers =
    count($customers);

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
        Customer - BEKUKU POS
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
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091623"
    >


    <!-- CSS POPUP CUSTOMER -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup-customer.css') ?>?v=20260919"
    >

</head>


<body
    class="layout-fixed sidebar-expand-lg bg-body-tertiary"
>


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


        <div
            class="container-fluid dashboard-wrapper"
        >


            <!-- HERO -->

            <section class="dashboard-hero">

                <div class="dashboard-hero-content">


                    <div>

                        <div
                            class="dashboard-hero-title"
                        >

                            <i class="bi bi-people me-2"></i>

                            Customer

                        </div>


                        <p
                            class="dashboard-hero-subtitle"
                        >

                            Kelola data customer BEKUKU
                            dengan lebih rapi.

                        </p>

                    </div>


                    <div
                        class="dashboard-hero-actions"
                    >


                        <!-- TOMBOL POPUP CUSTOMER -->

                        <button
                            type="button"
                            class="btn dashboard-product-add-button"
                            data-popup-customer
                            data-popup-url="<?= bekuku_url('customers/popup-tambah.php?popup=1') ?>"
                        >

                            <i
                                class="bi bi-plus-circle me-1"
                            ></i>

                            Tambah Customer

                        </button>


                    </div>


                </div>

            </section>


            <!-- STATISTIK -->

            <div class="row">


                <div class="col-xl-4 col-md-6">

                    <div
                        class="dashboard-stat stat-blue"
                    >

                        <div class="stat-icon">

                            <i class="bi bi-people"></i>

                        </div>


                        <div class="stat-label">

                            Total Customer

                        </div>


                        <div class="stat-value">

                            <?= number_format($totalCustomers) ?>

                        </div>


                        <div class="stat-description">

                            Customer terdaftar

                        </div>

                    </div>

                </div>


                <div class="col-xl-4 col-md-6">

                    <div
                        class="dashboard-stat stat-green"
                    >

                        <div class="stat-icon">

                            <i class="bi bi-person-check"></i>

                        </div>


                        <div class="stat-label">

                            Data Tersimpan

                        </div>


                        <div class="stat-value">

                            <?= number_format($totalCustomers) ?>

                        </div>


                        <div class="stat-description">

                            Siap digunakan di transaksi

                        </div>

                    </div>

                </div>


                <div class="col-xl-4 col-md-6">

                    <div
                        class="dashboard-stat stat-yellow"
                    >

                        <div class="stat-icon">

                            <i class="bi bi-person-plus"></i>

                        </div>


                        <div class="stat-label">

                            Aksi Cepat

                        </div>


                        <div class="stat-value">

                            <i class="bi bi-plus-circle"></i>

                        </div>


                        <div class="stat-description">

                            Tambah customer baru

                        </div>

                    </div>

                </div>


            </div>


            <!-- DAFTAR CUSTOMER -->

            <section
                class="card products-list-card customers-list-card"
            >


                <div
                    class="card-header d-flex align-items-center justify-content-between"
                >


                    <h3 class="card-title">

                        <i
                            class="bi bi-list-ul me-2"
                        ></i>

                        Daftar Customer

                    </h3>


                    <span class="products-count">

                        <?= number_format($totalCustomers) ?>

                        customer

                    </span>


                </div>


                <div class="card-body p-0">


                    <?php if ($totalCustomers > 0): ?>


                        <div class="table-responsive">


                            <table
                                class="table table-hover align-middle mb-0"
                            >


                                <thead>

                                    <tr>

                                        <th width="80">
                                            No
                                        </th>

                                        <th>
                                            Nama Customer
                                        </th>

                                        <th width="180">
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php

                                    $no = 1;

                                    foreach (
                                        $customers
                                        as $customer
                                    ):

                                    ?>


                                        <tr>


                                            <td>

                                                <?= $no++; ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $customer["customer_name"]
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <td>


                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?= $customer["customer_id"]; ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Edit customer"
                                                    aria-label="Edit customer"
                                                >

                                                    <i
                                                        class="bi bi-pencil"
                                                    ></i>

                                                </a>


                                                <!-- HAPUS -->

                                                <form
                                                    method="post"
                                                    action="delete.php"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus customer ini?')"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $customer["customer_id"]; ?>"
                                                    >


                                                    <?= bekuku_csrf_field() ?>


                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Hapus customer"
                                                        aria-label="Hapus customer"
                                                    >

                                                        <i
                                                            class="bi bi-trash"
                                                        ></i>

                                                    </button>

                                                </form>


                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="alert alert-info">

                            <i
                                class="bi bi-info-circle"
                            ></i>

                            Belum ada customer.

                        </div>


                    <?php endif; ?>


                </div>


            </section>


        </div>


    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<script
    src="<?= bekuku_url('assets/js/ui.js') ?>"
></script>


<script
    src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"
></script>


<!-- JS POPUP CUSTOMER -->

<script
    src="<?= bekuku_url('assets/js/popup-customer.js') ?>?v=20260919"
></script>


</body>

</html>