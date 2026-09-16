<?php

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/database.php";

$transaction_id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($transaction_id <= 0) {
    die("ID transaksi tidak valid.");
}

try {
    $stmt = $conn->prepare("
        DELETE FROM transactions
        WHERE transaction_id = :transaction_id
    ");

    $stmt->execute([
        ':transaction_id' => $transaction_id,
    ]);

    bekuku_audit('delete', 'transaction', $transaction_id);

    header('Location: ' . bekuku_url('transactions/'));
    exit;
} catch (PDOException $e) {
    die("Transaksi gagal dihapus: " . $e->getMessage());
}