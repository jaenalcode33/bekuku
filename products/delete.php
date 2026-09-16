<?php

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

require_once "../config/database.php";

$product_id = (int) ($_POST["id"] ?? 0);

if ($product_id <= 0) {
    die("ID produk tidak valid.");
}

/*
|--------------------------------------------------------------------------
| Cek apakah produk ada
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT product_id, product_name, stock
    FROM products
    WHERE product_id = :product_id
");

$stmt->execute([
    ":product_id" => $product_id
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produk tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| Hapus produk
|--------------------------------------------------------------------------
*/
try {

    $conn->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Hapus riwayat stok terlebih dahulu
    |--------------------------------------------------------------------------
    */
    $stmtMovement = $conn->prepare("
        DELETE FROM stock_movements
        WHERE product_id = :product_id
    ");

    $stmtMovement->execute([
        ":product_id" => $product_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Hapus produk
    |--------------------------------------------------------------------------
    */
    $stmtDelete = $conn->prepare("
        DELETE FROM products
        WHERE product_id = :product_id
    ");

    $stmtDelete->execute([
        ":product_id" => $product_id
    ]);


    $conn->commit();
    bekuku_audit('delete', 'product', $product_id);


    /*
    |--------------------------------------------------------------------------
    | Kembali ke daftar produk
    |--------------------------------------------------------------------------
    */
    header("Location: index.php");

    exit;


} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die(
        "Produk tidak dapat dihapus. " .
        "Kemungkinan produk sudah digunakan dalam transaksi. " .
        "<br><br>" .
        "Detail: " .
        htmlspecialchars($e->getMessage())
    );
}