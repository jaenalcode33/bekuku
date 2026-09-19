<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

$isPopup = isset($_GET['popup']) && $_GET['popup'] === '1';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (!bekuku_verify_csrf()) {
            throw new InvalidArgumentException('Token keamanan tidak valid. Silakan coba lagi.');
        }

        $supplierId = (int)($_POST['supplier_id'] ?? 0);

        $purchaseDate = trim($_POST['purchase_date'] ?? '');

        $invoiceNumber = trim($_POST['invoice_number'] ?? '');

        $note = trim($_POST['note'] ?? '');

        $paymentAmount = (float)($_POST['payment_amount'] ?? 0);

        $productIds = $_POST['product_id'] ?? [];

        $quantities = $_POST['quantity'] ?? [];

        $purchasePrices = $_POST['purchase_price'] ?? [];

        $batchNumbers = $_POST['batch_number'] ?? [];

        $expiryDates = $_POST['expiry_date'] ?? [];


        if ($supplierId <= 0) {
            throw new InvalidArgumentException('Supplier wajib dipilih.');
        }


        if ($purchaseDate === '') {
            throw new InvalidArgumentException('Tanggal pembelian wajib diisi.');
        }


        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $purchaseDate);

        if (!$dateTime) {
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $purchaseDate);
        }

        if (!$dateTime) {
            throw new InvalidArgumentException('Format tanggal pembelian tidak valid.');
        }


        if (!is_array($productIds) || count($productIds) === 0) {
            throw new InvalidArgumentException('Minimal harus ada satu produk.');
        }


        $rows = [];

        $total = 0;


        foreach ($productIds as $i => $productId) {

            $productId = (int)$productId;

            $quantity = (int)($quantities[$i] ?? 0);

            $purchasePrice = (float)($purchasePrices[$i] ?? 0);

            $batchNumber = trim($batchNumbers[$i] ?? '');

            $expiryDate = trim($expiryDates[$i] ?? '');


            if ($productId <= 0) {
                throw new InvalidArgumentException('Produk pada baris ' . ($i + 1) . ' belum dipilih.');
            }


            if ($quantity <= 0) {
                throw new InvalidArgumentException('Qty produk pada baris ' . ($i + 1) . ' harus lebih dari 0.');
            }


            if ($purchasePrice < 0) {
                throw new InvalidArgumentException('Harga beli produk pada baris ' . ($i + 1) . ' tidak valid.');
            }


            if ($batchNumber === '') {
                $batchNumber = 'BATCH-' . date('YmdHis') . '-' . ($i + 1);
            }


            $subtotal = round(
                $quantity * $purchasePrice,
                2
            );


            $total += $subtotal;


            $rows[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'subtotal' => $subtotal,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate !== '' ? $expiryDate : null,
            ];

        }


        $total = round($total, 2);


        if ($total <= 0) {
            throw new InvalidArgumentException('Total pembelian harus lebih dari Rp 0.');
        }


        if ($paymentAmount < 0) {
            throw new InvalidArgumentException('Pembayaran tidak boleh kurang dari Rp 0.');
        }


        if ($paymentAmount > $total) {
            throw new InvalidArgumentException('Pembayaran tidak boleh lebih besar dari total pembelian.');
        }


        $remaining = round(
            $total - $paymentAmount,
            2
        );


        $status = $remaining <= 0.009
            ? 'lunas'
            : 'hutang';


        $conn->beginTransaction();


        /*
         * CEK SUPPLIER
         */

        $supplierStmt = $conn->prepare("
            SELECT supplier_id
            FROM suppliers
            WHERE supplier_id = :supplier_id
            LIMIT 1
        ");

        $supplierStmt->execute([
            ':supplier_id' => $supplierId
        ]);


        if (!$supplierStmt->fetch(PDO::FETCH_ASSOC)) {
            throw new InvalidArgumentException('Supplier tidak ditemukan.');
        }


        /*
         * INSERT PEMBELIAN
         */

        $purchaseStmt = $conn->prepare("
            INSERT INTO purchases
            (
                purchase_date,
                supplier_id,
                invoice_number,
                total_amount,
                payment_amount,
                remaining_amount,
                status,
                note
            )
            VALUES
            (
                :purchase_date,
                :supplier_id,
                :invoice_number,
                :total_amount,
                :payment_amount,
                :remaining_amount,
                :status,
                :note
            )
        ");


        $purchaseStmt->execute([
            ':purchase_date' => $dateTime->format('Y-m-d H:i:s'),
            ':supplier_id' => $supplierId,
            ':invoice_number' => $invoiceNumber !== ''
                ? $invoiceNumber
                : null,
            ':total_amount' => $total,
            ':payment_amount' => $paymentAmount,
            ':remaining_amount' => $remaining,
            ':status' => $status,
            ':note' => $note !== ''
                ? $note
                : null,
        ]);


        $purchaseId = (int)$conn->lastInsertId();


        /*
         * PREPARE QUERY DETAIL
         */

        $detailStmt = $conn->prepare("
            INSERT INTO purchase_details
            (
                purchase_id,
                product_id,
                quantity,
                purchase_price,
                subtotal
            )
            VALUES
            (
                :purchase_id,
                :product_id,
                :quantity,
                :purchase_price,
                :subtotal
            )
        ");


        /*
         * PREPARE QUERY BATCH
         */

        $batchStmt = $conn->prepare("
            INSERT INTO batches
            (
                purchase_detail_id,
                product_id,
                batch_number,
                expiry_date,
                quantity,
                remaining_quantity
            )
            VALUES
            (
                :purchase_detail_id,
                :product_id,
                :batch_number,
                :expiry_date,
                :quantity,
                :remaining_quantity
            )
        ");


        /*
         * UPDATE STOCK
         */

        $stockStmt = $conn->prepare("
            UPDATE products
            SET stock = stock + :quantity
            WHERE product_id = :product_id
        ");


        /*
         * STOCK MOVEMENT
         */

        $movementStmt = $conn->prepare("
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
                'masuk',
                :quantity,
                'purchase',
                :reference_id,
                :note
            )
        ");


        foreach ($rows as $row) {


            /*
             * INSERT DETAIL
             */

            $detailStmt->execute([
                ':purchase_id' => $purchaseId,
                ':product_id' => $row['product_id'],
                ':quantity' => $row['quantity'],
                ':purchase_price' => $row['purchase_price'],
                ':subtotal' => $row['subtotal'],
            ]);


            $detailId = (int)$conn->lastInsertId();


            /*
             * INSERT BATCH
             */

            $batchStmt->execute([
                ':purchase_detail_id' => $detailId,
                ':product_id' => $row['product_id'],
                ':batch_number' => $row['batch_number'],
                ':expiry_date' => $row['expiry_date'],
                ':quantity' => $row['quantity'],
                ':remaining_quantity' => $row['quantity'],
            ]);


            /*
             * UPDATE STOCK
             */

            $stockStmt->execute([
                ':quantity' => $row['quantity'],
                ':product_id' => $row['product_id'],
            ]);


            if ($stockStmt->rowCount() === 0) {
                throw new InvalidArgumentException(
                    'Produk dengan ID ' . $row['product_id'] . ' tidak ditemukan.'
                );
            }


            /*
             * STOCK MOVEMENT
             */

            $movementStmt->execute([
                ':product_id' => $row['product_id'],
                ':quantity' => $row['quantity'],
                ':reference_id' => $purchaseId,
                ':note' => 'Pembelian #' . $purchaseId,
            ]);

        }


        /*
         * AUDIT LOG
         */

        bekuku_audit(
            'create',
            'purchase',
            $purchaseId,
            [
                'supplier_id' => $supplierId,
                'total_amount' => $total,
                'payment_amount' => $paymentAmount,
                'status' => $status,
            ]
        );


        $conn->commit();


        /*
         * JIKA POPUP
         */

        if ($isPopup) {

            ?>

            <!DOCTYPE html>

            <html lang="id">

            <head>

                <meta charset="UTF-8">

                <title>Tersimpan</title>

            </head>

            <body>

                <script>
                    window.parent.postMessage(
                        {
                            type: 'purchase-saved',
                            purchaseId: <?= $purchaseId ?>
                        },
                        window.location.origin
                    );
                </script>

            </body>

            </html>

            <?php

            exit;

        }


        /*
         * JIKA BUKAN POPUP
         */

        header(
            'Location: ' .
            bekuku_url(
                'purchases/detail.php?id=' . $purchaseId
            )
        );

        exit;


    } catch (Throwable $e) {


        if ($conn->inTransaction()) {
            $conn->rollBack();
        }


        if ($e instanceof InvalidArgumentException) {

            $error = $e->getMessage();

        } else {

            $error = 'Pembelian gagal disimpan. Periksa data dan koneksi database.';

        }


        bekuku_log(
            'Purchase create failed',
            [
                'error' => $e->getMessage()
            ]
        );

    }

}


/*
 * DATA SUPPLIER
 */

$suppliers = $conn->query("
    SELECT
        supplier_id,
        supplier_name
    FROM suppliers
    ORDER BY supplier_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


/*
 * DATA PRODUK
 */

$products = $conn->query("
    SELECT
        product_id,
        product_name,
        sku,
        purchase_price,
        unit
    FROM products
    WHERE status = 'aktif'
    ORDER BY product_name ASC
")->fetchAll(PDO::FETCH_ASSOC);


$defaultDate = date('Y-m-d\TH:i');

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pembelian Baru - BEKUKU POS</title>


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
        href="<?= bekuku_url('assets/css/popup-pembelian.css') ?>?v=20260919"
    >

</head>


<body class="bekuku-purchase-popup-page">


<div class="purchase-form-shell">


<form
    method="post"
    id="purchaseForm"
    autocomplete="off"
>


<?= bekuku_csrf_field() ?>


<?php if ($error !== null): ?>

    <div class="alert alert-danger purchase-alert">

        <i class="bi bi-exclamation-triangle me-2"></i>

        <?= htmlspecialchars($error) ?>

    </div>

<?php endif; ?>


<?php if (empty($suppliers)): ?>

    <div class="alert alert-warning purchase-alert">

        Belum ada supplier.
        Tambahkan supplier terlebih dahulu.

    </div>

<?php endif; ?>


<?php if (empty($products)): ?>

    <div class="alert alert-warning purchase-alert">

        Belum ada produk aktif yang bisa dibeli.

    </div>

<?php endif; ?>


<div class="purchase-section">


    <div class="purchase-section-title">

        <i class="bi bi-file-earmark-text"></i>

        Informasi Pembelian

    </div>


    <div class="row g-2">


        <div class="col-md-4">

            <label class="form-label">

                Supplier

                <span>*</span>

            </label>


            <select
                name="supplier_id"
                class="form-select"
                required
            >

                <option value="">
                    Pilih supplier
                </option>


                <?php foreach ($suppliers as $supplier): ?>

                    <option
                        value="<?= (int)$supplier['supplier_id'] ?>"
                        <?= (
                            (string)($_POST['supplier_id'] ?? '') ===
                            (string)$supplier['supplier_id']
                        ) ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars(
                            $supplier['supplier_name']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="col-md-4">

            <label class="form-label">

                Tanggal

                <span>*</span>

            </label>


            <input
                type="datetime-local"
                name="purchase_date"
                class="form-control"
                value="<?= htmlspecialchars(
                    $_POST['purchase_date'] ?? $defaultDate
                ) ?>"
                required
            >

        </div>


        <div class="col-md-4">

            <label class="form-label">

                No. Invoice

            </label>


            <input
                type="text"
                name="invoice_number"
                class="form-control"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['invoice_number'] ?? ''
                ) ?>"
                placeholder="INV-001"
            >

        </div>


        <div class="col-12">

            <label class="form-label">

                Catatan

            </label>


            <input
                type="text"
                name="note"
                class="form-control"
                maxlength="255"
                value="<?= htmlspecialchars(
                    $_POST['note'] ?? ''
                ) ?>"
                placeholder="Catatan pembelian (opsional)"
            >

        </div>


    </div>


</div>


<div class="purchase-section">


    <div class="purchase-section-head">


        <div class="purchase-section-title mb-0">

            <i class="bi bi-box-seam"></i>

            Produk

        </div>


        <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            data-action="add-product-row"
        >

            <i class="bi bi-plus-lg me-1"></i>

            Tambah Produk

        </button>


    </div>


    <div class="table-responsive purchase-table-wrap">


        <table
            class="table table-bordered align-middle mb-0 purchase-table"
        >


            <thead>

                <tr>

                    <th>
                        Produk
                    </th>

                    <th width="85">
                        Qty
                    </th>

                    <th width="145">
                        Harga Beli
                    </th>

                    <th width="145">
                        Subtotal
                    </th>

                    <th width="125">
                        Batch
                    </th>

                    <th width="130">
                        Expired
                    </th>

                    <th width="48"></th>

                </tr>

            </thead>


            <tbody id="productBody">


                <tr class="product-row">


                    <td>

                        <select
                            name="product_id[]"
                            class="form-select product-select"
                            required
                        >

                            <option value="">
                                Pilih produk
                            </option>


                            <?php foreach ($products as $product): ?>

                                <option
                                    value="<?= (int)$product['product_id'] ?>"
                                    data-price="<?= htmlspecialchars(
                                        (string)$product['purchase_price']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $product['product_name']
                                    ) ?>


                                    <?php if (!empty($product['sku'])): ?>

                                        —
                                        <?= htmlspecialchars(
                                            $product['sku']
                                        ) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </td>


                    <td>

                        <input
                            type="number"
                            name="quantity[]"
                            class="form-control quantity"
                            min="1"
                            value="1"
                            required
                        >

                    </td>


                    <td>

                        <input
                            type="number"
                            name="purchase_price[]"
                            class="form-control purchase-price"
                            min="0"
                            step="0.01"
                            value="0"
                            required
                        >

                    </td>


                    <td>

                        <input
                            type="text"
                            class="form-control subtotal"
                            value="Rp 0"
                            readonly
                        >

                    </td>


                    <td>

                        <input
                            type="text"
                            name="batch_number[]"
                            class="form-control"
                            maxlength="100"
                            placeholder="BATCH-001"
                        >

                    </td>


                    <td>

                        <input
                            type="date"
                            name="expiry_date[]"
                            class="form-control"
                        >

                    </td>


                    <td class="text-center">

                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm"
                            data-action="remove-product-row"
                            title="Hapus"
                        >

                            <i class="bi bi-trash"></i>

                        </button>

                    </td>


                </tr>


            </tbody>


        </table>


    </div>


</div>


<div class="purchase-bottom">


    <div class="payment-box">


        <div>

            <label class="form-label">
                Total Pembelian
            </label>


            <input
                type="text"
                id="totalDisplay"
                class="form-control total-display"
                value="Rp 0"
                readonly
            >

        </div>


        <div>

            <label class="form-label">
                Dibayar
            </label>


            <input
                type="number"
                id="paymentAmount"
                name="payment_amount"
                class="form-control"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars(
                    $_POST['payment_amount'] ?? '0'
                ) ?>"
                required
            >

        </div>


        <div>

            <label class="form-label">
                Sisa Hutang
            </label>


            <input
                type="text"
                id="remainingDisplay"
                class="form-control remaining-display"
                value="Rp 0"
                readonly
            >

        </div>


    </div>


    <div class="purchase-actions">


        <?php if ($isPopup): ?>

            <button
                type="button"
                class="btn btn-secondary"
                data-purchase-close
            >

                <i class="bi bi-x-lg me-1"></i>

                Batal

            </button>

        <?php else: ?>

            <a
                href="<?= bekuku_url('purchases/index.php') ?>"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Kembali

            </a>

        <?php endif; ?>


        <button
            type="submit"
            class="btn btn-primary"
            id="savePurchaseButton"
            <?= (
                empty($suppliers) ||
                empty($products)
            ) ? 'disabled' : '' ?>
        >

            <i class="bi bi-check-lg me-1"></i>

            Simpan Pembelian

        </button>


    </div>


</div>


</form>


</div>


<script
    src="<?= bekuku_url('assets/js/purchases-create.js') ?>?v=20260919"
></script>


<script
    src="<?= bekuku_url('assets/js/popup-pembelian-form.js') ?>?v=20260919"
></script>


</body>

</html>