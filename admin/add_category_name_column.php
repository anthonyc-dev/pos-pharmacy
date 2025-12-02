<?php
require_once __DIR__ . '/../db.php';

$sql = "ALTER TABLE product_details ADD COLUMN category_name VARCHAR(255) DEFAULT NULL AFTER category_id";

if ($conn->query($sql) === TRUE) {
    echo "Column category_name added successfully.";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
