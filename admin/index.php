<?php
// admin/index.php
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

// --- Get total income ---
$totalIncome = 0;
$res = $conn->query("SELECT SUM(total_amount) AS income FROM sales");
if ($res && $row = $res->fetch_assoc()) {
    $totalIncome = $row['income'] ?? 0;
}

// --- Get daily sales (last 7 days) ---
$chartData = [];
$res = $conn->query("
    SELECT DATE(sale_date) AS sale_day, SUM(total_amount) AS daily_total
    FROM sales
    WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY sale_day
    ORDER BY sale_day ASC
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $chartData[$r['sale_day']] = $r['daily_total'];
    }
}

// --- Fill missing days for chart ---
$dates = [];
$values = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $dates[] = $d;
    $values[] = $chartData[$d] ?? 0;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
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

    /* Toggle Button */
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

    /* Hidden Sidebar */
    .sidebar.hidden {
      transform: translateX(-100%);
    }

    /* Main Content */
    .main {
      flex: 1;
      padding: 30px;
      margin-left: 260px;
      transition: margin-left 0.3s ease;
    }
    .main.full {
      margin-left: 0;
    }

    /* Dashboard Cards */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border-top: 5px solid #ff0000;
    }
    .card h2 {
      color: #111;
      margin-bottom: 10px;
    }
    .card p {
      font-size: 2rem;
      font-weight: bold;
      color: #ff0000;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
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

  <!-- Main Content -->
  <div class="main" id="main">
    <h1 style="color:#111;">Dashboard</h1>
    <div class="grid">
      <div class="card">
        <h2>Total Income</h2>
        <p>₱<?= number_format($totalIncome, 2) ?></p>
      </div>
      <div class="card">
        <h2>Sales (Last 7 Days)</h2>
        <canvas id="salesChart" height="150"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Sidebar toggle with slide animation
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggle-btn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
      main.classList.toggle("full");
    });

    // Chart for daily sales
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
          label: 'Daily Sales (₱)',
          data: <?= json_encode($values) ?>,
          backgroundColor: '#ff0000'
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
