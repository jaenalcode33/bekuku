-- Add support for Midtrans Snap Checkout payments.
ALTER TABLE transactions
    MODIFY payment_method
    ENUM('cash', 'transfer', 'midtrans_snap', 'qris', 'qris_image')
    NOT NULL DEFAULT 'cash',
    ADD COLUMN midtrans_order_id VARCHAR(50) DEFAULT NULL AFTER payment_method,
    ADD UNIQUE KEY uq_transactions_midtrans_order_id (midtrans_order_id);
