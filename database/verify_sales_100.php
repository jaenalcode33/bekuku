<?php

require_once __DIR__ . '/../config/database.php';

$count = (int) $conn->query(
    "SELECT COUNT(*)
     FROM transactions
     WHERE transaction_id IN (
         SELECT DISTINCT reference_id
         FROM stock_movements
         WHERE reference_type = 'transaction'
           AND note LIKE 'Penjualan data dummy #%'
     )"
)->fetchColumn();

$detailCount = (int) $conn->query(
    "SELECT COUNT(*)
     FROM transaction_details td
     INNER JOIN transactions t ON t.transaction_id = td.transaction_id
     WHERE t.transaction_id IN (
         SELECT DISTINCT reference_id
         FROM stock_movements
         WHERE reference_type = 'transaction'
           AND note LIKE 'Penjualan data dummy #%'
     )"
)->fetchColumn();

echo "Dummy transactions: {$count}\n";
echo "Transaction detail rows: {$detailCount}\n";
