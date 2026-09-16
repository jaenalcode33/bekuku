<?php

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

require_once "../config/database.php";


$customer_id = (int) ($_POST["id"] ?? 0);


if ($customer_id <= 0) {

    die("ID customer tidak valid.");

}


try {


    // Cek apakah customer masih digunakan transaksi

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM transactions
        WHERE customer_id = :customer_id
    ");

    $stmt->execute([
        ":customer_id" => $customer_id
    ]);


    $totalTransactions = (int) $stmt->fetchColumn();


    if ($totalTransactions > 0) {

        die(
            "Customer tidak dapat dihapus karena sudah digunakan oleh "
            . $totalTransactions
            . " transaksi."
        );

    }


    // Hapus customer

    $stmt = $conn->prepare("
        DELETE FROM customers
        WHERE customer_id = :customer_id
    ");

    $stmt->execute([
        ":customer_id" => $customer_id
    ]);

    bekuku_audit('delete', 'customer', $customer_id);

    header("Location: index.php");
    exit;


} catch (PDOException $e) {

    die(
        "Customer gagal dihapus. "
        . "Kemungkinan customer masih digunakan oleh data lain."
    );

}