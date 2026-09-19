<?php

require_once __DIR__ . "/../config/app.php";
require_once "../config/database.php";

$error = "";

$is_popup = ($_GET["popup"] ?? "") === "1";
$saved = ($_GET["saved"] ?? "") === "1";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_name = trim(
        $_POST["customer_name"] ?? ""
    );


    if ($customer_name === "") {

        $error = "Nama customer wajib diisi.";

    } else {

        // Cek customer
        $stmt = $conn->prepare("
            SELECT customer_id
            FROM customers
            WHERE customer_name = :customer_name
        ");

        $stmt->execute([
            ":customer_name" => $customer_name
        ]);


        if ($stmt->fetch()) {

            $error = "Customer tersebut sudah ada.";

        } else {

            // Simpan customer
            $stmt = $conn->prepare("
                INSERT INTO customers (customer_name)
                VALUES (:customer_name)
            ");

            $stmt->execute([
                ":customer_name" => $customer_name
            ]);


            if ($is_popup) {

                header(
                    "Location: popup-tambah.php?popup=1&saved=1"
                );

                exit;

            }


            header("Location: index.php");
            exit;

        }

    }

}


if ($is_popup):

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Customer</title>


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup-customer.css') ?>?v=20260919"
    >

</head>


<body class="popup-customer-page">


<form
    method="POST"
    class="popup-customer-form"
>

    <?= bekuku_csrf_field() ?>


    <div class="popup-customer-form-body">


        <?php if ($error !== ""): ?>

            <div class="popup-customer-error">

                <i class="bi bi-exclamation-circle"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <div class="popup-customer-field">

            <label for="customer_name">

                Nama Customer

            </label>


            <input
                type="text"
                name="customer_name"
                id="customer_name"
                placeholder="Contoh: Budi"
                value="<?= htmlspecialchars(
                    $_POST["customer_name"] ?? ""
                ) ?>"
                required
                autofocus
            >

        </div>


    </div>


    <div class="popup-customer-form-footer">


        <button
            type="submit"
            class="popup-customer-btn popup-customer-btn-save"
        >

            <i class="bi bi-save"></i>

            Simpan

        </button>


        <button
            type="button"
            class="popup-customer-btn popup-customer-btn-cancel popup-close"
        >

            <i class="bi bi-arrow-left"></i>

            Kembali

        </button>


    </div>


</form>


<script
    src="<?= bekuku_url('assets/js/popup-customer-form.js') ?>?v=20260919"
></script>


</body>

</html>

<?php

exit;

endif;

?>