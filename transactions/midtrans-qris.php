<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/midtrans.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Endpoint QRIS hanya menerima metode POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $amount = bekuku_validate_int($_POST['amount'] ?? null, 'Nominal QRIS', 1);
    echo json_encode(bekuku_midtrans_create_qris($amount), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    http_response_code(422);
    echo json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}

