
CREATE DATABASE IF NOT EXISTS `bekuku`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bekuku`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','kasir','gudang') NOT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `audit_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `entity` VARCHAR(100) NOT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_created_at` (`created_at`),
  KEY `idx_audit_entity` (`entity`, `entity_id`),
  KEY `idx_audit_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`username`, `name`, `password_hash`, `role`) VALUES
  ('admin', 'Administrator', '$2y$10$3Og0vzp3ijUYP/dQfJb/y.qupuRY5JvQPNdUcbCYtZCvX7Hus96nu', 'admin'),
  ('kasir', 'Kasir', '$2y$10$5pSicgLKcqaezAjRQigjPOIlzJAV8sutgoInomC1RGamUUzorhzMW', 'kasir'),
  ('gudang', 'Petugas Gudang', '$2y$10$46LsHCZnK28bkzs2l4iaVOrOjKQCyLFeO0CgFKSbFcwVCZaIjSbGa', 'gudang');

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  PRIMARY KEY (`supplier_id`),
  KEY `idx_suppliers_name` (`supplier_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `idx_customers_name` (`customer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED DEFAULT NULL,
  `sku` VARCHAR(50) DEFAULT NULL,
  `barcode` VARCHAR(50) DEFAULT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
  `stock` INT NOT NULL DEFAULT 0,
  `min_stock` INT NOT NULL DEFAULT 5,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `uq_products_sku` (`sku`),
  UNIQUE KEY `uq_products_barcode` (`barcode`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_supplier` (`supplier_id`),
  KEY `idx_products_name` (`product_name`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_products_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` ENUM('cash','transfer','midtrans_snap','qris','qris_image') NOT NULL DEFAULT 'cash',
  `midtrans_order_id` VARCHAR(50) DEFAULT NULL,
  `midtrans_payment_channel` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('selesai','batal') NOT NULL DEFAULT 'selesai',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `idx_transactions_date` (`transaction_date`),
  KEY `idx_transactions_customer` (`customer_id`),
  KEY `idx_transactions_status` (`status`),
  UNIQUE KEY `uq_transactions_midtrans_order_id` (`midtrans_order_id`),
  CONSTRAINT `fk_transactions_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_details` (
  `transaction_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`transaction_id`, `product_id`),
  KEY `idx_transaction_details_product` (`product_id`),
  CONSTRAINT `fk_transaction_details_transaction`
    FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaction_details_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_movements` (
  `movement_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `movement_type` ENUM('masuk','keluar') NOT NULL,
  `quantity` INT NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`movement_id`),
  KEY `idx_stock_movements_product` (`product_id`),
  KEY `idx_stock_movements_type` (`movement_type`),
  KEY `idx_stock_movements_reference` (`reference_type`, `reference_id`),
  CONSTRAINT `fk_stock_movements_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchases` (
  `purchase_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` INT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(100) DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('lunas','hutang') NOT NULL DEFAULT 'lunas',
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purchase_id`),
  KEY `idx_purchases_date` (`purchase_date`),
  KEY `idx_purchases_supplier` (`supplier_id`),
  KEY `idx_purchases_status` (`status`),
  CONSTRAINT `fk_purchases_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_details` (
  `purchase_detail_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL,
  `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`purchase_detail_id`),
  KEY `idx_purchase_details_purchase` (`purchase_id`),
  KEY `idx_purchase_details_product` (`product_id`),
  CONSTRAINT `fk_purchase_details_purchase`
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_purchase_details_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `batches` (
  `batch_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_detail_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_number` VARCHAR(100) DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `quantity` INT NOT NULL,
  `remaining_quantity` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`batch_id`),
  KEY `idx_batches_purchase_detail` (`purchase_detail_id`),
  KEY `idx_batches_product_expiry` (`product_id`, `expiry_date`),
  CONSTRAINT `fk_batches_purchase_detail`
    FOREIGN KEY (`purchase_detail_id`) REFERENCES `purchase_details` (`purchase_detail_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_batches_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `products` p
LEFT JOIN (
  SELECT `product_id`, COALESCE(SUM(`remaining_quantity`), 0) AS `batch_stock`
  FROM `batches`
  GROUP BY `product_id`
) b ON b.`product_id` = p.`product_id`
SET p.`stock` = COALESCE(b.`batch_stock`, 0);

INSERT IGNORE INTO `categories` (`name`) VALUES
  ('Nugget ayam'),
  ('Sosis'),
  ('Dimsum'),
  ('Bakso'),
  ('Seafood'),
  ('Kentang');

-- Data dummy master dan stok awal. SKU dan nomor invoice tetap agar aman diimpor ulang.
INSERT INTO `suppliers` (`supplier_name`, `phone`, `address`)
SELECT seed.`supplier_name`, seed.`phone`, seed.`address`
FROM (
  SELECT 'PT Frozen Sejahtera' AS `supplier_name`, '081200000101' AS `phone`,
         'Jakarta' AS `address`
  UNION ALL
  SELECT 'CV Pangan Beku', '081200000102', 'Bandung'
) AS seed
WHERE NOT EXISTS (
  SELECT 1 FROM `suppliers` s WHERE s.`supplier_name` = seed.`supplier_name`
);

INSERT INTO `customers` (`customer_name`, `phone`, `address`)
SELECT seed.`customer_name`, seed.`phone`, seed.`address`
FROM (
  SELECT 'Pelanggan Umum 1' AS `customer_name`, '082100000101' AS `phone`,
         'Jakarta' AS `address`
  UNION ALL
  SELECT 'Pelanggan Umum 2', '082100000102', 'Depok'
  UNION ALL
  SELECT 'Pelanggan Umum 3', '082100000103', 'Bekasi'
) AS seed
WHERE NOT EXISTS (
  SELECT 1 FROM `customers` c WHERE c.`customer_name` = seed.`customer_name`
);

INSERT IGNORE INTO `products`
  (`category_id`, `supplier_id`, `sku`, `barcode`, `product_name`,
   `purchase_price`, `selling_price`, `unit`, `stock`, `min_stock`)
SELECT c.`category_id`, s.`supplier_id`, seed.`sku`, seed.`barcode`,
       seed.`product_name`, seed.`purchase_price`, seed.`selling_price`,
       'pack', 0, 5
FROM (
  SELECT 'Nugget ayam' AS `category_name`, 'NGT-001' AS `sku`,
         '8991000000011' AS `barcode`, 'Nugget Ayam 500g' AS `product_name`,
         28000.00 AS `purchase_price`, 35000.00 AS `selling_price`,
         'PT Frozen Sejahtera' AS `supplier_name`
  UNION ALL
  SELECT 'Sosis', 'SOS-001', '8991000000028', 'Sosis Sapi 500g',
         25000.00, 32000.00, 'PT Frozen Sejahtera'
  UNION ALL
  SELECT 'Dimsum', 'DIM-001', '8991000000035', 'Dimsum Ayam 10 pcs',
         30000.00, 38000.00, 'CV Pangan Beku'
  UNION ALL
  SELECT 'Bakso', 'BAK-001', '8991000000042', 'Bakso Sapi 500g',
         27000.00, 34000.00, 'CV Pangan Beku'
  UNION ALL
  SELECT 'Seafood', 'SEA-001', '8991000000059', 'Crab Stick 250g',
         18000.00, 24000.00, 'PT Frozen Sejahtera'
  UNION ALL
  SELECT 'Kentang', 'KNT-001', '8991000000066', 'Kentang Goreng 1kg',
         32000.00, 40000.00, 'CV Pangan Beku'
) AS seed
INNER JOIN `categories` c ON c.`name` = seed.`category_name`
INNER JOIN `suppliers` s ON s.`supplier_name` = seed.`supplier_name`;

INSERT INTO `purchases`
  (`purchase_date`, `supplier_id`, `invoice_number`, `total_amount`,
   `payment_amount`, `remaining_amount`, `status`, `note`)
SELECT NOW(), s.`supplier_id`,
       CASE s.`supplier_name`
         WHEN 'PT Frozen Sejahtera' THEN 'DUMMY-STOK-AWAL-001'
         ELSE 'DUMMY-STOK-AWAL-002'
       END,
       0, 0, 0, 'lunas', 'Pembelian stok awal data dummy'
FROM `suppliers` s
WHERE s.`supplier_name` IN ('PT Frozen Sejahtera', 'CV Pangan Beku')
  AND NOT EXISTS (
    SELECT 1 FROM `purchases` p
    WHERE p.`invoice_number` = CASE s.`supplier_name`
      WHEN 'PT Frozen Sejahtera' THEN 'DUMMY-STOK-AWAL-001'
      ELSE 'DUMMY-STOK-AWAL-002'
    END
  );

INSERT INTO `purchase_details`
  (`purchase_id`, `product_id`, `quantity`, `purchase_price`, `subtotal`)
SELECT p.`purchase_id`, pr.`product_id`, 20, pr.`purchase_price`,
       20 * pr.`purchase_price`
FROM `purchases` p
INNER JOIN `products` pr ON pr.`sku` IN
  ('NGT-001', 'SOS-001', 'DIM-001', 'BAK-001', 'SEA-001', 'KNT-001')
  AND pr.`supplier_id` = p.`supplier_id`
WHERE p.`invoice_number` IN
  ('DUMMY-STOK-AWAL-001', 'DUMMY-STOK-AWAL-002')
  AND NOT EXISTS (
    SELECT 1 FROM `purchase_details` pd
    WHERE pd.`purchase_id` = p.`purchase_id`
      AND pd.`product_id` = pr.`product_id`
  );

INSERT INTO `batches`
  (`purchase_detail_id`, `product_id`, `batch_number`, `expiry_date`,
   `quantity`, `remaining_quantity`)
SELECT pd.`purchase_detail_id`, pd.`product_id`,
       CONCAT('DUMMY-', pr.`sku`),
       DATE_ADD(CURDATE(), INTERVAL 365 DAY),
       pd.`quantity`, pd.`quantity`
FROM `purchase_details` pd
INNER JOIN `purchases` p ON p.`purchase_id` = pd.`purchase_id`
INNER JOIN `products` pr ON pr.`product_id` = pd.`product_id`
WHERE p.`invoice_number` IN
  ('DUMMY-STOK-AWAL-001', 'DUMMY-STOK-AWAL-002')
  AND NOT EXISTS (
    SELECT 1 FROM `batches` b
    WHERE b.`purchase_detail_id` = pd.`purchase_detail_id`
  );

UPDATE `purchases` p
INNER JOIN (
  SELECT `purchase_id`, SUM(`subtotal`) AS `total_amount`
  FROM `purchase_details`
  GROUP BY `purchase_id`
) totals ON totals.`purchase_id` = p.`purchase_id`
SET p.`total_amount` = totals.`total_amount`,
    p.`payment_amount` = totals.`total_amount`,
    p.`remaining_amount` = 0
WHERE p.`invoice_number` IN
  ('DUMMY-STOK-AWAL-001', 'DUMMY-STOK-AWAL-002');

UPDATE `products` pr
LEFT JOIN (
  SELECT b.`product_id`, SUM(b.`remaining_quantity`) AS `batch_stock`
  FROM `batches` b
  GROUP BY b.`product_id`
) totals ON totals.`product_id` = pr.`product_id`
SET pr.`stock` = COALESCE(totals.`batch_stock`, 0)
WHERE pr.`sku` IN
  ('NGT-001', 'SOS-001', 'DIM-001', 'BAK-001', 'SEA-001', 'KNT-001');

INSERT INTO `stock_movements`
  (`product_id`, `movement_type`, `quantity`, `reference_type`,
   `reference_id`, `note`)
SELECT pd.`product_id`, 'masuk', pd.`quantity`, 'purchase', p.`purchase_id`,
       'Stok awal data dummy'
FROM `purchase_details` pd
INNER JOIN `purchases` p ON p.`purchase_id` = pd.`purchase_id`
WHERE p.`invoice_number` IN
  ('DUMMY-STOK-AWAL-001', 'DUMMY-STOK-AWAL-002')
  AND NOT EXISTS (
    SELECT 1 FROM `stock_movements` sm
    WHERE sm.`reference_type` = 'purchase'
      AND sm.`reference_id` = p.`purchase_id`
      AND sm.`product_id` = pd.`product_id`
      AND sm.`note` = 'Stok awal data dummy'
  );

SET FOREIGN_KEY_CHECKS = 1;