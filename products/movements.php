<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$product_id = $_GET['product_id'] ?? '';
$movement_type = $_GET['movement_type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';


/*
|--------------------------------------------------------------------------
| QUERY RIWAYAT STOK
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        sm.movement_id,
        sm.movement_type,
        sm.quantity,
        sm.reference_type,
        sm.reference_id,
        sm.note,
        sm.created_at,
        p.product_name,
        p.unit
    FROM stock_movements sm
    INNER JOIN products p
        ON sm.product_id = p.product_id
    WHERE 1=1
";

$params = [];


/*
|--------------------------------------------------------------------------
| FILTER PRODUK
|--------------------------------------------------------------------------
*/

if ($product_id !== '') {

    $sql .= "
        AND sm.product_id = :product_id
    ";

    $params[':product_id'] = (int) $product_id;
}


/*
|--------------------------------------------------------------------------
| FILTER JENIS
|--------------------------------------------------------------------------
*/

if ($movement_type !== '') {

    $sql .= "
        AND sm.movement_type = :movement_type
    ";

    $params[':movement_type'] = $movement_type;
}


/*
|--------------------------------------------------------------------------
| FILTER TANGGAL MULAI
|--------------------------------------------------------------------------
*/

if ($start_date !== '') {

    $sql .= "
        AND DATE(sm.created_at) >= :start_date
    ";

    $params[':start_date'] = $start_date;
}


/*
|--------------------------------------------------------------------------
| FILTER TANGGAL AKHIR
|--------------------------------------------------------------------------
*/

if ($end_date !== '') {

    $sql .= "
        AND DATE(sm.created_at) <= :end_date
    ";

    $params[':end_date'] = $end_date;
}


$sql .= "
    ORDER BY sm.movement_id DESC
";


$stmt = $conn->prepare($sql);

$stmt->execute($params);

$movements = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| AMBIL SEMUA PRODUK UNTUK FILTER
|--------------------------------------------------------------------------
*/

$stmtProduct = $conn->query("
    SELECT
        product_id,
        product_name
    FROM products
    ORDER BY product_name ASC
");

$products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HITUNG RINGKASAN
|--------------------------------------------------------------------------
*/

$total_movements = count($movements);

$total_masuk = 0;
$total_keluar = 0;

foreach ($movements as $movement) {

    if ($movement['movement_type'] === 'masuk') {

        $total_masuk += (int) $movement['quantity'];

    } else {

        $total_keluar += (int) $movement['quantity'];

    }
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

    <title>Riwayat Stok - BEKUKU</title>


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
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="app-main">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="container-fluid dashboard-wrapper">

        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <div class="app-content">

            <div class="container-fluid">


                <!-- =================================================
                     SUMMARY
                ================================================== -->

                <div class="row mb-4">


                    <!-- Total Movement -->

                    <div class="col-lg-4 col-md-6 mb-3">

                        <div class="card movement-stat-card h-100">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Pergerakan

                                        </p>

                                        <h3 class="mb-0">

                                            <?= $total_movements; ?>

                                        </h3>

                                        <small class="text-muted">

                                            transaksi stok

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-arrow-left-right fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Stok Masuk -->

                    <div class="col-lg-4 col-md-6 mb-3">

                        <div class="card movement-stat-card h-100">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Stok Masuk

                                        </p>

                                        <h3 class="mb-0 text-success">

                                            +<?= number_format(
                                                $total_masuk,
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </h3>

                                        <small class="text-muted">

                                            unit

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-box-arrow-in-down fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Stok Keluar -->

                    <div class="col-lg-4 col-md-6 mb-3">

                        <div class="card movement-stat-card h-100">

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <p class="text-muted mb-1">

                                            Total Stok Keluar

                                        </p>

                                        <h3 class="mb-0 text-danger">

                                            -<?= number_format(
                                                $total_keluar,
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </h3>

                                        <small class="text-muted">

                                            unit

                                        </small>

                                    </div>


                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                                        style="
                                            width: 50px;
                                            height: 50px;
                                        "
                                    >

                                        <i class="bi bi-box-arrow-up fs-4"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     FILTER CARD
                ================================================== -->

                <div class="card movement-card mb-4">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-funnel me-2"></i>

                            Filter Riwayat Stok

                        </h3>

                    </div>


                    <div class="card-body">


                        <form method="GET">


                            <div class="row g-3">


                                <!-- Tanggal Mulai -->

                                <div class="col-lg-3 col-md-6">

                                    <label class="form-label">

                                        Tanggal Mulai

                                    </label>

                                    <input
                                        type="date"
                                        name="start_date"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $start_date
                                        ); ?>"
                                    >

                                </div>


                                <!-- Tanggal Akhir -->

                                <div class="col-lg-3 col-md-6">

                                    <label class="form-label">

                                        Tanggal Akhir

                                    </label>

                                    <input
                                        type="date"
                                        name="end_date"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $end_date
                                        ); ?>"
                                    >

                                </div>


                                <!-- Produk -->

                                <div class="col-lg-3 col-md-6">

                                    <label class="form-label">

                                        Produk

                                    </label>

                                    <select
                                        name="product_id"
                                        class="form-select"
                                    >

                                        <option value="">

                                            Semua Produk

                                        </option>


                                        <?php foreach ($products as $product): ?>

                                            <option
                                                value="<?= $product['product_id']; ?>"
                                                <?= $product_id == $product['product_id']
                                                    ? 'selected'
                                                    : ''; ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $product['product_name']
                                                ); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <!-- Jenis -->

                                <div class="col-lg-3 col-md-6">

                                    <label class="form-label">

                                        Jenis Pergerakan

                                    </label>

                                    <select
                                        name="movement_type"
                                        class="form-select"
                                    >

                                        <option value="">

                                            Semua Jenis

                                        </option>


                                        <option
                                            value="masuk"
                                            <?= $movement_type === 'masuk'
                                                ? 'selected'
                                                : ''; ?>
                                        >

                                            Stok Masuk

                                        </option>


                                        <option
                                            value="keluar"
                                            <?= $movement_type === 'keluar'
                                                ? 'selected'
                                                : ''; ?>
                                        >

                                            Stok Keluar

                                        </option>

                                    </select>

                                </div>


                                <!-- Buttons -->

                                <div class="col-12">

                                    <hr>

                                    <button
                                        type="submit"
                                        class="btn dashboard-product-add-button me-2"
                                    >

                                        <i class="bi bi-search me-1"></i>

                                        Tampilkan

                                    </button>


                                    <a
                                        href="movements.php"
                                        class="btn dashboard-secondary-action"
                                    >

                                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                                        Reset

                                    </a>

                                </div>


                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     DATA TABLE
                ================================================== -->

                <div class="card movement-card movement-list-card">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="bi bi-list-ul me-2"></i>

                            Data Pergerakan Stok

                        </h3>


                        <div class="card-tools">

                            <span class="badge text-bg-secondary">

                                <?= $total_movements; ?> Data

                            </span>

                        </div>

                    </div>


                    <div class="card-body p-0">


                        <div class="table-responsive">

                            <table
                                class="table table-hover align-middle mb-0"
                            >

                                <thead>

                                    <tr>

                                        <th
                                            class="text-center"
                                            style="width: 60px;"
                                        >

                                            No

                                        </th>


                                        <th
                                            style="width: 165px;"
                                        >

                                            Tanggal

                                        </th>


                                        <th>

                                            Produk

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 150px;"
                                        >

                                            Jenis

                                        </th>


                                        <th
                                            class="text-center"
                                            style="width: 120px;"
                                        >

                                            Jumlah

                                        </th>


                                        <th
                                            style="width: 180px;"
                                        >

                                            Sumber

                                        </th>


                                        <th>

                                            Keterangan

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php if (empty($movements)): ?>


                                        <tr>

                                            <td
                                                colspan="7"
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

                                                        Belum ada riwayat stok.

                                                    </div>


                                                    <small>

                                                        Coba ubah filter atau
                                                        tambahkan pergerakan stok.

                                                    </small>

                                                </div>

                                            </td>

                                        </tr>


                                    <?php else: ?>


                                        <?php $no = 1; ?>


                                        <?php foreach ($movements as $movement): ?>


                                            <tr>


                                                <!-- No -->

                                                <td class="text-center text-muted">

                                                    <?= $no++; ?>

                                                </td>


                                                <!-- Tanggal -->

                                                <td>

                                                    <div class="fw-semibold">

                                                        <?= date(
                                                            'd-m-Y',
                                                            strtotime(
                                                                $movement['created_at']
                                                            )
                                                        ); ?>

                                                    </div>


                                                    <small class="text-muted">

                                                        <i class="bi bi-clock me-1"></i>

                                                        <?= date(
                                                            'H:i',
                                                            strtotime(
                                                                $movement['created_at']
                                                            )
                                                        ); ?>

                                                        WIB

                                                    </small>

                                                </td>


                                                <!-- Produk -->

                                                <td>

                                                    <div class="d-flex align-items-center">


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
                                                                    $movement['product_name']
                                                                ); ?>

                                                            </div>

                                                            <small class="text-muted">

                                                                Satuan:
                                                                <?= htmlspecialchars(
                                                                    $movement['unit']
                                                                ); ?>

                                                            </small>

                                                        </div>


                                                    </div>

                                                </td>


                                                <!-- Jenis -->

                                                <td class="text-center">


                                                    <?php if (
                                                        $movement['movement_type']
                                                        === 'masuk'
                                                    ): ?>


                                                        <span
                                                            class="badge bg-success px-3 py-2"
                                                        >

                                                            <i
                                                                class="bi bi-arrow-down-circle me-1"
                                                            ></i>

                                                            STOK MASUK

                                                        </span>


                                                    <?php else: ?>


                                                        <span
                                                            class="badge bg-danger px-3 py-2"
                                                        >

                                                            <i
                                                                class="bi bi-arrow-up-circle me-1"
                                                            ></i>

                                                            STOK KELUAR

                                                        </span>


                                                    <?php endif; ?>


                                                </td>


                                                <!-- Jumlah -->

                                                <td class="text-center">


                                                    <?php if (
                                                        $movement['movement_type']
                                                        === 'masuk'
                                                    ): ?>


                                                        <span
                                                            class="fw-bold text-success"
                                                        >

                                                            +<?= number_format(
                                                                $movement['quantity'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ); ?>

                                                        </span>


                                                    <?php else: ?>


                                                        <span
                                                            class="fw-bold text-danger"
                                                        >

                                                            -<?= number_format(
                                                                $movement['quantity'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ); ?>

                                                        </span>


                                                    <?php endif; ?>


                                                    <small class="text-muted">

                                                        <?= htmlspecialchars(
                                                            $movement['unit']
                                                        ); ?>

                                                    </small>


                                                </td>


                                                <!-- Sumber -->

                                                <td>


                                                    <?php

                                                    $referenceType =
                                                        $movement['reference_type']
                                                        ?? '';

                                                    $referenceLabel = '-';


                                                    if (
                                                        $referenceType ===
                                                        'pembelian'
                                                    ) {

                                                        $referenceLabel =
                                                            'Pembelian';

                                                    } elseif (
                                                        $referenceType ===
                                                        'penjualan'
                                                    ) {

                                                        $referenceLabel =
                                                            'Penjualan';

                                                    } elseif (
                                                        $referenceType ===
                                                        'restock'
                                                    ) {

                                                        $referenceLabel =
                                                            'Restock';

                                                    } elseif (
                                                        $referenceType !== ''
                                                    ) {

                                                        $referenceLabel =
                                                            ucfirst(
                                                                $referenceType
                                                            );

                                                    }

                                                    ?>


                                                    <?php if (
                                                        $referenceType !== ''
                                                    ): ?>


                                                        <span
                                                            class="badge bg-light text-dark border"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $referenceLabel
                                                            ); ?>

                                                        </span>


                                                    <?php else: ?>

                                                        <span class="text-muted">

                                                            -

                                                        </span>

                                                    <?php endif; ?>


                                                    <?php if (
                                                        !empty(
                                                            $movement['reference_id']
                                                        )
                                                    ): ?>

                                                        <div>

                                                            <small class="text-muted">

                                                                ID:
                                                                #<?= htmlspecialchars(
                                                                    $movement['reference_id']
                                                                ); ?>

                                                            </small>

                                                        </div>

                                                    <?php endif; ?>


                                                </td>


                                                <!-- Keterangan -->

                                                <td>


                                                    <?php if (
                                                        !empty(
                                                            $movement['note']
                                                        )
                                                    ): ?>


                                                        <span>

                                                            <?= htmlspecialchars(
                                                                $movement['note']
                                                            ); ?>

                                                        </span>


                                                    <?php else: ?>


                                                        <span class="text-muted">

                                                            -

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


                    <?php if (!empty($movements)): ?>

                        <div class="card-footer">

                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
                            >

                                <small class="text-muted">

                                    <i class="bi bi-info-circle me-1"></i>

                                    Menampilkan
                                    <?= $total_movements; ?>
                                    riwayat pergerakan stok.

                                </small>


                                <button
                                    type="button"
                                    data-print
                                    class="btn btn-sm btn-outline-secondary"
                                >

                                    <i class="bi bi-printer me-1"></i>

                                    Cetak Riwayat

                                </button>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>


            </div>

        </div>


    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- =========================================================
     ADMINLTE JS
========================================================== -->

<script src="<?= bekuku_url('assets/js/ui.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>


</body>

</html>
