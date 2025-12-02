<?php
// admin/product_delete.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

// Get product ID safely
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 🧹 First, delete all related sale_items for this product
    $stmt = $conn->prepare("DELETE FROM sale_items WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 🗑 Then, delete the product itself
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();

    // ✅ Feedback message
    if ($ok) {
        $_SESSION['flash'] = "✅ Product deleted successfully.";
    } else {
        $_SESSION['flash'] = "❌ Could not delete product: " . $conn->error;
    }
}

header("Location: products.php");
exit;
?>
