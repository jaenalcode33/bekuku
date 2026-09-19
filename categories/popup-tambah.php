<?php

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";


$error = "";


/*
 * =========================================================
 * PROSES SIMPAN
 * =========================================================
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
     * Cek CSRF
     */

    bekuku_verify_csrf();


    $name = trim(
        $_POST["name"] ?? ""
    );


    /*
     * Validasi nama
     */

    if ($name === "") {

        $error = "Nama kategori wajib diisi.";

    } else {


        /*
         * Cek kategori yang sama
         */

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


            /*
             * Simpan kategori
             */

            $stmt = $conn->prepare("
                INSERT INTO categories (name)
                VALUES (:name)
            ");


            $stmt->execute([
                ":name" => $name
            ]);


            /*
             * Beri tahu halaman utama
             * bahwa kategori berhasil disimpan.
             */

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
                            type: "kategori-berhasil-disimpan"
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


    <title>Tambah Kategori</title>


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- CSS POPUP KATEGORI -->

    <link
        rel="stylesheet"
        href="<?= bekuku_url('assets/css/popup-kategori.css') ?>?v=2026091905"
    >


</head>


<body class="popup-kategori-page">


    <div class="popup-kategori-form">


        <!-- =====================================================
             HEADER FORM
             ===================================================== -->

        <div class="popup-kategori-form-header">


            <div class="popup-kategori-form-title">


                <i class="bi bi-plus-circle"></i>


                <span>
                    Form Tambah Kategori
                </span>


            </div>


        </div>



        <!-- =====================================================
             ERROR
             ===================================================== -->

        <?php if ($error !== ""): ?>


            <div class="popup-kategori-error">


                <i class="bi bi-exclamation-triangle"></i>


                <span>

                    <?= htmlspecialchars($error); ?>

                </span>


            </div>


        <?php endif; ?>



        <!-- =====================================================
             FORM
             ===================================================== -->

        <form
            method="POST"
            id="formTambahKategori"
        >


            <?= bekuku_csrf_field() ?>


            <div class="popup-kategori-form-body">


                <div class="popup-kategori-field">


                    <label for="name">


                        Nama Kategori

                        <span>*</span>


                    </label>


                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="popup-kategori-input"
                        placeholder="Contoh: Nugget"
                        value="<?= htmlspecialchars(
                            $_POST["name"] ?? ""
                        ); ?>"
                        required
                        autofocus
                    >


                </div>


            </div>



            <!-- =================================================
                 FOOTER
                 ================================================= -->

            <div class="popup-kategori-form-footer">


                <button
                    type="button"
                    id="btnBatalKategori"
                    class="popup-kategori-button popup-kategori-button-cancel"
                >


                    Batal


                </button>



                <button
                    type="submit"
                    class="popup-kategori-button popup-kategori-button-save"
                >


                    <i class="bi bi-check2-square"></i>


                    Simpan Kategori


                </button>


            </div>


        </form>


    </div>



<script
    src="<?= bekuku_url('assets/js/popup-kategori.js') ?>?v=2026091905"
></script>


</body>

</html>