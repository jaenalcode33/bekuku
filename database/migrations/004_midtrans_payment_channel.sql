-- Store the payment channel returned by Midtrans for Snap transactions.
ALTER TABLE transactions
    ADD COLUMN midtrans_payment_channel VARCHAR(100) DEFAULT NULL
    AFTER midtrans_order_id;
