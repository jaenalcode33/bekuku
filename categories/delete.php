<?php

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

require_once "../config/database.php";

$category_id = (int) ($_POST["id"] ?? 0);

if ($category_id <= 0) {
    die("ID kategori tidak valid.");
}


try {

    // Cek apakah kategori masih digunakan produk
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM products
        WHERE category_id = :category_id
    ");

    $stmt->execute([
        ":category_id" => $category_id
    ]);

    $totalProducts = (int) $stmt->fetchColumn();


    if ($totalProducts > 0) {

        die(
            "Kategori tidak dapat dihapus karena masih digunakan oleh "
            . $totalProducts
            . " produk. Silakan ubah kategori produk tersebut terlebih dahulu."
        );
    }


    // Hapus kategori
    $stmt = $conn->prepare("
        DELETE FROM categories
        WHERE category_id = :category_id
    ");

    $stmt->execute([
        ":category_id" => $category_id
    ]);

    bekuku_audit('delete', 'category', $category_id);

    header("Location: index.php");
    exit;


} catch (PDOException $e) {

    die(
        "Kategori gagal dihapus. "
        . "Kemungkinan kategori masih digunakan oleh data lain."
    );
}