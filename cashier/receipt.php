<?php
// cashier/receipt.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Get sale ID from query parameter
$saleId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($saleId <= 0) {
    echo "Invalid sale ID.";
    exit;
}

// Fetch sale info
$stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->bind_param("i", $saleId);
$stmt->execute();
$saleRes = $stmt->get_result();
$sale = $saleRes->fetch_assoc();

if (!$sale) {
    echo "Sale not found.";
    exit;
}

// Fetch sale items
$stmt = $conn->prepare("
    SELECT si.product_id, si.quantity, si.price, p.name AS name
    FROM sale_items si
    JOIN products p ON p.id = si.product_id
    WHERE si.sale_id = ?
");
$stmt->bind_param("i", $saleId);
$stmt->execute();
$itemsRes = $stmt->get_result();
$items = [];
while ($row = $itemsRes->fetch_assoc()) {
    $items[] = $row;
}

// Calculate total
$total = 0;
foreach ($items as $item) {
    $qty = $item['quantity'] ?? 0;
    $price = $item['price'] ?? 0;
    $total += $qty * $price;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= $saleId ?></title>
<style>
/* Layout with sidebar (hidden on print) */
html, body { height: auto; margin: 0; }
body {
  font-family: Arial, sans-serif;
  background: #f5f7fb;
  color: #000;
  display: flex;
}
.sidebar {
  width: 220px;
  background: #111;
  color: #fff;
  min-height: 100vh;
  padding: 20px;
}
.sidebar h2 {
  margin-bottom: 20px;
  color: #ff3333;
}
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
.sidebar ul {
  list-style: none;
  padding: 0;
}
.sidebar ul li {
  margin: 15px 0;
}
.sidebar ul li a {
  color: #fff;
  text-decoration: none;
  display: block;
  padding: 5px;
  border-radius: 4px;
  transition: 0.3s;
}
.sidebar ul li a:hover {
  background: #ff3333;
}
.main {
  flex: 1;
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
/* Receipt container for 58mm thermal printers */
.receipt-container {
  width: 58mm;
  background: #fff;
  padding: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}
h1, h2 { text-align:center; margin: 0; padding: 0; }
h1 { font-size: 14px; margin-top: 6px; }
h2 { font-size: 12px; margin-top: 4px; }
p { margin: 4px 0; padding: 0 6px; font-size: 11px; }
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 8px;
  font-size: 11px;
}
th, td {
  border: 1px dashed #000;
  padding: 4px;
  text-align: center;
  word-break: break-word;
}
th {
  background: #fff;
  color: #000;
  font-weight: bold;
}
.total { font-weight:bold; }
.btn-print {
  display: inline-block;
  background:#27ae60;
  color:#fff;
  padding:8px 16px;
  border:none;
  border-radius:5px;
  cursor:pointer;
  margin: 10px 0 16px;
}
.btn-print:hover { background:#1e8449; }

/* Print-specific rules */
@page { size: 58mm auto; margin: 0; }
@media print {
  .sidebar { display: none !important; }
  .main { padding: 0; margin: 0; }
  body { margin: 0; background: #fff; display: block; }
  .receipt-container { width: 58mm; box-shadow: none; padding: 0; margin: 0; }
  .btn-print { display: none !important; }
  th, td { border: 1px dashed #000; }
}
h2, p{
  text-align : center;
}
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
<div class="receipt-container">

<h1>Jespera Tele Pharmacy</h1>
<h2>Sale #<?= $saleId ?></h2>
<p><strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now')) ?></p>
<p><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier'] ?? $_SESSION['username']) ?></p>
<h2>Location : </h2><p>MARANDING LALA LANAO DEL NORTE <br>SALES INVOICE</p>

<table>
<tr>
<th>Product</th>
<th>Qty</th>
<th>Price (₱)</th>
<th>Subtotal (₱)</th>
</tr>
<?php foreach ($items as $item): 
    $qty = $item['quantity'] ?? 0;
    $price = $item['price'] ?? 0;
    $subtotal = $qty * $price;
?>
<tr>
<td><?= htmlspecialchars($item['name'] ?? '') ?></td>
<td><?= $qty ?></td>
<td><?= number_format($price, 2) ?></td>
<td><?= number_format($subtotal, 2) ?></td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="3" class="total">Total</td>
<td class="total">₱<?= number_format($total, 2) ?></td>
</tr>
<tr>
<td colspan="3" class="total">Cash Given</td>
<td class="total">₱<?= number_format($sale['cash_given'] ?? 0, 2) ?></td>
</tr>
<tr>
<td colspan="3" class="total">Change</td>
<td class="total">₱<?= number_format(($sale['cash_given'] ?? 0) - $total, 2) ?></td>
</tr>
</table>

<!-- <div style="text-align:center; padding: 6px;">
<button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
</div> -->

</div>
</div>

</body>
</html>
