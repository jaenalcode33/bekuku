<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";
require_once __DIR__ . "/../config/midtrans.php";

$isPopup = ($_GET["popup"] ?? "") === "1";
$transactionError = null;
$qrisImageUrl = trim((string) getenv("BEKUKU_QRIS_IMAGE_URL"));


/*
|--------------------------------------------------------------------------
| Ambil data customer
|--------------------------------------------------------------------------
*/

$stmtCustomer = $conn->query("
    SELECT *
    FROM customers
    ORDER BY customer_name ASC
");

$customers = $stmtCustomer->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Ambil produk aktif
|--------------------------------------------------------------------------
*/

$stmtProduct = $conn->query("
    SELECT
        p.*,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.category_id
    WHERE p.status = 'aktif'
    ORDER BY p.product_name ASC
");

$products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Proses transaksi
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id = !empty($_POST["customer_id"])
        ? (int) $_POST["customer_id"]
        : null;

    $payment_amount = (float) ($_POST["payment_amount"] ?? 0);

    $payment_method = $_POST["payment_method"] ?? "cash";

    $product_ids = $_POST["product_id"] ?? [];

    $quantities = $_POST["quantity"] ?? [];


    /*
    |--------------------------------------------------------------------------
    | Validasi produk
    |--------------------------------------------------------------------------
    */

    if (empty($product_ids)) {
        die("Produk belum dipilih.");
    }


    try {

        /*
        |--------------------------------------------------------------------------
        | Mulai transaksi database
        |--------------------------------------------------------------------------
        */

        $conn->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | Gabungkan produk yang sama
        |--------------------------------------------------------------------------
        */

        $requestedProducts = [];


        for ($i = 0; $i < count($product_ids); $i++) {

            $product_id = (int) ($product_ids[$i] ?? 0);

            $quantity = (int) ($quantities[$i] ?? 0);


            if ($product_id <= 0 || $quantity <= 0) {
                continue;
            }


            if (!isset($requestedProducts[$product_id])) {
                $requestedProducts[$product_id] = 0;
            }


            $requestedProducts[$product_id] += $quantity;
        }


        if (empty($requestedProducts)) {

            throw new Exception(
                "Produk belum dipilih."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil data produk dan hitung total
        |--------------------------------------------------------------------------
        */

        $total_amount = 0;

        $details = [];


        foreach ($requestedProducts as $product_id => $quantity) {

            /*
            |--------------------------------------------------------------------------
            | Lock data produk
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                SELECT
                    product_id,
                    product_name,
                    selling_price,
                    stock
                FROM products
                WHERE product_id = :product_id
                FOR UPDATE
            ");

            $stmt->execute([
                ":product_id" => $product_id
            ]);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
            |--------------------------------------------------------------------------
            | Produk tidak ditemukan
            |--------------------------------------------------------------------------
            */

            if (!$product) {

                throw new Exception(
                    "Produk dengan ID " .
                    $product_id .
                    " tidak ditemukan."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Ambil stok
            |--------------------------------------------------------------------------
            */

            $stock = (int) $product["stock"];


            /*
            |--------------------------------------------------------------------------
            | Cek stok
            |--------------------------------------------------------------------------
            */

            if ($quantity > $stock) {

                throw new Exception(
                    "Transaksi melebihi stok produk \"" .
                    $product["product_name"] .
                    "\" tidak mencukupi. " .
                    "Stok tersedia: " .
                    $stock .
                    ", jumlah diminta: " .
                    $quantity
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Harga jual
            |--------------------------------------------------------------------------
            */

            $price = (float) $product["selling_price"];


            /*
            |--------------------------------------------------------------------------
            | Subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal = $price * $quantity;


            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            $total_amount += $subtotal;


            /*
            |--------------------------------------------------------------------------
            | Simpan detail sementara
            |--------------------------------------------------------------------------
            */

            $details[] = [
                "product_id" => $product_id,
                "product_name" => $product["product_name"],
                "quantity" => $quantity,
                "price" => $price,
                "subtotal" => $subtotal
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Hitung kembalian
        |--------------------------------------------------------------------------
        */

        $change_amount =
            $payment_amount -
            $total_amount;


        if ($change_amount < 0) {

            throw new Exception(
                "Uang pembayaran kurang."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Simpan transaksi utama
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO transactions
            (
                customer_id,
                total_amount,
                payment_amount,
                change_amount,
                payment_method,
                status
            )
            VALUES
            (
                :customer_id,
                :total_amount,
                :payment_amount,
                :change_amount,
                :payment_method,
                'selesai'
            )
        ");


        $stmt->execute([

            ":customer_id" =>
                $customer_id,

            ":total_amount" =>
                $total_amount,

            ":payment_amount" =>
                $payment_amount,

            ":change_amount" =>
                $change_amount,

            ":payment_method" =>
                $payment_method
        ]);


        $transaction_id =
            $conn->lastInsertId();


        /*
        |--------------------------------------------------------------------------
        | Simpan detail transaksi
        |--------------------------------------------------------------------------
        */

        $stmtDetail = $conn->prepare("
            INSERT INTO transaction_details
            (
                transaction_id,
                product_id,
                quantity,
                price,
                subtotal
            )
            VALUES
            (
                :transaction_id,
                :product_id,
                :quantity,
                :price,
                :subtotal
            )
        ");


        foreach ($details as $detail) {

            $stmtDetail->execute([

                ":transaction_id" =>
                    $transaction_id,

                ":product_id" =>
                    $detail["product_id"],

                ":quantity" =>
                    $detail["quantity"],

                ":price" =>
                    $detail["price"],

                ":subtotal" =>
                    $detail["subtotal"]
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prepare pengurangan stok produk
        |--------------------------------------------------------------------------
        */

        $stmtStock = $conn->prepare("
            UPDATE products
            SET stock = (
                SELECT COALESCE(SUM(b.remaining_quantity), 0)
                FROM batches b
                WHERE b.product_id = products.product_id
            )
            WHERE product_id = :product_id
        ");


        /*
        |--------------------------------------------------------------------------
        | Prepare riwayat stok
        |--------------------------------------------------------------------------
        */

        $stmtMovement = $conn->prepare("
            INSERT INTO stock_movements
            (
                product_id,
                movement_type,
                quantity,
                reference_type,
                reference_id,
                note
            )
            VALUES
            (
                :product_id,
                'keluar',
                :quantity,
                'penjualan',
                :reference_id,
                :note
            )
        ");


        /*
        |--------------------------------------------------------------------------
        | Proses setiap produk
        |--------------------------------------------------------------------------
        */

        foreach ($details as $detail) {

            $product_id =
                $detail["product_id"];

            $quantityToSell =
                $detail["quantity"];


            /*
            |--------------------------------------------------------------------------
            | Ambil batch berdasarkan FEFO
            |--------------------------------------------------------------------------
            */

            $stmtBatch = $conn->prepare("
                SELECT
                    batch_id,
                    batch_number,
                    expiry_date,
                    remaining_quantity
                FROM batches
                WHERE
                    product_id = :product_id
                    AND remaining_quantity > 0
                ORDER BY
                    CASE
                        WHEN expiry_date IS NULL THEN 1
                        ELSE 0
                    END ASC,
                    expiry_date ASC,
                    batch_id ASC
                FOR UPDATE
            ");


            $stmtBatch->execute([
                ":product_id" => $product_id
            ]);


            $batches = $stmtBatch->fetchAll(
                PDO::FETCH_ASSOC
            );


            /*
            |--------------------------------------------------------------------------
            | Hitung total stok batch
            |--------------------------------------------------------------------------
            */

            $batchStock = 0;


            foreach ($batches as $batch) {

                $batchStock +=
                    (int) $batch["remaining_quantity"];
            }


            /*
            |--------------------------------------------------------------------------
            | Jika stok batch tidak mencukupi
            |--------------------------------------------------------------------------
            */

            if ($batchStock > 0 && $quantityToSell > $batchStock) {

                throw new Exception(
                    "Stok batch produk tidak mencukupi untuk \"" .
                    $detail["product_name"] .
                    "\". " .
                    "Stok batch tersedia: " .
                    $batchStock .
                    ", jumlah diminta: " .
                    $quantityToSell
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Jumlah yang masih harus dikeluarkan
            |--------------------------------------------------------------------------
            */

            $remainingToSell =
                $batchStock > 0
                    ? $quantityToSell
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | Kurangi batch menggunakan FEFO
            |--------------------------------------------------------------------------
            */

            foreach ($batches as $batch) {

                if ($remainingToSell <= 0) {
                    break;
                }


                $available =
                    (int) $batch["remaining_quantity"];


                $quantityFromBatch =
                    min(
                        $remainingToSell,
                        $available
                    );


                /*
                |--------------------------------------------------------------------------
                | Kurangi batch
                |--------------------------------------------------------------------------
                */

                $stmtUpdateBatch = $conn->prepare("
                    UPDATE batches
                    SET remaining_quantity =
                        remaining_quantity - :quantity
                    WHERE batch_id = :batch_id
                ");


                $stmtUpdateBatch->execute([

                    ":quantity" =>
                        $quantityFromBatch,

                    ":batch_id" =>
                        $batch["batch_id"]
                ]);


                $remainingToSell -=
                    $quantityFromBatch;
            }


            /*
            |--------------------------------------------------------------------------
            | Pastikan semua quantity sudah dialokasikan
            |--------------------------------------------------------------------------
            */

            if ($remainingToSell > 0) {

                throw new Exception(
                    "Gagal mengalokasikan stok batch."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Kurangi products.stock
            |--------------------------------------------------------------------------
            */

            $stmtStock->execute([
                ":product_id" =>
                    $product_id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Catat stock movement
            |--------------------------------------------------------------------------
            */

            $stmtMovement->execute([

                ":product_id" =>
                    $product_id,

                ":quantity" =>
                    $quantityToSell,

                ":reference_id" =>
                    $transaction_id,

                ":note" =>
                    "Stok keluar karena penjualan - FEFO"
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $conn->commit();
        bekuku_audit('create', 'transaction', (int) $transaction_id, [
            'total_amount' => $total_amount,
            'payment_method' => $payment_method,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect ke detail transaksi
        |--------------------------------------------------------------------------
        */

        header(
            "Location: detail.php?id=" .
            $transaction_id
        );

        exit;


    } catch (Exception $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }


        $transactionError =
            "Transaksi gagal: " . $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| Ambil daftar kategori
|--------------------------------------------------------------------------
*/

$categoryList = [];


foreach ($products as $product) {

    if (
        !empty($product["category_id"]) &&
        !isset(
            $categoryList[
                $product["category_id"]
            ]
        )
    ) {

        $categoryList[
            $product["category_id"]
        ] =
            $product["category_name"] ?? "-";
    }
}


asort($categoryList);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transaksi Baru - BEKUKU</title>


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


    <!-- CSS Global BEKUKU -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091622"
    >


    <!-- CSS Khusus Transaksi Baru -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/transactions-create.css') ?>?v=2026091651"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary transaction-create-page<?= $isPopup ? ' transaction-popup-page' : '' ?>">


<div class="app-wrapper">


    <!-- NAVBAR / SIDEBAR -->

    <?php if (!$isPopup): ?>
        <?php require_once __DIR__ . "/../includes/header.php"; ?>
    <?php endif; ?>


    <!-- MAIN CONTENT -->

    <main class="app-main">

        <div class="app-content">

            <div class="container-fluid create-wrapper">


                <!-- HERO -->

                <div class="create-hero">

                    <div class="create-hero-content">

                        <div class="create-hero-title">

                            <i class="bi bi-cart-plus me-2"></i>

                            Transaksi Baru

                        </div>


                        <p class="create-hero-subtitle">

                            Pilih produk, tentukan jumlah, lalu proses pembayaran.

                        </p>

                    </div>

                </div>


                <!-- FORM -->

                <form
                    method="POST"
                    id="transactionForm"
                ><?= bekuku_csrf_field() ?>

                    <div class="row g-4">


                        <!-- BAGIAN KIRI -->

                        <div class="col-lg-8">


                            <!-- CUSTOMER -->

                            <div class="create-card">

                                <div class="create-card-header">

                                    <h3 class="create-card-title">

                                        <i class="bi bi-person-circle"></i>

                                        Customer

                                    </h3>

                                </div>


                                <div class="create-card-body">

                                    <label class="form-label fw-semibold">

                                        Pilih Customer

                                    </label>


                                    <select
                                        name="customer_id"
                                        class="form-select customer-select"
                                    >

                                        <option value="">

                                            Customer Umum

                                        </option>


                                        <?php foreach ($customers as $customer): ?>

                                            <option
                                                value="<?= $customer["customer_id"]; ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $customer["customer_name"]
                                                ); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>


                            <!-- PRODUK -->

                            <div class="create-card">


                                <div class="create-card-header">

                                    <h3 class="create-card-title">

                                        <i class="bi bi-box-seam"></i>

                                        Pilih Produk

                                    </h3>


                                    <span
                                        class="selected-count"
                                        id="selectedCount"
                                    >

                                        <i class="bi bi-check2"></i>

                                        0 produk

                                    </span>

                                </div>


                                <!-- FILTER -->

                                <div class="create-card-body">

                                    <div class="filter-box">

                                        <div class="row g-3">


                                            <!-- SEARCH -->

                                            <div class="col-md-8">

                                                <label class="filter-label">

                                                    Cari Produk

                                                </label>


                                                <div class="input-group">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-search"></i>

                                                    </span>


                                                    <input
                                                        type="text"
                                                        id="searchProduct"
                                                        class="form-control search-input"
                                                        placeholder="Nama produk, SKU, atau barcode..."
                                                    >

                                                </div>

                                            </div>


                                            <!-- CATEGORY -->

                                            <div class="col-md-4">

                                                <label class="filter-label">

                                                    Kategori

                                                </label>


                                                <select
                                                    id="filterCategory"
                                                    class="form-select category-select"
                                                >

                                                    <option value="">

                                                        Semua Kategori

                                                    </option>


                                                    <?php foreach (
                                                        $categoryList
                                                        as $categoryId =>
                                                        $categoryName
                                                    ): ?>

                                                        <option
                                                            value="<?= $categoryId; ?>"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $categoryName
                                                            ); ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- TABLE -->

                                <div class="table-responsive">

                                    <table class="table product-table">

                                        <thead>

                                            <tr>

                                                <th
                                                    width="55"
                                                    class="text-center"
                                                >
                                                    Pilih
                                                </th>

                                                <th>
                                                    Produk
                                                </th>

                                                <th>
                                                    Kategori
                                                </th>

                                                <th>
                                                    Harga
                                                </th>

                                                <th class="text-center">
                                                    Stok
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


                                        <?php foreach ($products as $product): ?>

                                            <tr
                                                class="product-row"
                                                data-name="<?= htmlspecialchars(
                                                    strtolower(
                                                        $product["product_name"]
                                                    )
                                                ); ?>"
                                                data-sku="<?= htmlspecialchars(
                                                    strtolower(
                                                        $product["sku"] ?? ""
                                                    )
                                                ); ?>"
                                                data-barcode="<?= htmlspecialchars(
                                                    strtolower(
                                                        $product["barcode"] ?? ""
                                                    )
                                                ); ?>"
                                                data-category="<?= htmlspecialchars(
                                                    $product["category_id"] ?? ""
                                                ); ?>"
                                            >


                                                <!-- CHECKBOX -->

                                                <td class="text-center">

                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input product-check product-checkbox"
                                                        name="product_id[]"
                                                        value="<?= $product["product_id"]; ?>"
                                                        data-price="<?= $product["selling_price"]; ?>"
                                                    >

                                                </td>


                                                <!-- PRODUK -->

                                                <td>

                                                    <div class="product-title">

                                                        <?= htmlspecialchars(
                                                            $product["product_name"]
                                                        ); ?>

                                                    </div>


                                                    <div class="product-meta">

                                                        <?php if (!empty($product["sku"])): ?>

                                                            SKU:
                                                            <?= htmlspecialchars(
                                                                $product["sku"]
                                                            ); ?>

                                                        <?php elseif (!empty($product["barcode"])): ?>

                                                            Barcode:
                                                            <?= htmlspecialchars(
                                                                $product["barcode"]
                                                            ); ?>

                                                        <?php else: ?>

                                                            Produk BEKUKU

                                                        <?php endif; ?>

                                                    </div>

                                                </td>


                                                <!-- KATEGORI -->

                                                <td>

                                                    <span class="category-badge">

                                                        <?= htmlspecialchars(
                                                            $product["category_name"] ?? "-"
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- HARGA -->

                                                <td>

                                                    <span class="price-text">

                                                        Rp
                                                        <?= number_format(
                                                            $product["selling_price"],
                                                            0,
                                                            ",",
                                                            "."
                                                        ); ?>

                                                    </span>

                                                </td>


                                                <!-- STOK -->

                                                <td class="text-center">

                                                    <?php if (
                                                        (int) $product["stock"] <= 0
                                                    ): ?>

                                                        <span
                                                            class="stock-badge stock-empty"
                                                        >

                                                            HABIS

                                                        </span>

                                                    <?php elseif (
                                                        (int) $product["stock"] <=
                                                        (int) $product["min_stock"]
                                                    ): ?>

                                                        <span
                                                            class="stock-badge stock-low"
                                                        >

                                                            <?= (int) $product["stock"]; ?>

                                                        </span>

                                                    <?php else: ?>

                                                        <span
                                                            class="stock-badge stock-good"
                                                        >

                                                            <?= (int) $product["stock"]; ?>

                                                        </span>

                                                    <?php endif; ?>

                                                </td>


                                                <!-- JUMLAH -->

                                                <td class="text-center">

                                                    <input
                                                        type="number"
                                                        class="form-control form-control-sm quantity quantity-input"
                                                        name="quantity[]"
                                                        value="1"
                                                        min="1"
                                                        max="<?= (int) $product["stock"]; ?>"
                                                        disabled
                                                    >

                                                </td>


                                                <!-- SUBTOTAL -->

                                                <td class="text-end">

                                                    <span class="subtotal subtotal-text">

                                                        Rp 0

                                                    </span>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>


                                        <?php if (empty($products)): ?>

                                            <tr>

                                                <td
                                                    colspan="7"
                                                    class="product-empty"
                                                >

                                                    <i class="bi bi-box-seam"></i>

                                                    Belum ada produk aktif.

                                                </td>

                                            </tr>

                                        <?php endif; ?>


                                        <tr
                                            id="noProductFound"
                                            style="display: none;"
                                        >

                                            <td
                                                colspan="7"
                                                class="product-empty"
                                            >

                                                <i class="bi bi-search"></i>

                                                Produk tidak ditemukan.

                                            </td>

                                        </tr>


                                        </tbody>

                                    </table>

                                </div>

                            </div>


                        </div>


                        <!-- BAGIAN KANAN -->

                        <div class="col-lg-4">


                            <div class="summary-card">


                                <!-- HEADER -->

                                <div class="summary-header">

                                    <h3 class="summary-header-title">

                                        <i class="bi bi-calculator"></i>

                                        Ringkasan Transaksi

                                    </h3>

                                </div>


                                <!-- BODY -->

                                <div class="summary-body">


                                    <!-- TOTAL -->

                                    <div>

                                        <div class="summary-total-label">

                                            Total Belanja

                                        </div>


                                        <div
                                            class="summary-total"
                                            id="total"
                                        >

                                            Rp 0

                                        </div>

                                    </div>


                                    <hr class="summary-divider">


                                    <!-- PEMBAYARAN -->

                                    <label class="payment-label">

                                        Jumlah Pembayaran

                                    </label>


                                    <div class="input-group payment-input-group">

                                        <span class="input-group-text">

                                            Rp

                                        </span>


                                        <input
                                            type="number"
                                            name="payment_amount"
                                            id="payment_amount"
                                            class="form-control payment-input"
                                            min="0"
                                            required
                                            placeholder="0"
                                        >

                                    </div>


                                    <!-- METODE -->

                                    <label class="payment-label">

                                        Metode Pembayaran

                                    </label>


                                    <select
                                        name="payment_method"
                                        id="payment_method"
                                        class="form-select payment-select"
                                        data-qris-image-url="<?= htmlspecialchars($qrisImageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                        <option value="cash">
                                            Cash
                                        </option>

                                        <option value="transfer">
                                            Transfer
                                        </option>

                                        <option value="qris">
                                            QRIS Midtrans
                                        </option>

                                        <option value="qris_image">
                                            QRIS Gambar
                                        </option>

                                    </select>


                                    <div
                                        class="qris-barcode-panel"
                                        id="qrisBarcodePanel"
                                        aria-hidden="true"
                                    >
                                        <div class="qris-barcode-header">
                                            <i class="bi bi-qr-code-scan"></i>
                                            <span>Scan barcode QRIS</span>
                                        </div>
                                        <div
                                            class="qris-barcode"
                                            id="qrisBarcode"
                                            aria-label="Barcode QRIS"
                                        ></div>
                                        <div class="qris-barcode-amount" id="qrisBarcodeAmount">
                                            Total: Rp 0
                                        </div>
                                        <div class="qris-barcode-note" id="qrisBarcodeNote">
                                            QRIS dibuat melalui Midtrans.
                                        </div>
                                    </div>


                                    <!-- KEMBALIAN -->

                                    <div class="change-box">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <span class="change-label">

                                                Kembalian

                                            </span>


                                            <strong
                                                id="change"
                                                class="change-value"
                                            >

                                                Rp 0

                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <!-- FOOTER -->

                                <div class="summary-footer">


                                    <button
                                        type="submit"
                                        class="btn save-button w-100 mb-2"
                                        id="saveTransaction"
                                    >

                                        <i class="bi bi-check-circle me-1"></i>

                                        Simpan Transaksi

                                    </button>


                                    <a
                                        href="index.php"
                                        class="btn btn-outline-secondary cancel-button w-100"
                                    >

                                        <i class="bi bi-arrow-left me-1"></i>

                                        Batal

                                    </a>

                                </div>

                            </div>


                        </div>


                    </div>

                </form>

            </div>

        </div>

    </main>


    <!-- FOOTER -->

    <?php if (!$isPopup): ?>
        <?php require_once __DIR__ . "/../includes/footer.php"; ?>
    <?php endif; ?>


</div>


<div
    class="transaction-notice-modal"
    id="transactionNoticeModal"
    aria-hidden="true"
>
    <div class="transaction-notice-dialog" role="dialog" aria-modal="true">
        <div class="transaction-notice-header">
            <h5 class="transaction-notice-title">
                <i class="bi bi-exclamation-triangle"></i>
                Stok Tidak Mencukupi
            </h5>
            <button
                type="button"
                class="transaction-notice-close"
                aria-label="Tutup"
                onclick="closeTransactionNotice()"
            >&times;</button>
        </div>
        <div class="transaction-notice-body">
            <div class="delete-modal-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="delete-modal-title">Produk tidak dapat dipilih</div>
            <div
                class="delete-modal-text transaction-notice-text"
            ></div>
        </div>
        <div class="delete-modal-footer">
            <button
                type="button"
                class="btn delete-cancel-button"
                onclick="closeTransactionNotice()"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </button>
        </div>
    </div>
</div>


<!-- AdminLTE JS -->

<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

<!-- JavaScript Transaksi Baru -->

<script src="<?= bekuku_url('assets/js/transactions-create.js') ?>?v=2026091651"></script>

<?php if ($transactionError !== null): ?>
    <script>
        showTransactionNotice(<?= json_encode($transactionError, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
    </script>
<?php endif; ?>


</body>

</html>