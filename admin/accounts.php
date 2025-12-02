<?php
// admin/accounts.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Filter by role
$roleFilter = $_GET['role'] ?? "all";
if ($roleFilter === "admin") {
    $sql = "SELECT id, username, email, role, created_at FROM users WHERE role = 'admin' ORDER BY id DESC";
} elseif ($roleFilter === "cashier") {
    $sql = "SELECT id, username, email, role, created_at FROM users WHERE role = 'cashier' ORDER BY id DESC";
} else {
    $sql = "SELECT id, username, email, role, created_at FROM users ORDER BY id DESC";
}

$res = $conn->query($sql);
$accounts = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $accounts[] = $r;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Accounts - Admin</title>
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

    .top-actions { 
      display:flex; 
      justify-content:space-between; 
      align-items:center; 
      margin-bottom:20px; 
    }

    .btn { 
      padding:8px 14px; 
      background:#cc0000; 
      color: #fff; 
      text-decoration:none; 
      border-radius:6px; 
      font-weight:600;
      transition: 0.2s;
    }
    .btn:hover { background:#a00000; }

    .filter { margin-bottom:15px; }

    table { width:100%; border-collapse:collapse; }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#f7f7f7; font-weight:600; }

    tr:hover td { background:#fff3f3; }

    .actions a { margin-right:10px; color:#cc0000; text-decoration:none; font-weight:500; }
    .actions a:hover { text-decoration:underline; }

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
      <div class="top-actions">
        <h1>Manage Accounts</h1>
        <a class="btn" href="account_form.php">+ Add Account</a>
      </div>

      <div class="filter">
        <form method="get">
          <label>Filter by role: </label>
          <select name="role" onchange="this.form.submit()">
            <option value="all" <?= $roleFilter=="all"?"selected":"" ?>>All</option>
            <option value="admin" <?= $roleFilter=="admin"?"selected":"" ?>>Admin</option>
            <option value="cashier" <?= $roleFilter=="cashier"?"selected":"" ?>>Cashier</option>
          </select>
        </form>
      </div>

      <?php if (count($accounts) === 0): ?>
        <div class="empty">No accounts found.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($accounts as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['id']) ?></td>
              <td><?= htmlspecialchars($a['username']) ?></td>
              <td><?= htmlspecialchars($a['email']) ?></td>
              <td><?= htmlspecialchars(ucfirst($a['role'])) ?></td>
              <td><?= htmlspecialchars($a['created_at']) ?></td>
              <td class="actions">
                <a href="account_form.php?id=<?= $a['id'] ?>">Edit</a>
                <a href="account_delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Delete account <?= htmlspecialchars(addslashes($a['username'])) ?>?')">Delete</a>
              </td>
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
  </script>
</body>
</html>
