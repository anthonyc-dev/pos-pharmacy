<?php
// admin/alerts.php - AJAX endpoint for real-time alerts
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../db.php';

// Fetch low stock products (below 10, including negative stock)
$low_stock_products = [];
$low_stock_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.stock < 10
  ORDER BY p.stock ASC
";
$res = $conn->query($low_stock_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $low_stock_products[] = $r;
    }
}

// Fetch expired products
$expired_products = [];
$expired_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.expiration < CURDATE() AND p.expiration IS NOT NULL
  ORDER BY p.expiration ASC
";
$res = $conn->query($expired_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $expired_products[] = $r;
    }
}

// Fetch out of stock products (stock = 0)
$out_of_stock_products = [];
$out_of_stock_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.stock = 0
  ORDER BY p.name ASC
";
$res = $conn->query($out_of_stock_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $out_of_stock_products[] = $r;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'low_stock' => $low_stock_products,
    'expired' => $expired_products,
    'out_of_stock' => $out_of_stock_products
]);
?>
