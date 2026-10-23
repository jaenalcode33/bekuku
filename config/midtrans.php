<?php

require_once __DIR__ . '/app.php';

function bekuku_midtrans_config(): array
{
    $environment = strtolower(trim((string) getenv('MIDTRANS_ENVIRONMENT')));
    $environment = $environment !== '' ? $environment : 'sandbox';

    if (!in_array($environment, ['sandbox', 'production'], true)) {
        throw new InvalidArgumentException(
            'MIDTRANS_ENVIRONMENT harus bernilai sandbox atau production.'
        );
    }

    return [
        'environment' => $environment,
        'server_key' => trim((string) getenv('MIDTRANS_SERVER_KEY')),
        'client_key' => trim((string) getenv('MIDTRANS_CLIENT_KEY')),
        'base_url' => $environment === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com',
        'snap_js_url' => $environment === 'production'
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js',
        'snap_api_url' => $environment === 'production'
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com',
    ];
}

function bekuku_midtrans_api_request(
    string $method,
    string $path,
    ?array $payload = null,
    ?string $baseUrl = null
): array
{
    $config = bekuku_midtrans_config();
    if ($config['server_key'] === '') {
        throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
    }

    $headers = [
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($config['server_key'] . ':'),
    ];
    $options = [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ];

    if ($payload !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_THROW_ON_ERROR);
        $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
    }

    $curl = curl_init(($baseUrl ?? $config['base_url']) . $path);
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Gagal menghubungi Midtrans: ' . $curlError);
    }

    $result = json_decode($response, true);
    if (!is_array($result) || $statusCode < 200 || $statusCode >= 300) {
        bekuku_log('MIDTRANS API ERROR', [
            'environment' => $config['environment'],
            'status_code' => $statusCode,
            'response' => $response,
            'path' => $path,
        ]);

        if ($statusCode === 401) {
            throw new RuntimeException(
                'Midtrans menolak kredensial (HTTP 401). Pastikan MIDTRANS_SERVER_KEY '
                . 'berasal dari dashboard ' . ucfirst($config['environment'])
                . ' yang sama dengan MIDTRANS_ENVIRONMENT.'
            );
        }

        $message = is_array($result)
            ? (string) ($result['status_message'] ?? 'Respons Midtrans tidak valid.')
            : 'Respons Midtrans tidak valid.';
        throw new RuntimeException('Midtrans Error HTTP ' . $statusCode . ': ' . $message);
    }

    return $result;
}

function bekuku_midtrans_create_snap(int $amount, array $items): array
{
    $orderId = 'BEKUKU-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
    $config = bekuku_midtrans_config();
    $result = bekuku_midtrans_api_request('POST', '/snap/v1/transactions', [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => $amount,
        ],
        'item_details' => $items,
    ], $config['snap_api_url']);

    if (empty($result['token'])) {
        throw new RuntimeException('Midtrans tidak mengembalikan token Snap Checkout.');
    }

    return [
        'order_id' => $orderId,
        'token' => (string) $result['token'],
    ];
}

function bekuku_midtrans_get_status(string $orderId): array
{
    if (!preg_match('/^BEKUKU-[0-9]{14}-[a-f0-9]{8}$/', $orderId)) {
        throw new InvalidArgumentException('ID pesanan Midtrans tidak valid.');
    }

    return bekuku_midtrans_api_request(
        'GET',
        '/v2/' . rawurlencode($orderId) . '/status'
    );
}

function bekuku_midtrans_payment_channel(array $status): string
{
    $paymentType = strtolower(trim((string) ($status['payment_type'] ?? '')));
    $providerLabels = [
        'bca' => 'BCA',
        'bni' => 'BNI',
        'bri' => 'BRI',
        'cimb' => 'CIMB',
        'danamon' => 'Danamon',
        'mandiri' => 'Mandiri',
        'permata' => 'Permata',
        'gopay' => 'GoPay',
        'dana' => 'DANA',
        'shopeepay' => 'ShopeePay',
        'ovo' => 'OVO',
        'linkaja' => 'LinkAja',
        'akulaku' => 'Akulaku',
        'kredivo' => 'Kredivo',
    ];
    $getProviderLabel = static function (string $provider) use ($providerLabels): string {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $provider));
        return $providerLabels[$normalized] ?? '';
    };

    if ($paymentType === 'qris') {
        $provider = $getProviderLabel((string) ($status['issuer'] ?? ''))
            ?: $getProviderLabel((string) ($status['acquirer'] ?? ''));
        return 'QRIS' . ($provider !== '' ? ' ' . $provider : '');
    }

    if ($paymentType === 'bank_transfer') {
        $bank = (string) ($status['va_numbers'][0]['bank'] ?? '');
        $bankLabel = $getProviderLabel($bank);
        return $bankLabel !== '' ? 'VA ' . $bankLabel : 'Virtual Account';
    }

    if ($paymentType === 'permata') {
        return 'VA Permata';
    }

    if ($paymentType === 'echannel') {
        return 'Mandiri Bill';
    }

    if ($paymentType === 'credit_card') {
        return 'Kartu Kredit';
    }

    if ($paymentType === 'gopay') {
        return 'GoPay';
    }

    if ($paymentType === 'shopeepay') {
        return 'ShopeePay';
    }

    if ($paymentType === 'cstore') {
        $store = strtolower((string) ($status['store'] ?? ''));
        return match ($store) {
            'alfamart' => 'Alfamart',
            'indomaret' => 'Indomaret',
            default => 'Convenience Store',
        };
    }

    return $paymentType !== ''
        ? strtoupper(str_replace('_', ' ', $paymentType))
        : 'Midtrans Snap';
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
            'environment' => $config['environment'],
            'status_code' => $statusCode,
            'response' => $response,
            'base_url' => $config['base_url'],
        ]);

        if ($statusCode === 401) {
            throw new RuntimeException(
                'Midtrans menolak kredensial (HTTP 401). Pastikan MIDTRANS_SERVER_KEY '
                . 'berasal dari dashboard ' . ucfirst($config['environment'])
                . ' yang sama dengan MIDTRANS_ENVIRONMENT. Client Key tidak dapat '
                . 'digunakan untuk request QRIS backend.'
            );
        }

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
        $statusMessage = (string) ($result['status_message'] ?? '');
        if (stripos($statusMessage, 'payment channel is not activated') !== false) {
            bekuku_log('Midtrans QRIS payment channel is not activated', [
                'environment' => $config['environment'],
                'status_code' => $statusCode,
            ]);
            throw new RuntimeException(
                'QRIS belum diaktifkan pada akun Midtrans '
                . ucfirst($config['environment'])
                . '. Pastikan metode QRIS diaktifkan di environment dashboard yang sama, '
                . 'lalu coba kembali.'
            );
        }

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
            . '. Periksa log aplikasi untuk detail.'
        );
    }

    return [
        'order_id' => $orderId,
        'qr_code_url' => $qrCodeUrl,
        'transaction_status' => (string) ($result['transaction_status'] ?? 'pending'),
    ];
}
