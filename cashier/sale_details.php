<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sale #<?= $saleId ?> Details</title>
  <style>
    body { font-family:Arial, sans-serif; background:#f5f7fb; margin:0; display:flex; }
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
    .sidebar ul li { margin:15px 0; }
    .sidebar ul li a { color:#fff; text-decoration:none; display:block; padding:5px; border-radius:4px; transition:0.3s; }
    .sidebar ul li a:hover { background:#ff3333; }
    .main { flex:1; padding:20px; }
    .container { background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
    h1 { margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f7f8fb; }
    .btn { padding:8px 14px; background:#2d8cf0; color:#fff; text-decoration:none; border-radius:6px; }
    .btn:hover { background:#1d6fc2; }
    .summary { margin-top:20px; font-size:1.1rem; }

    /* receipt style (hidden by default) */
    #receipt { display:none; font-family:monospace; }
    #receipt h2 { text-align:center; margin-bottom:10px; }
    #receipt table { width:100%; border-collapse:collapse; }
    #receipt td { padding:4px 0; }
    @media print {
      body * { visibility:hidden; }
      #receipt, #receipt * { visibility:visible; }
      #receipt { display:block; position:absolute; top:0; left:0; width:100%; }
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
  <div class="container">
    <h1>Sale #<?= $saleId ?> Details</h1>

    <p><strong>Date:</strong> <?= $sale['sale_date'] ?></p>
    <p><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier']) ?></p>

    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Quantity</th>
          <th>Price (₱)</th>
          <th>Subtotal (₱)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= number_format($item['quantity'] * $item['price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="summary">
      <strong>Total:</strong> ₱<?= number_format($sale['total'], 2) ?>
    </div>

    <p>
      <a class="btn" href="sales.php">← Back to Sales</a>
      <button class="btn" onclick="window.print()">🖨 Print Receipt</button>
    </p>
  </div>

  <!-- Printable Receipt -->
  <div id="receipt">
    <h2>My Store</h2>
    <p>Sale ID: <?= $saleId ?><br>
    Date: <?= $sale['sale_date'] ?><br>
    Cashier: <?= htmlspecialchars($sale['cashier']) ?></p>

    <table>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></td>
            <td style="text-align:right;">₱<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p><strong>Total: ₱<?= number_format($sale['total'], 2) ?></strong></p>
    <p style="text-align:center;">Thank you for your purchase!</p>
  </div>
  </div>
</body>
</html>
