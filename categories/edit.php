<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";

$category_id = (int) ($_GET["id"] ?? 0);

if ($category_id <= 0) {
    die("ID kategori tidak valid.");
}


// Ambil data kategori
$stmt = $conn->prepare("
    SELECT *
    FROM categories
    WHERE category_id = :category_id
");

$stmt->execute([
    ":category_id" => $category_id
]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$category) {
    die("Kategori tidak ditemukan.");
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");


    if ($name === "") {

        $error = "Nama kategori wajib diisi.";

    } else {

        // Cek nama kategori yang sama
        $stmt = $conn->prepare("
            SELECT category_id
            FROM categories
            WHERE name = :name
            AND category_id != :category_id
        ");

        $stmt->execute([
            ":name" => $name,
            ":category_id" => $category_id
        ]);


        if ($stmt->fetch()) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $stmt = $conn->prepare("
                UPDATE categories
                SET name = :name
                WHERE category_id = :category_id
            ");

            $stmt->execute([
                ":name" => $name,
                ":category_id" => $category_id
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

    <title>Edit Kategori - BEKUKU POS</title>

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
                            Edit Kategori
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
                                    Kategori
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


        <div class="app-content">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-md-8">

                        <div class="card">

                            <div class="card-header">

                                <h3 class="card-title">

                                    <i class="bi bi-pencil"></i>

                                    Form Edit Kategori

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
                                            for="name"
                                            class="form-label"
                                        >
                                            Nama Kategori
                                        </label>

                                        <input
                                            type="text"
                                            name="name"
                                            id="name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($category["name"]); ?>"
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