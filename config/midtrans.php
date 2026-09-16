<?php

require_once __DIR__ . '/app.php';

function bekuku_midtrans_config(): array
{
    $environment = strtolower(trim((string) getenv('MIDTRANS_ENVIRONMENT')));
    $environment = in_array($environment, ['sandbox', 'production'], true)
        ? $environment
        : 'sandbox';

    return [
        'server_key' => trim((string) getenv('MIDTRANS_SERVER_KEY')),
        'base_url' => $environment === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com',
    ];
}

function bekuku_midtrans_create_qris(int $amount): array
{
    $config = bekuku_midtrans_config();

    if ($config['server_key'] === '') {
        throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
    }

    $orderId = 'BEKUKU-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
    $payload = json_encode([
        'payment_type' => 'qris',
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => $amount,
        ],
        'qris' => [
            'acquirer' => 'gopay',
        ],
    ], JSON_THROW_ON_ERROR);

    $curl = curl_init($config['base_url'] . '/v2/charge');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($config['server_key'] . ':'),
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Gagal menghubungi Midtrans: ' . $curlError);
    }

    $result = json_decode($response, true);
    if (!is_array($result) || $statusCode < 200 || $statusCode >= 300) {
        $message = is_array($result)
            ? (string) ($result['status_message'] ?? 'Respons Midtrans tidak valid.')
            : 'Respons Midtrans tidak valid.';
        throw new RuntimeException($message);
    }

    $qrCodeUrl = (string) ($result['actions'][0]['url'] ?? '');
    if ($qrCodeUrl === '') {
        throw new RuntimeException('Midtrans tidak mengembalikan URL QRIS.');
    }

    return [
        'order_id' => $orderId,
        'qr_code_url' => $qrCodeUrl,
        'transaction_status' => (string) ($result['transaction_status'] ?? 'pending'),
    ];
}
