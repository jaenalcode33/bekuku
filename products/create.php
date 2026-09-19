<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

$error = "";

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
| Proses form
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
            | Cek SKU
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
            | Cek Barcode
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
            | ID produk
            |--------------------------------------------------------------------------
            */

            $product_id = $conn->lastInsertId();

            /*
            |--------------------------------------------------------------------------
            | Catat stok awal
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
            | Jika popup
            |--------------------------------------------------------------------------
            */

            if (
                isset($_GET["popup"]) &&
                $_GET["popup"] === "1"
            ) {
                ?>
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <meta
                        name="viewport"
                        content="width=device-width, initial-scale=1.0"
                    >

                    <title>Produk Berhasil Ditambahkan</title>

                    <link
                        rel="stylesheet"
                        href="<?= bekuku_url('assets/css/style.css') ?>"
                    >

                    <link
                        rel="stylesheet"
                        href="<?= bekuku_url('assets/css/popup.css') ?>?v=20260919"
                    >
                </head>

                <body class="bekuku-product-popup-page">

                    <div class="bekuku-success-page">

                        <div class="bekuku-success-icon">
                            ✓
                        </div>

                        <h2>Produk Berhasil Ditambahkan</h2>

                        <p>
                            Produk berhasil disimpan.
                        </p>

                    </div>

                    <script>
                        setTimeout(function () {
                            if (window.parent && window.parent !== window) {
                                window.parent.location.reload();
                            } else {
                                window.location.href = "index.php";
                            }
                        }, 500);
                    </script>

                </body>
                </html>
                <?php

                exit;
            }

            header("Location: index.php");
            exit;

        } catch (Exception $e) {

            $error = "Gagal menambahkan produk: " . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Mode popup
|--------------------------------------------------------------------------
*/

$isPopup =
    isset($_GET["popup"]) &&
    $_GET["popup"] === "1";


/*
|--------------------------------------------------------------------------
| POPUP
|--------------------------------------------------------------------------
*/

if ($isPopup):
?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Produk</title>

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup.css') ?>?v=2026091904"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

</head>


<body class="bekuku-product-popup-page">


<div class="bekuku-product-popup">


    <!-- FORM -->

    <form
        method="POST"
        class="bekuku-product-form"
        autocomplete="off"
    >

        <?= bekuku_csrf_field() ?>


        <?php if ($error !== ""): ?>

            <div class="bekuku-product-error">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>


        <div class="bekuku-product-grid">


            <!-- SKU -->

            <div class="bekuku-field">

                <label for="sku">
                    SKU
                </label>

                <input
                    id="sku"
                    type="text"
                    name="sku"
                    placeholder="Contoh: BK-001"
                    value="<?= htmlspecialchars($_POST["sku"] ?? "") ?>"
                >

            </div>


            <!-- BARCODE -->

            <div class="bekuku-field">

                <label for="barcode">
                    Barcode
                </label>

                <input
                    id="barcode"
                    type="text"
                    name="barcode"
                    placeholder="Contoh: 8991234567890"
                    value="<?= htmlspecialchars($_POST["barcode"] ?? "") ?>"
                >

            </div>


            <!-- NAMA PRODUK -->

            <div class="bekuku-field bekuku-field-full">

                <label for="product_name">

                    Nama Produk

                    <span>*</span>

                </label>

                <input
                    id="product_name"
                    type="text"
                    name="product_name"
                    placeholder="Contoh: Nugget Ayam"
                    required
                    value="<?= htmlspecialchars($_POST["product_name"] ?? "") ?>"
                >

            </div>


            <!-- KATEGORI -->

            <div class="bekuku-field">

                <label for="category_id">

                    Kategori

                    <span>*</span>

                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                >

                    <option value="">
                        -- Pilih Kategori --
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= $category["category_id"] ?>"
                            <?= (
                                ($_POST["category_id"] ?? "") ==
                                $category["category_id"]
                            )
                                ? "selected"
                                : ""
                            ?>
                        >

                            <?= htmlspecialchars($category["name"]) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- SUPPLIER -->

            <div class="bekuku-field">

                <label for="supplier_id">
                    Supplier
                </label>

                <select
                    id="supplier_id"
                    name="supplier_id"
                >

                    <option value="">
                        -- Tidak Ada Supplier --
                    </option>

                    <?php foreach ($suppliers as $supplier): ?>

                        <option
                            value="<?= $supplier["supplier_id"] ?>"
                            <?= (
                                ($_POST["supplier_id"] ?? "") ==
                                $supplier["supplier_id"]
                            )
                                ? "selected"
                                : ""
                            ?>
                        >

                            <?= htmlspecialchars(
                                $supplier["supplier_name"]
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- HARGA BELI -->

            <div class="bekuku-field">

                <label for="purchase_price">

                    Harga Beli

                    <span>*</span>

                </label>

                <div class="bekuku-money">

                    <span>Rp</span>

                    <input
                        id="purchase_price"
                        type="number"
                        name="purchase_price"
                        min="0"
                        step="0.01"
                        required
                        value="<?= htmlspecialchars(
                            $_POST["purchase_price"] ?? "0"
                        ) ?>"
                    >

                </div>

            </div>


            <!-- HARGA JUAL -->

            <div class="bekuku-field">

                <label for="selling_price">

                    Harga Jual

                    <span>*</span>

                </label>

                <div class="bekuku-money">

                    <span>Rp</span>

                    <input
                        id="selling_price"
                        type="number"
                        name="selling_price"
                        min="0"
                        step="0.01"
                        required
                        value="<?= htmlspecialchars(
                            $_POST["selling_price"] ?? "0"
                        ) ?>"
                    >

                </div>

            </div>


            <!-- SATUAN -->

            <div class="bekuku-field">

                <label for="unit">
                    Satuan
                </label>

                <select
                    id="unit"
                    name="unit"
                >

                    <?php
                    $units = [
                        "pcs" => "pcs",
                        "pack" => "pack",
                        "box" => "box",
                        "kg" => "kg"
                    ];
                    ?>

                    <?php foreach ($units as $value => $label): ?>

                        <option
                            value="<?= $value ?>"
                            <?= (
                                ($_POST["unit"] ?? "pcs") === $value
                            )
                                ? "selected"
                                : ""
                            ?>
                        >

                            <?= $label ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- STOK AWAL -->

            <div class="bekuku-field">

                <label for="stock">
                    Stok Awal
                </label>

                <input
                    id="stock"
                    type="number"
                    name="stock"
                    min="0"
                    value="<?= htmlspecialchars(
                        $_POST["stock"] ?? "0"
                    ) ?>"
                >

            </div>


            <!-- MINIMUM STOK -->

            <div class="bekuku-field">

                <label for="min_stock">
                    Minimum Stok
                </label>

                <input
                    id="min_stock"
                    type="number"
                    name="min_stock"
                    min="0"
                    value="<?= htmlspecialchars(
                        $_POST["min_stock"] ?? "5"
                    ) ?>"
                >

            </div>


            <!-- STATUS -->

            <div class="bekuku-field">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="aktif"
                        <?= (
                            ($_POST["status"] ?? "aktif") === "aktif"
                        )
                            ? "selected"
                            : ""
                        ?>
                    >
                        Aktif
                    </option>

                    <option
                        value="nonaktif"
                        <?= (
                            ($_POST["status"] ?? "") === "nonaktif"
                        )
                            ? "selected"
                            : ""
                        ?>
                    >
                        Nonaktif
                    </option>

                </select>

            </div>


        </div>


        <!-- BUTTON -->

        <div class="bekuku-product-actions">

            <button
                type="button"
                class="bekuku-btn bekuku-btn-secondary"
                data-popup-close
            >

                Batal

            </button>


            <button
                type="submit"
                class="bekuku-btn bekuku-btn-primary"
            >

                <i class="bi bi-save"></i>

                Simpan Produk

            </button>

        </div>


    </form>

</div>


</body>

</html>

<?php
exit;
endif;


/*
|--------------------------------------------------------------------------
| HALAMAN NORMAL
|--------------------------------------------------------------------------
*/
?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Produk - BEKUKU</title>

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
        href="<?= bekuku_url('assets/css/style.css') ?>"
    >

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <main class="app-main">


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


                            <form method="POST">

                                <?= bekuku_csrf_field() ?>


                                <div class="card-body">


                                    <?php if ($error !== ""): ?>

                                        <div class="alert alert-danger">

                                            <i class="bi bi-exclamation-triangle"></i>

                                            <?= htmlspecialchars($error) ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="row">


                                        <div class="col-md-6 mb-3">

                                            <label class="form-label">
                                                SKU
                                            </label>

                                            <input
                                                type="text"
                                                name="sku"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $_POST["sku"] ?? ""
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="col-md-6 mb-3">

                                            <label class="form-label">
                                                Barcode
                                            </label>

                                            <input
                                                type="text"
                                                name="barcode"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $_POST["barcode"] ?? ""
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="col-md-12 mb-3">

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
                                                value="<?= htmlspecialchars(
                                                    $_POST["product_name"] ?? ""
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="col-md-6 mb-3">

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
                                                        value="<?= $category["category_id"] ?>"
                                                        <?= (
                                                            ($_POST["category_id"] ?? "") ==
                                                            $category["category_id"]
                                                        )
                                                            ? "selected"
                                                            : ""
                                                        ?>
                                                    >

                                                        <?= htmlspecialchars(
                                                            $category["name"]
                                                        ) ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>


                                        <div class="col-md-6 mb-3">

                                            <label class="form-label">
                                                Supplier
                                            </label>

                                            <div class="d-flex gap-2">

    <select
        name="supplier_id"
        id="supplier_id"
        class="form-select"
    >

        <option value="">
            -- Tidak Ada Supplier --
        </option>

        <?php foreach ($suppliers as $supplier): ?>

            <option
                value="<?= (int)$supplier["supplier_id"] ?>"
            >
                <?= htmlspecialchars($supplier["supplier_name"]) ?>
            </option>

        <?php endforeach; ?>

    </select>


    <button
        type="button"
        class="btn btn-primary"
        data-popup-supplier
    >

        <i class="bi bi-plus-lg"></i>

        Tambah

    </button>

</div>

                                        </div>


                                        <div class="col-md-6 mb-3">

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
                                                    value="<?= htmlspecialchars(
                                                        $_POST["purchase_price"] ?? "0"
                                                    ) ?>"
                                                >

                                            </div>

                                        </div>


                                        <div class="col-md-6 mb-3">

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
                                                    value="<?= htmlspecialchars(
                                                        $_POST["selling_price"] ?? "0"
                                                    ) ?>"
                                                >

                                            </div>

                                        </div>


                                        <div class="col-md-4 mb-3">

                                            <label class="form-label">
                                                Satuan
                                            </label>

                                            <select
                                                name="unit"
                                                class="form-select"
                                            >

                                                <?php foreach ($units as $value => $label): ?>

                                                    <option
                                                        value="<?= $value ?>"
                                                        <?= (
                                                            ($_POST["unit"] ?? "pcs") === $value
                                                        )
                                                            ? "selected"
                                                            : ""
                                                        ?>
                                                    >

                                                        <?= $label ?>

                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>


                                        <div class="col-md-4 mb-3">

                                            <label class="form-label">
                                                Stok Awal
                                            </label>

                                            <input
                                                type="number"
                                                name="stock"
                                                class="form-control"
                                                min="0"
                                                value="<?= htmlspecialchars(
                                                    $_POST["stock"] ?? "0"
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="col-md-4 mb-3">

                                            <label class="form-label">
                                                Minimum Stok
                                            </label>

                                            <input
                                                type="number"
                                                name="min_stock"
                                                class="form-control"
                                                min="0"
                                                value="<?= htmlspecialchars(
                                                    $_POST["min_stock"] ?? "5"
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="col-md-12 mb-3">

                                            <label class="form-label">
                                                Status
                                            </label>

                                            <select
                                                name="status"
                                                class="form-select"
                                            >

                                                <option
                                                    value="aktif"
                                                    <?= (
                                                        ($_POST["status"] ?? "aktif") === "aktif"
                                                    )
                                                        ? "selected"
                                                        : ""
                                                    ?>
                                                >
                                                    Aktif
                                                </option>

                                                <option
                                                    value="nonaktif"
                                                    <?= (
                                                        ($_POST["status"] ?? "") === "nonaktif"
                                                    )
                                                        ? "selected"
                                                        : ""
                                                    ?>
                                                >
                                                    Nonaktif
                                                </option>

                                            </select>

                                        </div>


                                    </div>


                                </div>


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


<script
    src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"
></script>

<script
    src="<?= bekuku_url('assets/js/popup-supplier.js') ?>?v=1"
></script>

</body>

</html>