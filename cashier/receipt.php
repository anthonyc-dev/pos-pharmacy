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
/* Clean receipt-only layout */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: auto;
  margin: 0;
  padding: 0;
  background: #f5f7fb;
}

body {
  font-family: Arial, sans-serif;
  color: #000;
  display: block;
}

/* Receipt container for 58mm thermal printers */
.receipt-container {
  width: 58mm;
  max-width: 58mm;
  background: #fff;
  padding: 10px;
  margin: 20px auto;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

h1, h2 {
  text-align: center;
  margin: 0;
  padding: 0;
}

h1 {
  font-size: 14px;
  margin-top: 6px;
  font-weight: bold;
}

h2 {
  font-size: 12px;
  margin-top: 4px;
}

p {
  margin: 4px 0;
  padding: 0 6px;
  font-size: 11px;
  text-align: center;
}

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

.total {
  font-weight: bold;
}

/* Print-specific rules */
@page {
  size: 58mm auto;
  margin: 0;
}

@media print {
  html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 58mm !important;
    height: auto !important;
    background: #fff !important;
  }

  body {
    display: block !important;
  }

  .receipt-container {
    width: 58mm !important;
    max-width: 58mm !important;
    box-shadow: none !important;
    padding: 5mm !important;
    margin: 0 !important;
    background: #fff !important;
  }

  /* Table borders */
  th, td {
    border: 1px dashed #000 !important;
  }

  /* Force print colors */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
   
  }

  h1, h2, p, td, th {
    color: #000 !important;
  }
}
</style>
</head>
<body>

<div class="receipt-container">

<h1>Jespera Tele Pharmacy</h1>
<h2>Sale #<?= $saleId ?></h2>
<p><strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now')) ?></p>
<p><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier'] ?? $_SESSION['username']) ?></p>
<h2>Location : </h2>
<p>MARANDING LALA LANAO DEL NORTE<br>SALES INVOICE</p>

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

</div>

</body>
</html>
