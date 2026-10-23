-- Add support for static QRIS image payments.
ALTER TABLE transactions
    MODIFY payment_method
    ENUM('cash', 'transfer', 'qris', 'qris_image')
    NOT NULL DEFAULT 'cash';
