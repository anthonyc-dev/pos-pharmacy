<?php
require_once __DIR__ . '/../db.php';

// SQL to create product_details table similar to products table
$sql = "CREATE TABLE IF NOT EXISTS product_details (
  id INT(11) NOT NULL AUTO_INCREMENT,
  category_id INT(11) DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock INT(11) DEFAULT 0,
  expiration VARCHAR(250) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY category_id (category_id),
  CONSTRAINT product_details_ibfk_1 FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'product_details' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
