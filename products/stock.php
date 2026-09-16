<?php

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| AMBIL ID PRODUK
|--------------------------------------------------------------------------
*/

$product_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


/*
|--------------------------------------------------------------------------
| VALIDASI ID
|--------------------------------------------------------------------------
*/

if ($product_id <= 0) {

    die("ID produk tidak valid.");

}


/*
|--------------------------------------------------------------------------
| PROSES TAMBAH STOK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = (int) ($_POST['quantity'] ?? 0);

    if ($quantity <= 0) {

        die("Jumlah stok harus lebih dari 0.");

    }


    /*
    |--------------------------------------------------------------------------
    | MULAI TRANSAKSI DATABASE
    |--------------------------------------------------------------------------
    */

    $conn->beginTransaction();


    try {

        /*
        |--------------------------------------------------------------------------
        | UPDATE STOK PRODUK
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE products
            SET stock = stock + :quantity
            WHERE product_id = :product_id
        ");

        $stmt->execute([
            ":quantity" => $quantity,
            ":product_id" => $product_id
        ]);


        /*
        |--------------------------------------------------------------------------
        | CATAT STOK MASUK
        |--------------------------------------------------------------------------
        */

        $stmtMovement = $conn->prepare("
            INSERT INTO stock_movements (
                product_id,
                movement_type,
                quantity,
                reference_type,
                note
            )
            VALUES (
                :product_id,
                'masuk',
                :quantity,
                'restock',
                'Penambahan stok manual'
            )
        ");

        $stmtMovement->execute([
            ":product_id" => $product_id,
            ":quantity" => $quantity
        ]);


        /*
        |--------------------------------------------------------------------------
        | SIMPAN PERUBAHAN
        |--------------------------------------------------------------------------
        */

        $conn->commit();


        /*
        |--------------------------------------------------------------------------
        | KEMBALI KE DAFTAR PRODUK
        |--------------------------------------------------------------------------
        */

        header("Location: index.php");

        exit;


    } catch (Exception $e) {

        $conn->rollBack();

        die(
            "Gagal menambahkan stok: "
            . $e->getMessage()
        );

    }

}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PRODUK
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        product_id,
        product_name,
        stock,
        unit
    FROM products
    WHERE product_id = :product_id
");

$stmt->execute([
    ":product_id" => $product_id
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRODUK TIDAK DITEMUKAN
|--------------------------------------------------------------------------
*/

if (!$product) {

    die("Produk tidak ditemukan.");

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Stok - BEKUKU</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>

    <?php require_once __DIR__ . "/../includes/header.php"; ?>


    <h1>Tambah Stok Produk</h1>


    <br>


    <a href="index.php">
        ← Kembali ke Produk
    </a>


    <br><br>


    <!-- =========================================================
         INFORMASI PRODUK
    ========================================================== -->

    <h2>Informasi Produk</h2>


    <table border="1" cellpadding="10">

        <tr>

            <th>
                Produk
            </th>

            <td>

                <?= htmlspecialchars(
                    $product['product_name']
                ); ?>

            </td>

        </tr>


        <tr>

            <th>
                Stok Saat Ini
            </th>

            <td>

                <?= $product['stock']; ?>

                <?= htmlspecialchars(
                    $product['unit']
                ); ?>

            </td>

        </tr>

    </table>


    <br><br>


    <!-- =========================================================
         FORM TAMBAH STOK
    ========================================================== -->

    <h2>Tambah Stok</h2>


    <form method="POST"><?= bekuku_csrf_field() ?>


        <label>
            Jumlah Stok yang Ditambahkan
        </label>


        <br>


        <input
            type="number"
            name="quantity"
            min="1"
            required
        >


        <?= htmlspecialchars(
            $product['unit']
        ); ?>


        <br><br>


        <button type="submit">
            Simpan Stok
        </button>


    </form>

    <?php require_once __DIR__ . "/../includes/footer.php"; ?>

</body>

</html>