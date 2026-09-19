<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

$error = "";


/* =========================================================
   SIMPAN SUPPLIER
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $supplier_name = trim($_POST["supplier_name"] ?? "");


    if ($supplier_name === "") {

        $error = "Nama supplier wajib diisi.";

    } else {

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

            $stmt = $conn->prepare("
                INSERT INTO suppliers (supplier_name)
                VALUES (:supplier_name)
            ");

            $stmt->execute([
                ":supplier_name" => $supplier_name
            ]);


            ?>

            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Berhasil</title>
            </head>

            <body>

            <script>

                window.parent.postMessage(
                    {
                        type: "supplier-berhasil-disimpan"
                    },
                    window.location.origin
                );

            </script>

            </body>
            </html>

            <?php

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

    <title>Tambah Supplier</title>


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup-supplier.css') ?>?v=1"
    >

</head>


<body class="popup-supplier-page">


<div class="popup-supplier-form">


    <div class="popup-supplier-form-body">


        <?php if ($error !== ""): ?>

            <div class="popup-supplier-error">

                <i class="bi bi-exclamation-triangle"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            id="formTambahSupplier"
        >

            <?= bekuku_csrf_field() ?>


            <div class="popup-supplier-field">

                <label for="supplier_name">

                    Nama Supplier

                    <span>*</span>

                </label>


                <input
                    type="text"
                    name="supplier_name"
                    id="supplier_name"
                    class="popup-supplier-input"
                    placeholder="Contoh: PT Sumber Frozen Food"
                    value="<?= htmlspecialchars($_POST["supplier_name"] ?? "") ?>"
                    required
                    autofocus
                >

            </div>


            <div class="popup-supplier-form-footer">


                <button
                    type="button"
                    class="popup-supplier-button popup-supplier-button-cancel"
                    id="btnBatalSupplier"
                >

                    Batal

                </button>


                <button
                    type="submit"
                    class="popup-supplier-button popup-supplier-button-save"
                >

                    <i class="bi bi-check2-square"></i>

                    Simpan Supplier

                </button>


            </div>


        </form>


    </div>


</div>


<script
    src="<?= bekuku_url('assets/js/popup-supplier.js') ?>?v=1"
></script>


</body>

</html>