<?php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$res = $conn->query("SELECT id, stock FROM products");
$stocks = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $stocks[$row['id']] = (int)$row['stock'];
    }
}

echo json_encode(['stocks' => $stocks]);
exit;
?>
