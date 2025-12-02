<?php
// admin/product_update_stock.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : null;

if ($id <= 0 || $stock === null || $stock < 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID or stock quantity']);
    exit;
}

// Check if product exists
$stmt_check = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

// Update stock
$stmt_update = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
$stmt_update->bind_param("ii", $stock, $id);
if ($stmt_update->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Stock updated']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update stock']);
}
exit;
?>
