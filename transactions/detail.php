<?php

require_once __DIR__ . "/../config/app.php";


require_once __DIR__ . "/../config/database.php";

/*
|--------------------------------------------------------------------------
| Ambil ID transaksi
|--------------------------------------------------------------------------
*/

$transaction_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

$currentUser = bekuku_user();


/*
|--------------------------------------------------------------------------
| Validasi ID transaksi
|--------------------------------------------------------------------------
*/

if ($transaction_id <= 0) {
    die("ID transaksi tidak valid.");
}


/*
|--------------------------------------------------------------------------
| Ambil data transaksi
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        t.*,
        c.customer_name
    FROM transactions t
    LEFT JOIN customers c
        ON t.customer_id = c.customer_id
    WHERE t.transaction_id = :transaction_id
");

$stmt->execute([
    ":transaction_id" => $transaction_id
]);

$transaction = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Jika transaksi tidak ditemukan
|--------------------------------------------------------------------------
*/

if (!$transaction) {
    die("Transaksi tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| Ambil detail produk
|--------------------------------------------------------------------------
*/

$stmtDetail = $conn->prepare("
    SELECT
        td.*,
        p.product_name,
        p.unit
    FROM transaction_details td
    INNER JOIN products p
        ON td.product_id = p.product_id
    WHERE td.transaction_id = :transaction_id
    ORDER BY td.product_id ASC
");

$stmtDetail->execute([
    ":transaction_id" => $transaction_id
]);

$details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Hitung total item
|--------------------------------------------------------------------------
*/

$total_items = 0;

foreach ($details as $detail) {
    $total_items += (int) $detail['quantity'];
}


/*
|--------------------------------------------------------------------------
| Fungsi Rupiah
|--------------------------------------------------------------------------
*/

function rupiah($number)
{
    return 'Rp ' . number_format(
        $number,
        0,
        ',',
        '.'
    );
}


/*
|--------------------------------------------------------------------------
| Status transaksi
|--------------------------------------------------------------------------
*/

$status = strtolower($transaction['status'] ?? '');

if ($status === 'selesai') {

    $status_class = 'status-success';
    $status_icon = 'bi-check-circle-fill';
    $status_label = 'Selesai';

} elseif ($status === 'batal') {

    $status_class = 'status-danger';
    $status_icon = 'bi-x-circle-fill';
    $status_label = 'Batal';

} else {

    $status_class = 'status-warning';
    $status_icon = 'bi-clock-fill';
    $status_label = ucfirst($transaction['status'] ?? 'Pending');
}


/*
|--------------------------------------------------------------------------
| Metode pembayaran
|--------------------------------------------------------------------------
*/

$payment_method = strtolower($transaction['payment_method'] ?? '');

if ($payment_method === 'cash') {

    $payment_class = 'payment-cash';
    $payment_icon = 'bi-cash-stack';
    $payment_label = 'Cash';

} elseif ($payment_method === 'transfer') {

    $payment_class = 'payment-transfer';
    $payment_icon = 'bi-bank';
    $payment_label = 'Transfer';

} elseif ($payment_method === 'qris') {

    $payment_class = 'payment-qris';
    $payment_icon = 'bi-qr-code';
    $payment_label = 'QRIS Midtrans';

} elseif ($payment_method === 'qris_image') {

    $payment_class = 'payment-qris';
    $payment_icon = 'bi-image';
    $payment_label = 'QRIS Gambar';

} else {

    $payment_class = 'payment-other';
    $payment_icon = 'bi-credit-card';
    $payment_label = strtoupper(
        $transaction['payment_method'] ?? '-'
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

    <title>
        Detail Transaksi #<?= $transaction['transaction_id']; ?> - BEKUKU
    </title>


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

    <!-- CSS Khusus Transaksi -->
    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/transactions-create.css') ?>?v=2026091612"
    >

    <link rel="stylesheet" href="<?= bekuku_url('assets/css/transactions-detail.css') ?>"
    >


    

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary transaction-detail-page">


<div class="app-wrapper">


    <!-- =========================================================
         NAVBAR / SIDEBAR
    ========================================================== -->

    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <!-- =========================================================
         MAIN
    ========================================================== -->

    <main class="app-main">


        <div class="app-content">

            <div class="container-fluid transaction-detail-wrapper">


                <!-- =================================================
                     HERO
                ================================================== -->

                <div class="detail-hero">

                    <div class="row align-items-center">

                        <div class="col-lg-8">

                            <div class="detail-hero-content">

                                <div class="detail-hero-title">

                                    <i class="bi bi-receipt-cutoff me-2"></i>

                                    Detail Transaksi
                                    #<?= $transaction['transaction_id']; ?>

                                </div>


                                <p class="detail-hero-subtitle">

                                    Informasi lengkap transaksi dan rincian produk BEKUKU.

                                </p>


                                <div class="detail-hero-meta">

                                    <span class="hero-meta-item">

                                        <i class="bi bi-calendar3"></i>

                                        <?= date(
                                            'd M Y',
                                            strtotime($transaction['transaction_date'])
                                        ); ?>

                                    </span>


                                    <span class="hero-meta-item">

                                        <i class="bi bi-clock"></i>

                                        <?= date(
                                            'H:i',
                                            strtotime($transaction['transaction_date'])
                                        ); ?>

                                    </span>


                                    <span class="hero-meta-item">

                                        <i class="bi bi-person"></i>

                                        <?= htmlspecialchars(
                                            $transaction['customer_name'] ?? 'Umum'
                                        ); ?>

                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="col-lg-4">

                            <div class="detail-hero-actions justify-content-lg-end">


                                <a
                                    href="index.php"
                                    class="btn btn-light no-print"
                                >

                                    <i class="bi bi-arrow-left me-1"></i>

                                    Kembali

                                </a>


                                <button
                                    type="button"
                                    class="btn btn-dark no-print"
                                    data-print
                                >

                                    <i class="bi bi-printer me-1"></i>

                                    Cetak Struk
                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     INFORMASI TRANSAKSI
                ================================================== -->

                <div class="detail-info-card mb-4">

                    <div class="row g-0">


                        <!-- ID -->

                        <div class="col-md-3">

                            <div class="detail-info-item">

                                <div class="detail-label">
                                    ID Transaksi
                                </div>

                                <div class="detail-value">

                                    <i class="bi bi-hash"></i>

                                    <?= $transaction['transaction_id']; ?>

                                </div>

                            </div>

                        </div>


                        <!-- Tanggal -->

                        <div class="col-md-3">

                            <div class="detail-info-item">

                                <div class="detail-label">
                                    Tanggal Transaksi
                                </div>

                                <div class="detail-value">

                                    <i class="bi bi-calendar-event"></i>

                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime($transaction['transaction_date'])
                                    ); ?>

                                </div>

                            </div>

                        </div>


                        <!-- Customer -->

                        <div class="col-md-3">

                            <div class="detail-info-item">

                                <div class="detail-label">
                                    Customer
                                </div>

                                <div class="detail-value">

                                    <i class="bi bi-person-circle"></i>

                                    <?= htmlspecialchars(
                                        $transaction['customer_name'] ?? 'Umum'
                                    ); ?>

                                </div>

                            </div>

                        </div>


                        <!-- Status -->

                        <div class="col-md-3">

                            <div class="detail-info-item">

                                <div class="detail-label">
                                    Status
                                </div>

                                <div>

                                    <span class="detail-status <?= $status_class; ?>">

                                        <i class="bi <?= $status_icon; ?>"></i>

                                        <?= htmlspecialchars($status_label); ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SUMMARY
                ================================================== -->

                <div class="row g-3 mb-4 detail-summary-row">


                    <!-- JUMLAH PRODUK -->

                    <div class="col-lg-4 col-md-4">

                        <div class="detail-summary-card">

                            <div class="d-flex align-items-center">

                                <div class="summary-icon summary-icon-orange">

                                    <i class="bi bi-box-seam"></i>

                                </div>


                                <div class="ms-3">

                                    <div class="summary-label">
                                        Jumlah Produk
                                    </div>

                                    <div class="summary-value">

                                        <?= count($details); ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- TOTAL ITEM -->

                    <div class="col-lg-4 col-md-4">

                        <div class="detail-summary-card">

                            <div class="d-flex align-items-center">

                                <div class="summary-icon summary-icon-green">

                                    <i class="bi bi-cart-check"></i>

                                </div>


                                <div class="ms-3">

                                    <div class="summary-label">
                                        Total Item
                                    </div>

                                    <div class="summary-value">

                                        <?= $total_items; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- TOTAL -->

                    <div class="col-lg-4 col-md-4">

                        <div class="detail-summary-card">

                            <div class="d-flex align-items-center">

                                <div class="summary-icon summary-icon-blue">

                                    <i class="bi bi-wallet2"></i>

                                </div>


                                <div class="ms-3">

                                    <div class="summary-label">
                                        Total Transaksi
                                    </div>

                                    <div class="summary-value">

                                        <?= rupiah(
                                            $transaction['total_amount']
                                        ); ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     DETAIL PRODUK
                ================================================== -->

                <div class="detail-section">


                    <div class="detail-section-header">

                        <h3 class="detail-section-title">

                            <i class="bi bi-basket"></i>

                            Detail Produk

                        </h3>


                        <span class="text-muted small">

                            <?= count($details); ?> produk

                        </span>

                    </div>


                    <div class="detail-section-body">

                        <div class="table-responsive">

                            <table class="table detail-product-table">

                                <thead>

                                    <tr>

                                        <th
                                            class="text-center"
                                            width="70"
                                        >
                                            No
                                        </th>

                                        <th>
                                            Produk
                                        </th>

                                        <th class="text-end">
                                            Harga
                                        </th>

                                        <th class="text-center">
                                            Jumlah
                                        </th>

                                        <th class="text-end">
                                            Subtotal
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php if (empty($details)): ?>

                                        <tr>

                                            <td
                                                colspan="5"
                                                class="detail-empty"
                                            >

                                                <i class="bi bi-inbox"></i>

                                                Tidak ada detail produk.

                                            </td>

                                        </tr>

                                    <?php else: ?>

                                        <?php $no = 1; ?>


                                        <?php foreach ($details as $detail): ?>

                                            <tr>


                                                <!-- NOMOR -->

                                                <td class="text-center">

                                                    <?= $no++; ?>

                                                </td>


                                                <!-- PRODUK -->

                                                <td>

                                                    <div class="d-flex align-items-center">

                                                        <div class="product-icon">

                                                            <i class="bi bi-box"></i>

                                                        </div>


                                                        <div class="ms-3">

                                                            <div class="product-name">

                                                                <?= htmlspecialchars(
                                                                    $detail['product_name']
                                                                ); ?>

                                                            </div>


                                                            <div class="product-unit">

                                                                Satuan:
                                                                <?= htmlspecialchars(
                                                                    $detail['unit']
                                                                ); ?>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </td>


                                                <!-- HARGA -->

                                                <td class="text-end">

                                                    <?= rupiah(
                                                        $detail['price']
                                                    ); ?>

                                                </td>


                                                <!-- JUMLAH -->

                                                <td class="text-center">

                                                    <span class="quantity-badge">

                                                        <?= (int) $detail['quantity']; ?>

                                                        <?= htmlspecialchars(
                                                            $detail['unit']
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- SUBTOTAL -->

                                                <td class="text-end">

                                                    <strong>

                                                        <?= rupiah(
                                                            $detail['subtotal']
                                                        ); ?>

                                                    </strong>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PEMBAYARAN
                ================================================== -->

                <div class="row justify-content-end">


                    <div class="col-xl-5 col-lg-6 col-md-8">


                        <div class="payment-card">


                            <!-- HEADER -->

                            <div class="payment-header">

                                <h3 class="payment-header-title">

                                    <i class="bi bi-credit-card"></i>

                                    Informasi Pembayaran

                                </h3>

                            </div>


                            <!-- BODY -->

                            <div class="payment-body">


                                <!-- METODE -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Metode Pembayaran

                                    </span>


                                    <span class="payment-badge <?= $payment_class; ?>">

                                        <i class="bi <?= $payment_icon; ?>"></i>

                                        <?= htmlspecialchars(
                                            $payment_label
                                        ); ?>

                                    </span>

                                </div>


                                <!-- TOTAL -->

                                <div class="payment-row">

                                    <span class="payment-label">
                                        Total
                                    </span>


                                    <span class="payment-value">

                                        <?= rupiah(
                                            $transaction['total_amount']
                                        ); ?>

                                    </span>

                                </div>


                                <!-- PEMBAYARAN -->

                                <div class="payment-row">

                                    <span class="payment-label">
                                        Pembayaran
                                    </span>


                                    <span class="payment-value green">

                                        <?= rupiah(
                                            $transaction['payment_amount']
                                        ); ?>

                                    </span>

                                </div>


                                <!-- KEMBALIAN -->

                                <div class="payment-row">

                                    <span class="payment-label">
                                        Kembalian
                                    </span>


                                    <span class="payment-value orange">

                                        <?= rupiah(
                                            $transaction['change_amount']
                                        ); ?>

                                    </span>

                                </div>


                                <!-- TOTAL AKHIR -->

                                <div class="payment-total">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div>

                                            <div class="payment-total-label">

                                                TOTAL TRANSAKSI

                                            </div>

                                        </div>


                                        <div class="payment-total-value">

                                            <?= rupiah(
                                                $transaction['total_amount']
                                            ); ?>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <section class="receipt-preview-area no-print">
                    <div class="receipt-preview-toolbar">
                        <strong>Preview Struk</strong>
                        <label>
                            Ukuran
                            <select id="receiptSize" aria-label="Ukuran struk">
                                <option value="58">58mm</option>
                                <option value="80" selected>80mm</option>
                            </select>
                        </label>
                        <button type="button" class="btn btn-dark" data-print>
                            <i class="bi bi-printer me-1"></i>
                            Cetak Struk
                        </button>
                    </div>
                </section>

                <article class="receipt-print" id="receiptPrint">
                    <header class="receipt-header">
                        <div class="receipt-logo">❄</div>
                        <div class="receipt-store-name">BEKUKU</div>
                        <div>FROZEN FOOD</div>
                        <div>POINT OF SALE</div>
                        <div class="receipt-store-contact">
                            Jl. Contoh No. 123<br>
                            Karawang, Jawa Barat<br>
                            Telp: 08xxxxxxxxxx
                        </div>
                    </header>

                    <div class="receipt-divider"></div>
                    <section class="receipt-info">
                        <div><span>No. Transaksi</span><strong>#<?= (int) $transaction['transaction_id']; ?></strong></div>
                        <div><span>Tanggal</span><strong><?= date('d-m-Y H:i', strtotime($transaction['transaction_date'])); ?></strong></div>
                        <div><span>Kasir</span><strong><?= htmlspecialchars($currentUser['name'] ?? $currentUser['username'] ?? 'Admin'); ?></strong></div>
                        <div><span>Customer</span><strong><?= htmlspecialchars($transaction['customer_name'] ?? 'Umum'); ?></strong></div>
                    </section>
                    <div class="receipt-divider"></div>
                    <section class="receipt-items">
                        <?php foreach ($details as $detail): ?>
                            <div class="receipt-item">
                                <div class="receipt-item-name"><?= htmlspecialchars($detail['product_name']); ?></div>
                                <div class="receipt-item-line">
                                    <span><?= (int) $detail['quantity']; ?> x <?= rupiah($detail['price']); ?></span>
                                    <strong><?= rupiah($detail['subtotal']); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                    <div class="receipt-divider"></div>
                    <section class="receipt-summary">
                        <div><span>Subtotal</span><strong><?= rupiah($transaction['total_amount']); ?></strong></div>
                        <div class="receipt-total"><span>TOTAL</span><strong><?= rupiah($transaction['total_amount']); ?></strong></div>
                        <div><span>Bayar</span><strong><?= rupiah($transaction['payment_amount']); ?></strong></div>
                        <div><span>Kembalian</span><strong><?= rupiah($transaction['change_amount']); ?></strong></div>
                    </section>
                    <section class="receipt-payment">
                        <div><span>Pembayaran</span><strong><?= htmlspecialchars($payment_label); ?></strong></div>
                        <?php if ($payment_method === 'qris' || $payment_method === 'qris_image'): ?>
                            <div><span>Status</span><strong>LUNAS</strong></div>
                        <?php endif; ?>
                    </section>
                    <div class="receipt-status">[ TRANSAKSI BERHASIL ]</div>
                    <footer class="receipt-footer">
                        <div>Terima kasih telah berbelanja</div>
                        <div>di BEKUKU Frozen Food</div>
                        <div>Simpan struk ini sebagai bukti transaksi.</div>
                        <strong>*** TERIMA KASIH ***</strong>
                    </footer>
                </article>


            </div>

        </div>

    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>


</div>


<!-- AdminLTE JS -->

<script src="<?= bekuku_url('assets/js/ui.js') ?>"></script>
<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

<!-- JavaScript Transaksi Baru -->

<script src="<?= bekuku_url('assets/js/transactions-create.js') ?>"></script>

</body>

</html>     
