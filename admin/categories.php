<?php
// admin/categories.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

$message = "";

// ADD CATEGORY
if (isset($_POST["add"])) {
    $name = trim($_POST["name"]);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $message = "✅ Category added!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    }
}

// UPDATE CATEGORY
if (isset($_POST["update"])) {
    $id = intval($_POST["id"]);
    $newName = trim($_POST["newName"]);
    if (!empty($newName)) {
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $newName, $id);
        if ($stmt->execute()) {
            $message = "✏️ Category updated!";
        }
    }
}

// DELETE CATEGORY
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $conn->query("DELETE FROM categories WHERE id=$id");
    $message = "🗑️ Category deleted!";
}

// Fetch categories
$res = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$categories = [];
while ($r = $res->fetch_assoc()) $categories[] = $r;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Categories - Admin</title>

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
        display: block;
        padding: 8px 10px;
        border-radius: 6px;
        text-decoration: none;
        transition: 0.2s;
    }
    .sidebar ul li a:hover {
        background: #ff0000;
    }

    /* Toggle Button */
    #toggle-btn {
        background: #ff0000;
        color: white;
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

    /* Main content */
    .main {
        flex: 1;
        padding: 30px;
        margin-left: 260px;
        transition: margin-left 0.3s ease;
    }
    .main.full {
        margin-left: 0;
    }

    /* Table and forms */
    .container {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn {
        padding: 8px 14px;
        background: #ff0000;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn:hover {
        background: #cc0000;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: white;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #ff0000;
        color: white;
    }
    .actions a {
        color: #ff0000;
        text-decoration: none;
        margin-left: 10px;
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

<div class="main" id="main">
    <h1>Categories</h1>

    <?php if ($message): ?>
        <p style="color:#111; font-weight:bold;"><?= $message ?></p>
    <?php endif; ?>

    <div class="container">

        <!-- Add Category -->
        <form method="post">
            <input type="text" name="name" placeholder="Enter new category" required>
            <button class="btn" name="add">+ Add Category</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td>
                        <form method="post" style="display:flex; gap:5px;">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <input type="text" name="newName" value="<?= htmlspecialchars($c['name']) ?>">
                    </td>
                    <td class="actions">
                            <button class="btn" name="update">Update</button>
                        </form>

                        <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
// Sidebar toggle (same as dashboard)
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
