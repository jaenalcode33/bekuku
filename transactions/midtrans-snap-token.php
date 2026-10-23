<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/midtrans.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Endpoint Snap hanya menerima metode POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $config = bekuku_midtrans_config();
    if ($config['client_key'] === '') {
        throw new RuntimeException('MIDTRANS_CLIENT_KEY belum dikonfigurasi.');
    }

    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    if (!is_array($productIds) || !is_array($quantities)) {
        throw new InvalidArgumentException('Daftar produk tidak valid.');
    }

    $requestedProducts = [];
    foreach ($productIds as $index => $rawProductId) {
        $productId = bekuku_validate_int($rawProductId, 'Produk', 1);
        $quantity = bekuku_validate_int($quantities[$index] ?? null, 'Jumlah produk', 1);
        $requestedProducts[$productId] = ($requestedProducts[$productId] ?? 0) + $quantity;
    }

    if ($requestedProducts === []) {
        throw new InvalidArgumentException('Pilih minimal satu produk.');
    }

    $customerId = !empty($_POST['customer_id'])
        ? bekuku_validate_int($_POST['customer_id'], 'Customer', 1)
        : null;
    if ($customerId !== null) {
        $customerStmt = $conn->prepare(
            'SELECT customer_id FROM customers WHERE customer_id = :customer_id'
        );
        $customerStmt->execute([':customer_id' => $customerId]);
        if (!$customerStmt->fetchColumn()) {
            throw new InvalidArgumentException('Customer yang dipilih tidak ditemukan.');
        }
    }

    $totalAmount = 0;
    $items = [];
    $snapPrices = [];
    foreach ($requestedProducts as $productId => $quantity) {
        $stmt = $conn->prepare(
            "SELECT product_id, product_name, selling_price, stock
             FROM products
             WHERE product_id = :product_id AND status = 'aktif'"
        );
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new InvalidArgumentException('Produk yang dipilih tidak tersedia.');
        }
        if ($quantity > (int) $product['stock']) {
            throw new InvalidArgumentException(
                'Stok produk "' . $product['product_name'] . '" tidak mencukupi.'
            );
        }

        $price = (int) round((float) $product['selling_price']);
        $totalAmount += $price * $quantity;
        $snapPrices[$productId] = $price;
        $items[] = [
            'id' => (string) $product['product_id'],
            'price' => $price,
            'quantity' => $quantity,
            'name' => mb_substr((string) $product['product_name'], 0, 50),
        ];
    }

    if ($totalAmount <= 0) {
        throw new InvalidArgumentException('Total transaksi harus lebih dari Rp 0.');
    }

    $snap = bekuku_midtrans_create_snap($totalAmount, $items);
    $_SESSION['bekuku_midtrans_snap_orders'][$snap['order_id']] = [
        'amount' => $totalAmount,
        'items' => $requestedProducts,
        'prices' => $snapPrices,
        'customer_id' => $customerId,
        'created_at' => time(),
    ];

    echo json_encode([
        'order_id' => $snap['order_id'],
        'token' => $snap['token'],
        'amount' => $totalAmount,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    http_response_code(422);
    echo json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
}
