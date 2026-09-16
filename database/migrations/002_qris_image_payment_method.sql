-- Add support for static QRIS image payments.
ALTER TABLE transactions
    MODIFY payment_method
    ENUM('cash', 'transfer', 'qris', 'qris_image')
    NOT NULL DEFAULT 'cash';

UPDATE products p
LEFT JOIN (
    SELECT product_id, COALESCE(SUM(remaining_quantity), 0) AS batch_stock
    FROM batches
    GROUP BY product_id
) b ON b.product_id = p.product_id
SET p.stock = COALESCE(b.batch_stock, 0);
