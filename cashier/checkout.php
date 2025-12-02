<?php
// cashier/checkout.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Cart is empty. Cannot checkout.";
    exit;
}

// Get cash given from form
$cash_given = isset($_POST['cash']) ? (float)$_POST['cash'] : 0;
if ($cash_given <= 0) {
    echo "Invalid cash amount.";
    exit;
}

// Calculate total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}

if ($cash_given < $grand_total) {
    echo "Cash given is less than total. Please provide enough cash.";
    exit;
}

// --- Start transaction ---
$conn->begin_transaction();

try {
    // Insert into sales table
    $stmt = $conn->prepare("INSERT INTO sales (cashier, sale_date, total_amount, cash_given) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("sdd", $_SESSION['username'], $grand_total, $cash_given);
    $stmt->execute();
    $sale_id = $stmt->insert_id;

    // Insert each item into sale_items and update product stock
    $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, price, qty) VALUES (?, ?, ?, ?)");
    $stmt_update = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($_SESSION['cart'] as $item) {
        // Insert sale item
        $stmt_item->bind_param("iidi", $sale_id, $item['id'], $item['price'], $item['quantity']);
        $stmt_item->execute();

        // Update stock
        $stmt_update->bind_param("ii", $item['quantity'], $item['id']);
        $stmt_update->execute();
    }

    // Commit transaction
    $conn->commit();

    // Clear cart
    $_SESSION['cart'] = [];

    // Redirect to receipt
    header("Location: receipt.php?id=$sale_id");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo "Checkout failed: " . $e->getMessage();
    exit;
}
