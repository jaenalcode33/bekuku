<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";

$product_id = (int) ($_GET["id"] ?? 0);

if ($product_id <= 0) {
    die("ID produk tidak valid.");
}

/*
|--------------------------------------------------------------------------
| Ambil data produk
|--------------------------------------------------------------------------
*/
$stmtProduct = $conn->prepare("
    SELECT *
    FROM products
    WHERE product_id = :product_id
");

$stmtProduct->execute([
    ":product_id" => $product_id
]);

$product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produk tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| Ambil kategori
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
| Ambil supplier
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
| Proses update produk
|--------------------------------------------------------------------------
*/
$error = "";

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

    } elseif ($min_stock < 0) {

        $error = "Minimum stok tidak boleh kurang dari 0.";

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Cek SKU
            |--------------------------------------------------------------------------
            */
            if ($sku !== "") {

                $stmtCheckSku = $conn->prepare("
                    SELECT product_id
                    FROM products
                    WHERE sku = :sku
                    AND product_id != :product_id
                    LIMIT 1
                ");

                $stmtCheckSku->execute([
                    ":sku" => $sku,
                    ":product_id" => $product_id
                ]);

                if ($stmtCheckSku->fetch()) {
                    throw new Exception("SKU sudah digunakan oleh produk lain.");
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Cek Barcode
            |--------------------------------------------------------------------------
            */
            if ($barcode !== "") {

                $stmtCheckBarcode = $conn->prepare("
                    SELECT product_id
                    FROM products
                    WHERE barcode = :barcode
                    AND product_id != :product_id
                    LIMIT 1
                ");

                $stmtCheckBarcode->execute([
                    ":barcode" => $barcode,
                    ":product_id" => $product_id
                ]);

                if ($stmtCheckBarcode->fetch()) {
                    throw new Exception("Barcode sudah digunakan oleh produk lain.");
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Update produk
            |--------------------------------------------------------------------------
            */
            $stmtUpdate = $conn->prepare("
                UPDATE products
                SET
                    category_id = :category_id,
                    supplier_id = :supplier_id,
                    sku = :sku,
                    barcode = :barcode,
                    product_name = :product_name,
                    purchase_price = :purchase_price,
                    selling_price = :selling_price,
                    unit = :unit,
                    min_stock = :min_stock,
                    status = :status
                WHERE product_id = :product_id
            ");

            $stmtUpdate->execute([

                ":category_id" => $category_id,

                ":supplier_id" => $supplier_id,

                ":sku" => $sku !== ""
                    ? $sku
                    : null,

                ":barcode" => $barcode !== ""
                    ? $barcode
                    : null,

                ":product_name" => $product_name,

                ":purchase_price" => $purchase_price,

                ":selling_price" => $selling_price,

                ":unit" => $unit,

                ":min_stock" => $min_stock,

                ":status" => $status,

                ":product_id" => $product_id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Kembali ke daftar produk
            |--------------------------------------------------------------------------
            */
            header("Location: index.php");

            exit;


        } catch (Exception $e) {

            $error = "Gagal mengubah produk: " . $e->getMessage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Jika gagal, tampilkan data yang baru diinput
    |--------------------------------------------------------------------------
    */
    $product["sku"] = $sku;
    $product["barcode"] = $barcode;
    $product["product_name"] = $product_name;
    $product["category_id"] = $category_id;
    $product["supplier_id"] = $supplier_id;
    $product["purchase_price"] = $purchase_price;
    $product["selling_price"] = $selling_price;
    $product["unit"] = $unit;
    $product["min_stock"] = $min_stock;
    $product["status"] = $status;
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

    <title>Edit Produk - BEKUKU</title>


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

                            Edit Produk

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

                                Edit Produk

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

                        <div class="card card-warning">


                            <!-- Card Header -->

                            <div class="card-header">

                                <h3 class="card-title">

                                    Edit Data Produk

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
                                            value="<?= htmlspecialchars($product["sku"] ?? ""); ?>"
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
                                            value="<?= htmlspecialchars($product["barcode"] ?? ""); ?>"
                                        >

                                    </div>



                                    <!-- Nama -->

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
                                            required
                                            value="<?= htmlspecialchars($product["product_name"]); ?>"
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
                                                    <?= ($product["category_id"] == $category["category_id"]) ? "selected" : ""; ?>
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
                                                    <?= ($product["supplier_id"] == $supplier["supplier_id"]) ? "selected" : ""; ?>
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
                                                        value="<?= htmlspecialchars($product["purchase_price"]); ?>"
                                                    >

                                                </div>

                                            </div>

                                        </div>



                                        <!-- Harga Jual -->

                                        <div class="col-md-6">

                                            <div class="mb-3">

                                                <label class="form-label">

                                                    Harga Jual

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
                                                        value="<?= htmlspecialchars($product["selling_price"]); ?>"
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
                                                        <?= $product["unit"] === "pcs" ? "selected" : ""; ?>
                                                    >

                                                        pcs

                                                    </option>


                                                    <option
                                                        value="pack"
                                                        <?= $product["unit"] === "pack" ? "selected" : ""; ?>
                                                    >

                                                        pack

                                                    </option>


                                                    <option
                                                        value="box"
                                                        <?= $product["unit"] === "box" ? "selected" : ""; ?>
                                                    >

                                                        box

                                                    </option>


                                                    <option
                                                        value="kg"
                                                        <?= $product["unit"] === "kg" ? "selected" : ""; ?>
                                                    >

                                                        kg

                                                    </option>

                                                </select>

                                            </div>

                                        </div>



                                        <!-- Stok Saat Ini -->

                                        <div class="col-md-4">

                                            <div class="mb-3">

                                                <label class="form-label">

                                                    Stok Saat Ini

                                                </label>


                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    value="<?= $product["stock"]; ?>"
                                                    disabled
                                                >


                                                <div class="form-text">

                                                    Stok diubah melalui menu Tambah Stok.

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
                                                    value="<?= htmlspecialchars($product["min_stock"]); ?>"
                                                >

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
                                                <?= $product["status"] === "aktif" ? "selected" : ""; ?>
                                            >

                                                Aktif

                                            </option>


                                            <option
                                                value="nonaktif"
                                                <?= $product["status"] === "nonaktif" ? "selected" : ""; ?>
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
                                        class="btn btn-warning"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                        Simpan Perubahan

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