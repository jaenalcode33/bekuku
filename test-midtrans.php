<?php

require_once __DIR__ . '/config/midtrans.php';

try {

    $result = bekuku_midtrans_create_qris(10000);

    echo '<h2>QRIS berhasil dibuat</h2>';

    echo '<pre>';
    print_r($result);
    echo '</pre>';

    echo '<p>QRIS:</p>';

    echo '<img 
        src="' . htmlspecialchars($result['qr_code_url']) . '" 
        style="width:300px;"
    >';

} catch (Throwable $e) {

    echo '<h2>ERROR</h2>';

    echo '<pre>';
    echo htmlspecialchars($e->getMessage());
    echo '</pre>';
}

