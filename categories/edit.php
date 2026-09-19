<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";


$category_id =
    (int) (
        $_GET["id"] ?? 0
    );


$isPopup =
    isset($_GET["popup"])
    && $_GET["popup"] === "1";


if ($category_id <= 0) {

    die("ID kategori tidak valid.");

}


/* =========================================================
   AMBIL KATEGORI
   ========================================================= */

$stmt =
    $conn->prepare("
        SELECT *
        FROM categories
        WHERE category_id = :category_id
        LIMIT 1
    ");


$stmt->execute([
    ":category_id" => $category_id
]);


$category =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$category) {

    die("Kategori tidak ditemukan.");

}


$error = "";


/* =========================================================
   UPDATE
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim(
            $_POST["name"] ?? ""
        );


    if ($name === "") {

        $error =
            "Nama kategori wajib diisi.";

    } else {

        /*
         * Cek nama duplikat
         */

        $stmt =
            $conn->prepare("
                SELECT category_id
                FROM categories
                WHERE name = :name
                AND category_id != :category_id
                LIMIT 1
            ");


        $stmt->execute([
            ":name" =>
                $name,

            ":category_id" =>
                $category_id
        ]);


        if ($stmt->fetch()) {

            $error =
                "Kategori tersebut sudah ada.";

        } else {

            /*
             * Update
             */

            $stmt =
                $conn->prepare("
                    UPDATE categories
                    SET name = :name
                    WHERE category_id = :category_id
                ");


            $stmt->execute([
                ":name" =>
                    $name,

                ":category_id" =>
                    $category_id
            ]);


            /*
             * Popup
             */

            if ($isPopup) {

                header(
                    "Location: edit.php?id="
                    . $category_id
                    . "&popup=1&saved=1"
                );

                exit;

            }


            header(
                "Location: index.php"
            );

            exit;

        }

    }

}


/* =========================================================
   MODE POPUP
   ========================================================= */

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

    <title>Edit Kategori</title>


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
        href="<?= bekuku_url('assets/css/style.css') ?>?v=2026091902"
    >


    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup.css') ?>?v=2026091902"
    >

</head>


<body class="bekuku-popup-page">


<div class="bekuku-category-popup">


    <form
        method="POST"
        class="bekuku-category-form"
    >

        <?= bekuku_csrf_field() ?>


        <?php if ($error !== ""): ?>

            <div class="bekuku-popup-error">

                <i class="bi bi-exclamation-triangle"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>


        <div class="bekuku-category-field">

            <label for="name">

                Nama Kategori

                <span>*</span>

            </label>


            <input
                type="text"
                name="name"
                id="name"
                value="<?= htmlspecialchars(
                    $_POST["name"]
                    ?? $category["name"]
                ) ?>"
                required
                autofocus
                autocomplete="off"
            >

        </div>


        <div class="bekuku-category-actions">


            <button
                type="button"
                class="bekuku-btn bekuku-btn-secondary"
                data-popup-close
            >

                <i class="bi bi-x-lg"></i>

                Batal

            </button>


            <button
                type="submit"
                class="bekuku-btn bekuku-btn-primary"
            >

                <i class="bi bi-save"></i>

                Simpan Perubahan

            </button>


        </div>


    </form>


</div>


<?php if (
    isset($_GET["saved"])
    && $_GET["saved"] === "1"
): ?>

    <div
        data-popup-saved="1"
        style="display:none;"
    ></div>

<?php endif; ?>


</body>

</html>

<?php

exit;

endif;


/* =========================================================
   HALAMAN NORMAL
   ========================================================= */

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


    <?php
    require_once __DIR__ . "/../includes/header.php";
    ?>


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


                            <form method="POST">

                                <?= bekuku_csrf_field() ?>


                                <div class="card-body">

                                    <?php if ($error !== ""): ?>

                                        <div class="alert alert-danger">

                                            <i class="bi bi-exclamation-triangle"></i>

                                            <?= htmlspecialchars($error) ?>

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
                                            value="<?= htmlspecialchars(
                                                $category["name"]
                                            ) ?>"
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


    <?php
    require_once __DIR__ . "/../includes/footer.php";
    ?>


</div>


<script
    src="<?= bekuku_url('assets/js/adminlte.min.js') ?>"
></script>


</body>

</html>