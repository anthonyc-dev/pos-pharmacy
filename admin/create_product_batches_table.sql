CREATE TABLE `product_batches` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `batch_stock` INT(11) NOT NULL DEFAULT 0,
  `batch_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `batch_expiration` DATE DEFAULT NULL,
  `batch_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
