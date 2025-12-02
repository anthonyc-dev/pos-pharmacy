<?php
// cashier/cart.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $id = (int) $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = (float) $_POST['product_price'];
    $qty = (int) $_POST['quantity'];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity'] += $qty;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => $qty
        ];
    }
    header("Location: cart.php");
    exit;
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($item) => $item['id'] != $id);
    header("Location: cart.php");
    exit;
}

// Fetch products
$products = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY name ASC");
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) $grand_total += $item['price'] * $item['quantity'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Cashier Cart</title>
<style>
body { font-family: Arial, sans-serif; margin:0; display:flex; background:#f9f9f9; }
.sidebar { width:220px; background:#111; color:#fff; min-height:100vh; padding:20px; }
.sidebar h2 { margin-bottom:20px; color:#ff3333; }
.sidebar .user-info {
    background: #222;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 3px solid #ff3333;
}
.sidebar .user-info .role {
    color: #ff3333;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 5px;
}
.sidebar .user-info .email {
    color: #aaa;
    font-size: 12px;
    word-break: break-all;
}
.sidebar ul { list-style:none; padding:0; }
.sidebar ul li { margin:10px 0; }
.sidebar ul li a { color:#fff; text-decoration:none; display:block; padding:5px; border-radius:4px; transition:0.3s; }
.sidebar ul li a:hover { background:#ff3333; }
.main { flex:1; padding:20px; }
h1 { margin-bottom:20px; }
table { width:100%; border-collapse: collapse; margin-bottom:20px; }
th, td { padding:10px; border:1px solid #ddd; text-align:left; }
th { background:#2d8cf0; color:white; }
.btn { padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
.btn-add { background:#2d8cf0; color:#fff; }
.btn-remove { background:#e74c3c; color:#fff; }
.btn-checkout { background:#27ae60; color:#fff; float:right; }
.card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:20px; }
input[type=number] { width:60px; padding:4px; border:1px solid #ccc; border-radius:4px; }
.input-group { margin-top:10px; }
.input-group label { display:block; margin-bottom:5px; }
.input-group input { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>💵 Cashier</h2>
    <div class="user-info">
      <div class="role"><?= ucfirst($_SESSION['role']) ?></div>
      <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
    </div>
    <ul>
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="sales.php">Sales History</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<div class="main">
<h1>🛒 Cashier Cart</h1>

<div class="card">
<h2>Available Products</h2>
<table>
<tr>
<th>Name</th>
<th>Price (₱)</th>
<th>Stock</th>
<th>Action</th>
</tr>
<?php while ($row = $products->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= number_format($row['price'], 2) ?></td>
<td><?= $row['stock'] ?></td>
<td>
<form method="post">
<input type="hidden" name="product_id" value="<?= $row['id'] ?>">
<input type="hidden" name="product_name" value="<?= htmlspecialchars($row['name']) ?>">
<input type="hidden" name="product_price" value="<?= $row['price'] ?>">
<input type="number" name="quantity" value="1" min="1" max="<?= $row['stock'] ?>" required>
<button type="submit" name="add_to_cart" class="btn btn-add">Add</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<div class="card">
<h2>Current Cart</h2>
<?php if (empty($_SESSION['cart'])): ?>
<p>Cart is empty.</p>
<?php else: ?>
<form method="post" action="checkout.php">
<table>
<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Subtotal</th>
<th>Action</th>
</tr>
<?php foreach ($_SESSION['cart'] as $item): 
$subtotal = $item['price'] * $item['quantity'];
?>
<tr>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><?= $item['quantity'] ?></td>
<td><?= number_format($item['price'], 2) ?></td>
<td><?= number_format($subtotal, 2) ?></td>
<td><a href="?remove=<?= $item['id'] ?>" class="btn btn-remove">Remove</a></td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
<td colspan="2"><strong>₱<?= number_format($grand_total, 2) ?></strong></td>
</tr>
</table>

<div class="input-group">
<label>Cash Given (₱)</label>
<input type="number" name="cash" step="0.01" required min="<?= $grand_total ?>" value="<?= $grand_total ?>">
</div>

<button type="submit" class="btn btn-checkout">Proceed & Print Receipt</button>
</form>
<?php endif; ?>
</div>
</div>

</body>
</html>
