<?php

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

require_once "../config/database.php";


$supplier_id = (int) ($_POST["id"] ?? 0);


if ($supplier_id <= 0) {

    die("ID supplier tidak valid.");

}


try {


    // Cek apakah supplier masih digunakan oleh produk

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM products
        WHERE supplier_id = :supplier_id
    ");

    $stmt->execute([
        ":supplier_id" => $supplier_id
    ]);


    $totalProducts = (int) $stmt->fetchColumn();


    if ($totalProducts > 0) {

        die(
            "Supplier tidak dapat dihapus karena masih digunakan oleh "
            . $totalProducts
            . " produk. Silakan ubah supplier produk tersebut terlebih dahulu."
        );

    }


    // Hapus supplier

    $stmt = $conn->prepare("
        DELETE FROM suppliers
        WHERE supplier_id = :supplier_id
    ");

    $stmt->execute([
        ":supplier_id" => $supplier_id
    ]);

    bekuku_audit('delete', 'supplier', $supplier_id);

    header("Location: index.php");
    exit;


} catch (PDOException $e) {


    die(
        "Supplier gagal dihapus. "
        . "Kemungkinan supplier masih digunakan oleh data lain."
    );

}