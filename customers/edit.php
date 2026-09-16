<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";


$customer_id = (int) ($_GET["id"] ?? 0);


if ($customer_id <= 0) {

    die("ID customer tidak valid.");

}


// Ambil data customer

$stmt = $conn->prepare("
    SELECT *
    FROM customers
    WHERE customer_id = :customer_id
");

$stmt->execute([
    ":customer_id" => $customer_id
]);


$customer = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$customer) {

    die("Customer tidak ditemukan.");

}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_name = trim(
        $_POST["customer_name"] ?? ""
    );


    if ($customer_name === "") {

        $error = "Nama customer wajib diisi.";

    } else {


        // Cek nama customer yang sama

        $stmt = $conn->prepare("
            SELECT customer_id
            FROM customers
            WHERE customer_name = :customer_name
            AND customer_id != :customer_id
        ");

        $stmt->execute([

            ":customer_name" => $customer_name,

            ":customer_id" => $customer_id

        ]);


        if ($stmt->fetch()) {

            $error = "Customer tersebut sudah ada.";

        } else {


            // Update customer

            $stmt = $conn->prepare("
                UPDATE customers
                SET customer_name = :customer_name
                WHERE customer_id = :customer_id
            ");

            $stmt->execute([

                ":customer_name" => $customer_name,

                ":customer_id" => $customer_id

            ]);


            header("Location: index.php");
            exit;

        }

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

    <title>Edit Customer - BEKUKU POS</title>


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
                            Edit Customer
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
                                    Customer
                                </a>

                            </li>


                            <li class="breadcrumb-item active">
                                Edit
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

                                    <i class="bi bi-pencil"></i>

                                    Form Edit Customer

                                </h3>

                            </div>


                            <form method="POST"><?= bekuku_csrf_field() ?><div class="card-body">


                                    <?php if ($error !== ""): ?>

                                        <div class="alert alert-danger">

                                            <i class="bi bi-exclamation-triangle"></i>

                                            <?= htmlspecialchars($error); ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="mb-3">


                                        <label
                                            for="customer_name"
                                            class="form-label"
                                        >

                                            Nama Customer

                                        </label>


                                        <input
                                            type="text"
                                            name="customer_name"
                                            id="customer_name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer["customer_name"]); ?>"
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

                                        Simpan Perubahan

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