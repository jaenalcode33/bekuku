<?php

require_once __DIR__ . "/../config/app.php";


require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    if ($name === "") {

        $error = "Nama kategori wajib diisi.";

    } else {

        // Cek apakah kategori sudah ada
        $stmt = $conn->prepare("
            SELECT category_id
            FROM categories
            WHERE name = :name
        ");

        $stmt->execute([
            ":name" => $name
        ]);

        if ($stmt->fetch()) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO categories (name)
                VALUES (:name)
            ");

            $stmt->execute([
                ":name" => $name
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

    <title>Tambah Kategori - BEKUKU POS</title>

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
    <link
    rel="stylesheet"
    href="<?= bekuku_url('assets/css/popup.css') ?>?v=2026091904"
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
                            Tambah Kategori
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
                                Tambah
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

                                    <i class="bi bi-plus-circle"></i>

                                    Form Tambah Kategori

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
                                            placeholder="Contoh: Nugget"
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