<?php
// admin/product_save.php
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Collect POST data
$id         = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name       = trim($_POST['name']);
$barcode    = trim($_POST['barcode']);
$category   = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
$medicine_type = isset($_POST['medicine_type']) ? trim($_POST['medicine_type']) : null;
$price      = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$stock      = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$mg         = trim($_POST['mg']);
$expiration = trim($_POST['expiration']);

// Fix expiration handling
if ($expiration === "" || $expiration === "0000-00-00") {
    $expiration = NULL; // Save as NULL (good practice)
}

// CHECK FOR DUPLICATE BARCODE
if (!empty($barcode)) {
    if ($id > 0) { 
        $stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
        $stmt->bind_param("si", $barcode, $id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
        $stmt->bind_param("s", $barcode);
    }
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['flash_duplicate_barcode'] = "The barcode '$barcode' is already in use.";
        header("Location: product_form.php?id=$id");
        exit;
    }
}

// For existing product (restocking), update stock
if ($id > 0) {
    $stmt = $conn->prepare("
        UPDATE products SET stock = stock + ?, price = ?, expiration = ? WHERE id = ?
    ");
    $stmt->bind_param(
        "idsi",
        $stock,
        $price,
        $expiration,
        $id
    );
    $stmt->execute();
}
// INSERT new product
else {
    // Insert product with stock from form
    $stmt = $conn->prepare("
        INSERT INTO products (name, barcode, category_id, medicine_type, price, stock, mg, expiration)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssisdiss",
        $name,
        $barcode,
        $category,
        $medicine_type,
        $price,
        $stock,
        $mg,
        $expiration
    );
    $stmt->execute();
}

header("Location: products.php");
exit;

