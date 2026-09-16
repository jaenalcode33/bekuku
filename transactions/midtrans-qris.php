<?php

require_once __DIR__ . '/../config/midtrans.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metode tidak diizinkan.']);
    exit;
}

try {
    bekuku_verify_csrf();
    $amount = bekuku_validate_int($_POST['amount'] ?? null, 'Nominal QRIS', 1);
    echo json_encode(bekuku_midtrans_create_qris($amount));
} catch (Throwable $exception) {
    http_response_code(422);
    echo json_encode(['error' => $exception->getMessage()]);
}
