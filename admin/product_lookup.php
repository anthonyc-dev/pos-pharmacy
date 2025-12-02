<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header("Content-Type: application/json");

// Disable displaying errors to avoid corrupting JSON output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

try {
    if (!isset($_GET['barcode'])) {
        echo json_encode(["status" => "error", "message" => "No barcode provided"]);
        exit;
    }

    $barcode = trim($_GET['barcode']);

    // Prepare query to select product with category name
    $stmt = $conn->prepare("SELECT p.*, IFNULL(c.name, '--') AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.barcode = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $barcode);

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();

    if ($result === false) {
        echo json_encode(["status" => "error", "message" => "Get result failed: " . $stmt->error]);
        exit;
    }

    if ($result->num_rows > 0) {
        $p = $result->fetch_assoc();

        echo json_encode([
            "status" => "found",
            "id" => $p["id"],
            "name" => $p["name"],
            "category_id" => $p["category_id"],
            "category_name" => $p["category_name"],
            "price" => $p["price"],
            "stock" => $p["stock"],
            "expiration" => $p["expiration"],
            "barcode" => $p["barcode"],
            "mg" => $p["mg"]
        ]);
    } else {
        echo json_encode([
            "status" => "not_found"
        ]);
    }
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => "Exception: " . $e->getMessage()]);
    exit;
}
?>
