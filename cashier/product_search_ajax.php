<?php
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$where = "1=1";
$params = [];

if ($search !== '') {
    // Escape for LIKE statement, using prepared statements would be better but using simple escaping for now
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND name LIKE '%" . $search_esc . "%'";
}

if ($category_id > 0) {
    $where .= " AND category_id = " . $category_id;
}

$query = "SELECT * FROM products WHERE $where ORDER BY name ASC";

$result = $conn->query($query);

$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock']
        ];
    }
}

echo json_encode(['products' => $products]);
exit;
?>
