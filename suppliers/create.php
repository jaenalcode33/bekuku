<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";

$error = "";
$is_popup = ($_GET["popup"] ?? "") === "1";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $supplier_name = trim($_POST["supplier_name"] ?? "");


    if ($supplier_name === "") {

        $error = "Nama supplier wajib diisi.";

    } else {


        // Cek apakah supplier sudah ada

        $stmt = $conn->prepare("
            SELECT supplier_id
            FROM suppliers
            WHERE supplier_name = :supplier_name
        ");

        $stmt->execute([
            ":supplier_name" => $supplier_name
        ]);


        if ($stmt->fetch()) {

            $error = "Supplier tersebut sudah ada.";

        } else {


            // Simpan supplier

            $stmt = $conn->prepare("
                INSERT INTO suppliers (supplier_name)
                VALUES (:supplier_name)
            ");

            $stmt->execute([
                ":supplier_name" => $supplier_name
            ]);


            header("Location: index.php");
            exit;

        }

    }

}

?>
<?php if ($is_popup): ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Supplier</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= bekuku_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= bekuku_url('popup/popup.css') ?>?v=2026091646">
</head>
<body class="popup-form-page">
    <form method="POST" class="popup-form-card"><?= bekuku_csrf_field() ?>
        <div class="popup-form-body">
            <?php if ($error !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <label for="supplier_name">Nama Supplier</label>
            <input type="text" name="supplier_name" id="supplier_name"
                placeholder="Contoh: PT Sumber Frozen Food" required autofocus>
        </div>
        <div class="popup-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <button type="button" class="btn btn-secondary popup-close">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>
        </div>
    </form>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Supplier - BEKUKU POS</title>


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


        <!-- Header -->

        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">


                    <div class="col-sm-6">

                        <h3 class="mb-0">
                            Tambah Supplier
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
                                    Supplier
                                </a>

                            </li>


                            <li class="breadcrumb-item active">
                                Tambah
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

                    <div class="col-md-8">


                        <div class="card">


                            <div class="card-header">

                                <h3 class="card-title">

                                    <i class="bi bi-plus-circle"></i>

                                    Form Tambah Supplier

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


                                    <div class="mb-3">


                                        <label
                                            for="supplier_name"
                                            class="form-label"
                                        >

                                            Nama Supplier

                                        </label>


                                        <input
                                            type="text"
                                            name="supplier_name"
                                            id="supplier_name"
                                            class="form-control"
                                            placeholder="Contoh: PT Sumber Frozen Food"
                                            required
                                            autofocus
                                        >


                                    </div>


                                </div>


                                <div class="card-footer">


                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-save"></i>

                                        Simpan

                                    </button>


                                    <a
                                        href="index.php"
                                        class="btn btn-secondary"
                                    >

                                        <i class="bi bi-arrow-left"></i>

                                        Kembali

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