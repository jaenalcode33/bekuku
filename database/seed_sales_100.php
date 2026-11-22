<?php

require_once __DIR__ . '/../config/database.php';

$count = 100;
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));

try {
    $dummyIds = $conn->query(
        "SELECT DISTINCT reference_id
         FROM stock_movements
         WHERE reference_type = 'transaction'
           AND note LIKE 'Penjualan data dummy #%'"
    )->fetchAll(PDO::FETCH_COLUMN);

    if ($dummyIds) {
        $placeholders = implode(',', array_fill(0, count($dummyIds), '?'));
        $deleteMovements = $conn->prepare(
            "DELETE FROM stock_movements
             WHERE reference_type = 'transaction'
               AND reference_id IN ($placeholders)"
        );
        $deleteMovements->execute($dummyIds);

        $deleteTransactions = $conn->prepare(
            "DELETE FROM transactions WHERE transaction_id IN ($placeholders)"
        );
        $deleteTransactions->execute($dummyIds);
    }

    $supplierId = $conn->query(
        'SELECT supplier_id FROM suppliers ORDER BY supplier_id LIMIT 1'
    )->fetchColumn();

    if (!$supplierId) {
        $conn->exec(
            "INSERT INTO suppliers (supplier_name, phone, address)
             VALUES ('Supplier Data Dummy', '0800000000', 'Data pengujian')"
        );
        $supplierId = (int) $conn->lastInsertId();
    }

    $conn->exec(
        'INSERT INTO purchases
            (purchase_date, supplier_id, invoice_number, total_amount,
             payment_amount, remaining_amount, status, note)
         VALUES
            (NOW(), ' . (int) $supplierId . ', "SEED-100-DUMMY",
             0, 0, 0, "lunas", "Persediaan untuk 100 data penjualan dummy")'
    );
    $seedPurchaseId = (int) $conn->lastInsertId();

    $customers = $conn->query(
        'SELECT customer_id, customer_name FROM customers ORDER BY customer_id'
    )->fetchAll(PDO::FETCH_ASSOC);

    $products = $conn->query(
        'SELECT product_id, product_name, selling_price, stock
         FROM products
         WHERE status = "aktif"
         ORDER BY product_id'
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$products) {
        throw new RuntimeException('Tidak ada produk aktif untuk membuat data penjualan.');
    }

    $insertPurchaseDetail = $conn->prepare(
        'INSERT INTO purchase_details
            (purchase_id, product_id, quantity, purchase_price, subtotal)
         VALUES (?, ?, ?, ?, 0)'
    );
    $insertSeedBatch = $conn->prepare(
        'INSERT INTO batches
            (purchase_detail_id, product_id, batch_number, expiry_date,
             quantity, remaining_quantity)
         VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 365 DAY), 1000, 1000)'
    );

    foreach ($products as $product) {
        $productId = (int) $product['product_id'];
        $remaining = (int) $conn->query(
            'SELECT COALESCE(SUM(remaining_quantity), 0)
             FROM batches WHERE product_id = ' . $productId
        )->fetchColumn();

        if ($remaining < 500) {
            $insertPurchaseDetail->execute([
                $seedPurchaseId,
                $productId,
                1000,
                (float) $product['selling_price'] * 0.7,
            ]);
            $detailId = (int) $conn->lastInsertId();
            $insertSeedBatch->execute([
                $detailId,
                $productId,
                'DUMMY-100-' . $productId,
            ]);
        }
    }

    $insertTransaction = $conn->prepare(
        'INSERT INTO transactions
            (transaction_date, customer_id, total_amount, payment_amount, change_amount,
             payment_method, midtrans_order_id, midtrans_payment_channel, status)
         VALUES
            (?, ?, ?, ?, ?, ?, NULL, NULL, "selesai")'
    );

    $insertDetail = $conn->prepare(
        'INSERT INTO transaction_details
            (transaction_id, product_id, quantity, price, subtotal)
         VALUES (?, ?, ?, ?, ?)'
    );

    $insertMovement = $conn->prepare(
        'INSERT INTO stock_movements
            (product_id, movement_type, quantity, reference_type, reference_id, note)
         VALUES (?, "keluar", ?, "transaction", ?, ?)'
    );

    $lockBatch = $conn->prepare(
        'SELECT batch_id, remaining_quantity
         FROM batches
         WHERE product_id = ? AND remaining_quantity > 0
         ORDER BY expiry_date ASC, batch_id ASC
         LIMIT 1
         FOR UPDATE'
    );

    $updateBatch = $conn->prepare(
        'UPDATE batches SET remaining_quantity = ? WHERE batch_id = ?'
    );

    $methods = ['cash', 'cash', 'cash', 'transfer', 'qris', 'qris_image'];
    $created = 0;

    for ($i = 1; $i <= $count; $i++) {
        $conn->beginTransaction();

        $dayOffset = (int) floor(($i - 1) / 4);
        $minuteOffset = (($i * 37) % 600);
        $transactionDate = $now
            ->modify('-' . $dayOffset . ' days')
            ->setTime(8 + (($i * 7) % 12), $minuteOffset % 60, 0)
            ->format('Y-m-d H:i:s');

        $customerId = null;
        if ($customers) {
            $customerId = (int) $customers[($i * 7) % count($customers)]['customer_id'];
        }

        $productCount = $i % 5 === 0 ? 2 : 1;
        $selected = [];
        $total = 0.0;

        for ($j = 0; $j < $productCount; $j++) {
            $index = ($i * 11 + $j * 9) % count($products);
            $productId = (int) $products[$index]['product_id'];
            if (isset($selected[$productId])) {
                continue;
            }

            $quantity = 1 + (($i + $j * 2) % 3);
            $price = (float) $products[$index]['selling_price'];
            $subtotal = $price * $quantity;
            $selected[$productId] = [
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        if (!$selected) {
            $conn->rollBack();
            continue;
        }

        $method = $methods[$i % count($methods)];
        $payment = $method === 'cash'
            ? (ceil($total / 10000) * 10000)
            : $total;
        $change = $payment - $total;

        $insertTransaction->execute([
            $transactionDate,
            $customerId,
            $total,
            $payment,
            $change,
            $method,
        ]);
        $transactionId = (int) $conn->lastInsertId();

        foreach ($selected as $productId => $detail) {
            $quantityLeft = $detail['quantity'];

            $insertDetail->execute([
                $transactionId,
                $productId,
                $detail['quantity'],
                $detail['price'],
                $detail['subtotal'],
            ]);

            while ($quantityLeft > 0) {
                $lockBatch->execute([$productId]);
                $batch = $lockBatch->fetch(PDO::FETCH_ASSOC);

                if (!$batch) {
                    throw new RuntimeException(
                        'Stok batch tidak mencukupi untuk produk ID ' . $productId
                        . '. Tambahkan pembelian/batch lalu jalankan seeder kembali.'
                    );
                }

                $used = min($quantityLeft, (int) $batch['remaining_quantity']);
                $updateBatch->execute([
                    (int) $batch['remaining_quantity'] - $used,
                    (int) $batch['batch_id'],
                ]);
                $quantityLeft -= $used;
            }

            $insertMovement->execute([
                $productId,
                $detail['quantity'],
                $transactionId,
                'Penjualan data dummy #' . $i,
            ]);

            $conn->prepare(
                'UPDATE products
                 SET stock = (
                     SELECT COALESCE(SUM(remaining_quantity), 0)
                     FROM batches
                     WHERE product_id = ?
                 )
                 WHERE product_id = ?'
            )->execute([$productId, $productId]);
        }

        $conn->commit();
        $created++;
    }

    echo "Berhasil membuat {$created} data penjualan dummy.\n";
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    fwrite(STDERR, 'Gagal membuat data penjualan: ' . $e->getMessage() . "\n");
    exit(1);
}
