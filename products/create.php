<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";

$message = "";
$error = "";

/*
|--------------------------------------------------------------------------
| Ambil data kategori
|--------------------------------------------------------------------------
*/
$stmtCategory = $conn->query("
    SELECT *
    FROM categories
    ORDER BY name ASC
");

$categories = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Ambil data supplier
|--------------------------------------------------------------------------
*/
$stmtSupplier = $conn->query("
    SELECT *
    FROM suppliers
    ORDER BY supplier_name ASC
");

$suppliers = $stmtSupplier->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Proses tambah produk
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sku = trim($_POST["sku"] ?? "");
    $barcode = trim($_POST["barcode"] ?? "");
    $product_name = trim($_POST["product_name"] ?? "");
    $category_id = !empty($_POST["category_id"])
        ? (int) $_POST["category_id"]
        : null;

    $supplier_id = !empty($_POST["supplier_id"])
        ? (int) $_POST["supplier_id"]
        : null;

    $purchase_price = (float) ($_POST["purchase_price"] ?? 0);
    $selling_price = (float) ($_POST["selling_price"] ?? 0);
    $unit = trim($_POST["unit"] ?? "pcs");
    $stock = (int) ($_POST["stock"] ?? 0);
    $min_stock = (int) ($_POST["min_stock"] ?? 5);
    $status = $_POST["status"] ?? "aktif";

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if ($product_name === "") {
        $error = "Nama produk wajib diisi.";
    } elseif (!$category_id) {
        $error = "Kategori wajib dipilih.";
    } elseif ($purchase_price < 0) {
        $error = "Harga beli tidak boleh kurang dari 0.";
    } elseif ($selling_price < 0) {
        $error = "Harga jual tidak boleh kurang dari 0.";
    } elseif ($stock < 0) {
        $error = "Stok tidak boleh kurang dari 0.";
    } elseif ($min_stock < 0) {
        $error = "Minimum stok tidak boleh kurang dari 0.";
    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Cek SKU jika diisi
            |--------------------------------------------------------------------------
            */
            if ($sku !== "") {

                $stmtCheckSku = $conn->prepare("
                    SELECT product_id
                    FROM products
                    WHERE sku = :sku
                    LIMIT 1
                ");

                $stmtCheckSku->execute([
                    ":sku" => $sku
                ]);

                if ($stmtCheckSku->fetch()) {
                    throw new Exception("SKU sudah digunakan.");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Cek barcode jika diisi
            |--------------------------------------------------------------------------
            */
            if ($barcode !== "") {

                $stmtCheckBarcode = $conn->prepare("
                    SELECT product_id
                    FROM products
                    WHERE barcode = :barcode
                    LIMIT 1
                ");

                $stmtCheckBarcode->execute([
                    ":barcode" => $barcode
                ]);

                if ($stmtCheckBarcode->fetch()) {
                    throw new Exception("Barcode sudah digunakan.");
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan produk
            |--------------------------------------------------------------------------
            */
            $stmt = $conn->prepare("
                INSERT INTO products
                (
                    category_id,
                    supplier_id,
                    sku,
                    barcode,
                    product_name,
                    purchase_price,
                    selling_price,
                    unit,
                    stock,
                    min_stock,
                    status
                )
                VALUES
                (
                    :category_id,
                    :supplier_id,
                    :sku,
                    :barcode,
                    :product_name,
                    :purchase_price,
                    :selling_price,
                    :unit,
                    :stock,
                    :min_stock,
                    :status
                )
            ");

            $stmt->execute([
                ":category_id" => $category_id,
                ":supplier_id" => $supplier_id,
                ":sku" => $sku !== "" ? $sku : null,
                ":barcode" => $barcode !== "" ? $barcode : null,
                ":product_name" => $product_name,
                ":purchase_price" => $purchase_price,
                ":selling_price" => $selling_price,
                ":unit" => $unit,
                ":stock" => $stock,
                ":min_stock" => $min_stock,
                ":status" => $status
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ambil ID produk yang baru dibuat
            |--------------------------------------------------------------------------
            */
            $product_id = $conn->lastInsertId();

            /*
            |--------------------------------------------------------------------------
            | Jika stok awal > 0, catat sebagai stok masuk
            |--------------------------------------------------------------------------
            */
            if ($stock > 0) {

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
                        'masuk',
                        :quantity,
                        'produk_baru',
                        :reference_id,
                        'Stok awal produk'
                    )
                ");

                $stmtMovement->execute([
                    ":product_id" => $product_id,
                    ":quantity" => $stock,
                    ":reference_id" => $product_id
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Kembali ke halaman produk
            |--------------------------------------------------------------------------
            */
            header("Location: index.php");
            exit;

        } catch (Exception $e) {

            $error = "Gagal menambahkan produk: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk - BEKUKU</title>

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

    <!-- CSS Custom -->
    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <?php require_once __DIR__ . "/../includes/header.php"; ?>

    <main class="app-main">

        <!-- Header -->
        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-6">

                        <h3 class="mb-0">
                            Tambah Produk
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
                                    Produk
                                </a>
                            </li>

                            <li class="breadcrumb-item active">
                                Tambah Produk
                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </div>


        <!-- Content -->
        <div class="app-content">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-md-10">

                        <div class="card card-primary">

                            <div class="card-header">

                                <h3 class="card-title">
                                    Form Produk Baru
                                </h3>

                            </div>


                            <form method="POST"><?= bekuku_csrf_field() ?>

                                <div class="card-body">

                                    <?php if ($error !== ""): ?>

                                        <div class="alert alert-danger">

                                            <i class="bi bi-exclamation-triangle"></i>

                                            <?= htmlspecialchars($error); ?>

                                        </div>

                                    <?php endif; ?>


                                    <!-- SKU -->
                                    <div class="mb-3">

                                        <label class="form-label">
                                            SKU
                                        </label>

                                        <input
                                            type="text"
                                            name="sku"
                                            class="form-control"
                                            placeholder="Contoh: BK-001"
                                            value="<?= htmlspecialchars($_POST["sku"] ?? ""); ?>"
                                        >

                                    </div>


                                    <!-- Barcode -->
                                    <div class="mb-3">

                                        <label class="form-label">
                                            Barcode
                                        </label>

                                        <input
                                            type="text"
                                            name="barcode"
                                            class="form-control"
                                            placeholder="Contoh: 8991234567890"
                                            value="<?= htmlspecialchars($_POST["barcode"] ?? ""); ?>"
                                        >

                                    </div>


                                    <!-- Nama Produk -->
                                    <div class="mb-3">

                                        <label class="form-label">

                                            Nama Produk

                                            <span class="text-danger">
                                                *
                                            </span>

                                        </label>

                                        <input
                                            type="text"
                                            name="product_name"
                                            class="form-control"
                                            placeholder="Contoh: Nugget Ayam"
                                            required
                                            value="<?= htmlspecialchars($_POST["product_name"] ?? ""); ?>"
                                        >

                                    </div>


                                    <!-- Kategori -->
                                    <div class="mb-3">

                                        <label class="form-label">

                                            Kategori

                                            <span class="text-danger">
                                                *
                                            </span>

                                        </label>

                                        <select
                                            name="category_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                -- Pilih Kategori --
                                            </option>

                                            <?php foreach ($categories as $category): ?>

                                                <option
                                                    value="<?= $category["category_id"]; ?>"
                                                    <?= (($_POST["category_id"] ?? "") == $category["category_id"]) ? "selected" : ""; ?>
                                                >

                                                    <?= htmlspecialchars($category["name"]); ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <!-- Supplier -->
                                    <div class="mb-3">

                                        <label class="form-label">
                                            Supplier
                                        </label>

                                        <select
                                            name="supplier_id"
                                            class="form-select"
                                        >

                                            <option value="">
                                                -- Tidak Ada Supplier --
                                            </option>

                                            <?php foreach ($suppliers as $supplier): ?>

                                                <option
                                                    value="<?= $supplier["supplier_id"]; ?>"
                                                    <?= (($_POST["supplier_id"] ?? "") == $supplier["supplier_id"]) ? "selected" : ""; ?>
                                                >

                                                    <?= htmlspecialchars($supplier["supplier_name"]); ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <div class="row">

                                        <!-- Harga Beli -->
                                        <div class="col-md-6">

                                            <div class="mb-3">

                                                <label class="form-label">

                                                    Harga Beli

                                                    <span class="text-danger">
                                                        *
                                                    </span>

                                                </label>

                                                <div class="input-group">

                                                    <span class="input-group-text">
                                                        Rp
                                                    </span>

                                                    <input
                                                        type="number"
                                                        name="purchase_price"
                                                        class="form-control"
                                                        min="0"
                                                        step="0.01"
                                                        required
                                                        value="<?= htmlspecialchars($_POST["purchase_price"] ?? "0"); ?>"
                                                    >

                                                </div>

                                            </div>

                                        </div>


                                        <!-- Harga Jual -->
                                        <div class="col-md-6">

                                            <div class="mb-3">

                                                <label class="form-label">

                                                    Harga Jual

                                                    <span class="text-danger">
                                                        *
                                                    </span>

                                                </label>

                                                <div class="input-group">

                                                    <span class="input-group-text">
                                                        Rp
                                                    </span>

                                                    <input
                                                        type="number"
                                                        name="selling_price"
                                                        class="form-control"
                                                        min="0"
                                                        step="0.01"
                                                        required
                                                        value="<?= htmlspecialchars($_POST["selling_price"] ?? "0"); ?>"
                                                    >

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    <div class="row">

                                        <!-- Satuan -->
                                        <div class="col-md-4">

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Satuan
                                                </label>

                                                <select
                                                    name="unit"
                                                    class="form-select"
                                                >

                                                    <option
                                                        value="pcs"
                                                        <?= (($_POST["unit"] ?? "pcs") === "pcs") ? "selected" : ""; ?>
                                                    >
                                                        pcs
                                                    </option>

                                                    <option
                                                        value="pack"
                                                        <?= (($_POST["unit"] ?? "") === "pack") ? "selected" : ""; ?>
                                                    >
                                                        pack
                                                    </option>

                                                    <option
                                                        value="box"
                                                        <?= (($_POST["unit"] ?? "") === "box") ? "selected" : ""; ?>
                                                    >
                                                        box
                                                    </option>

                                                    <option
                                                        value="kg"
                                                        <?= (($_POST["unit"] ?? "") === "kg") ? "selected" : ""; ?>
                                                    >
                                                        kg
                                                    </option>

                                                </select>

                                            </div>

                                        </div>


                                        <!-- Stok Awal -->
                                        <div class="col-md-4">

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Stok Awal
                                                </label>

                                                <input
                                                    type="number"
                                                    name="stock"
                                                    class="form-control"
                                                    min="0"
                                                    value="<?= htmlspecialchars($_POST["stock"] ?? "0"); ?>"
                                                >

                                                <div class="form-text">
                                                    Masukkan stok yang tersedia saat produk dibuat.
                                                </div>

                                            </div>

                                        </div>


                                        <!-- Minimum Stok -->
                                        <div class="col-md-4">

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Minimum Stok
                                                </label>

                                                <input
                                                    type="number"
                                                    name="min_stock"
                                                    class="form-control"
                                                    min="0"
                                                    value="<?= htmlspecialchars($_POST["min_stock"] ?? "5"); ?>"
                                                >

                                                <div class="form-text">
                                                    Batas untuk peringatan stok rendah.
                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- Status -->
                                    <div class="mb-3">

                                        <label class="form-label">
                                            Status
                                        </label>

                                        <select
                                            name="status"
                                            class="form-select"
                                        >

                                            <option
                                                value="aktif"
                                                <?= (($_POST["status"] ?? "aktif") === "aktif") ? "selected" : ""; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="nonaktif"
                                                <?= (($_POST["status"] ?? "") === "nonaktif") ? "selected" : ""; ?>
                                            >
                                                Nonaktif
                                            </option>

                                        </select>

                                    </div>

                                </div>


                                <!-- Footer -->
                                <div class="card-footer">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-save"></i>

                                        Simpan Produk

                                    </button>


                                    <a
                                        href="index.php"
                                        class="btn btn-secondary"
                                    >

                                        <i class="bi bi-arrow-left"></i>

                                        Batal

                                    </a>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</div>


<script src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"></script>

</body>

</html>