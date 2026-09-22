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

    bekuku_log('MIDTRANS ERROR', [
        'status_code' => $statusCode,
        'response' => $response,
        'server_key_prefix' => substr($config['server_key'], 0, 12),
        'base_url' => $config['base_url'],
    ]);

    $message = is_array($result)
        ? (string) ($result['status_message'] ?? 'Respons Midtrans tidak valid.')
        : 'Respons Midtrans tidak valid.';

    throw new RuntimeException(
        'Midtrans Error HTTP ' . $statusCode . ': ' . $message
    );
}
    $qrCodeUrl = '';
    foreach (['generate-qr-code-v2', 'generate-qr-code'] as $actionName) {
        foreach (($result['actions'] ?? []) as $action) {
            if (($action['name'] ?? '') === $actionName && !empty($action['url'])) {
                $qrCodeUrl = (string) $action['url'];
                break 2;
            }
        }
    }

    if ($qrCodeUrl === '') {
        foreach (($result['actions'] ?? []) as $action) {
            if (!empty($action['url'])) {
                $qrCodeUrl = (string) $action['url'];
                break;
            }
        }
    }

    if ($qrCodeUrl === '') {
        $actionNames = [];
        foreach (($result['actions'] ?? []) as $action) {
            if (is_array($action) && isset($action['name'])) {
                $actionNames[] = (string) $action['name'];
            }
        }

        $details = [
            'status_code' => $statusCode,
            'status_message' => (string) ($result['status_message'] ?? ''),
            'response_keys' => implode(', ', array_keys($result)),
            'action_names' => implode(', ', $actionNames),
        ];
        bekuku_log('Midtrans QRIS response did not include a QR URL', $details);
        throw new RuntimeException(
            'Midtrans tidak mengembalikan URL QRIS. '
            . 'Status: ' . ($details['status_message'] !== '' ? $details['status_message'] : 'tidak diketahui')
            . '. Periksa storage/logs/app.log.'
        );
    }

    return [
        'order_id' => $orderId,
        'qr_code_url' => $qrCodeUrl,
        'transaction_status' => (string) ($result['transaction_status'] ?? 'pending'),
    ];
}


