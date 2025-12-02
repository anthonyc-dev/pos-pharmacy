<?php
// cashier/sales.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

$cashier = $_SESSION['username'];
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$sql = "SELECT * FROM sales WHERE cashier = ? ";
$params = [$cashier];
$types = "s";

if (!empty($start)) {
    $sql .= " AND DATE(sale_date) >= ? ";
    $params[] = $start;
    $types .= "s";
}
if (!empty($end)) {
    $sql .= " AND DATE(sale_date) <= ? ";
    $params[] = $end;
    $types .= "s";
}
$sql .= " ORDER BY sale_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$sales = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $sales[] = $r;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sales History - Cashier</title>
  <style>
    body { margin:0; font-family:Arial, sans-serif; background:#f5f7fb; display:flex; }
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
    form { margin-bottom:20px; }
    input[type="date"] { padding:6px; border:1px solid #ccc; border-radius:6px; margin-right:10px; }
    .btn { padding:8px 14px; background:#2d8cf0; color:#fff; border:none; border-radius:6px; cursor:pointer; }
    .btn:hover { background:#1d6fc2; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px; overflow:hidden; }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f7f8fb; font-weight:600; }
    tr:hover td { background:#f9fcff; }
    .empty { padding:20px; text-align:center; color:#666; }
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
      <h1>Sales History</h1>

      <form method="get">
        <label>Start Date:</label>
        <input type="date" name="start" value="<?= htmlspecialchars($start) ?>">
        <label>End Date:</label>
        <input type="date" name="end" value="<?= htmlspecialchars($end) ?>">
        <button class="btn" type="submit">Filter</button>
      </form>

      <?php if (count($sales) === 0): ?>
        <div class="empty">No sales found for this period.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Total (₱)</th>
            <th>Date</th>
            <th>Cashier</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sales as $s): ?>
            <tr>
              <td><?= $s['id'] ?></td>
              <td><?= number_format($s['total'], 2) ?></td>
              <td><?= $s['sale_date'] ?></td>
              <td><?= htmlspecialchars($s['cashier']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
