<?php
// admin/reports.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

// Default filter = last 7 days
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-6 days'));
$end_date   = isset($_GET['end_date'])   ? $_GET['end_date']   : date('Y-m-d');

// Fetch total sales
$stmt = $conn->prepare("SELECT SUM(total_amount) AS total_income FROM sales WHERE sale_date BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$total_res = $stmt->get_result()->fetch_assoc();
$total_income = $total_res['total_income'] ?? 0;

// Fetch daily totals for chart
$stmt = $conn->prepare("
    SELECT sale_date, SUM(total_amount) AS daily_total
    FROM sales
    WHERE sale_date BETWEEN ? AND ?
    GROUP BY sale_date
    ORDER BY sale_date ASC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$chart_res = $stmt->get_result();
$dates = [];
$totals = [];
while ($row = $chart_res->fetch_assoc()) {
    $dates[] = $row['sale_date'];
    $totals[] = $row['daily_total'];
}

// Fetch detailed transactions
$stmt = $conn->prepare("SELECT id, sale_date, total_amount FROM sales WHERE sale_date BETWEEN ? AND ? ORDER BY sale_date DESC");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_res = $stmt->get_result();
$sales = [];
while ($row = $sales_res->fetch_assoc()) {
    $sales[] = $row;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reports - Admin</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      display: flex;
      background: #f9f9f9;
      transition: margin-left 0.3s ease;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #111;
      color: #fff;
      min-height: 100vh;
      padding: 20px;
      transition: transform 0.3s ease;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 100;
      box-shadow: 4px 0 10px rgba(0,0,0,0.2);
    }
    .sidebar h2 {
      margin-bottom: 20px;
      color: #ff0000;
    }
    .sidebar .user-info {
      background: #222;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 3px solid #ff0000;
    }
    .sidebar .user-info .role {
      color: #ff0000;
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
      padding: 8px 10px;
      border-radius: 6px;
      transition: 0.2s;
    }
    .sidebar ul li a:hover {
      background: #ff0000;
    }

    /* Toggle Button inside sidebar */
    #toggle-btn {
      background: #ff0000;
      color: #fff;
      border: none;
      padding: 10px 15px;
      font-size: 18px;
      cursor: pointer;
      position: absolute;
      top: 15px;
      right: -45px;
      border-radius: 5px 0 0 5px;
      transition: 0.3s;
      box-shadow: 2px 2px 6px rgba(0,0,0,0.3);
    }
    #toggle-btn:hover {
      background: #cc0000;
    }

    .sidebar.hidden {
      transform: translateX(-100%);
    }

    /* Main content */
    .main {
      flex:1;
      padding:20px;
      margin-left: 260px;
      transition: margin-left 0.3s ease;
    }
    .main.full {
      margin-left: 0;
    }

    .container {
      background:#fff;
      padding:20px;
      border-radius:10px;
      box-shadow:0 4px 10px rgba(0,0,0,0.05);
    }

    .filter-form {
      margin-bottom:20px;
      display:flex;
      gap:10px;
      align-items:center;
    }
    .filter-form input {
      padding:6px;
      border:1px solid #ccc;
      border-radius:6px;
    }
    .filter-form button {
      padding:8px 14px;
      background:#2d8cf0;
      color:#fff;
      border:none;
      border-radius:6px;
      cursor:pointer;
    }
    .filter-form button:hover { background:#1d6fc2; }

    .card { background:#f7f8fb; padding:15px; border-radius:8px; margin-bottom:20px; font-size:18px; font-weight:600; }
    canvas { margin:20px 0; }

    table {
      width:100%;
      border-collapse:collapse;
      background:#fff;
      border-radius:6px;
      overflow:hidden;
    }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f7f8fb; font-weight:600; }
    tr:hover td { background:#f9fcff; }
    .empty { padding:20px; text-align:center; color:#666; }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <button id="toggle-btn">☰</button>
    <h2>POS Admin</h2>
    <div class="user-info">
      <div class="role"><?= ucfirst($_SESSION['role']) ?></div>
      <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
    </div>
    <ul>
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a href="categories.php">Categories</a></li>
      <li><a href="accounts.php">Accounts</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="../logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main" id="main">
    <div class="container">
      <h1>Sales Reports</h1>

      <form class="filter-form" method="get">
        <label>From:</label>
        <input type="date" name="start_date" value="<?= $start_date ?>">
        <label>To:</label>
        <input type="date" name="end_date" value="<?= $end_date ?>">
        <button type="submit">Filter</button>
      </form>

      <div class="card">
        Total Income (<?= $start_date ?> to <?= $end_date ?>): 
        <span style="color:#2d8cf0;">₱<?= number_format($total_income, 2) ?></span>
      </div>

      <canvas id="salesChart" height="120"></canvas>

      <?php if (count($sales) === 0): ?>
        <div class="empty">No sales found in this date range.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Total Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sales as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['id']) ?></td>
              <td><?= htmlspecialchars($s['sale_date']) ?></td>
              <td><?= number_format($s['total_amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggle-btn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
      main.classList.toggle("full");
    });

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
          label: 'Daily Sales (₱)',
          data: <?= json_encode($totals) ?>,
          backgroundColor: '#2d8cf0'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  </script>
</body>
</html>
