<?php
// admin/product_list.php
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Fetch all products including barcode
$res = $conn->query("SELECT p.id, p.name, p.price, p.qty, p.barcode, c.name AS category_name 
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
</head>
<body>

<h2>Products</h2>

<?php if (isset($_GET['success'])): ?>
<p style="color: green;">Product saved successfully!</p>
<?php endif; ?>

<a href="product_form.php">Add Product</a>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Barcode</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $res->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['category_name']) ?></td>
        <td><?= $row['price'] ?></td>
        <td><?= $row['qty'] ?></td>
        <td><?= htmlspecialchars($row['barcode']) ?></td>
        <td>
            <a href="product_form.php?id=<?= $row['id'] ?>">Edit</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
