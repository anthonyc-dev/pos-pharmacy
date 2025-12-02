<?php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
$username = "";
$email = "";
$role = "cashier";
$editMode = false;

if ($id) {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
        $role = $row['role'];
        $editMode = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if ($editMode) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $hashed, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $role, $id);
        }
        $stmt->execute();
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed, $role);
        $stmt->execute();
    }

    header("Location: accounts.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= $editMode ? "Edit Account" : "Add Account" ?> - Admin</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin:0;
    display:flex;
    background:#f9f9f9;
    transition: margin-left 0.3s ease;
}

/* Sidebar */
.sidebar {
    width:220px;
    background:#111;
    color:#fff;
    min-height:100vh;
    padding:20px;
    transition:transform 0.3s ease;
    position:fixed;
    left:0;
    top:0;
    z-index:100;
    box-shadow:4px 0 10px rgba(0,0,0,0.2);
}
.sidebar h2 { margin-bottom:20px; color:#ff0000; }
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
.sidebar ul { list-style:none; padding:0; }
.sidebar ul li { margin:15px 0; }
.sidebar ul li a { color:#fff; text-decoration:none; display:block; padding:8px 10px; border-radius:6px; transition:0.2s; }
.sidebar ul li a:hover { background:#ff0000; }

/* Toggle Button */
#toggle-btn {
    background:#ff0000; color:#fff; border:none; padding:10px 15px; font-size:18px;
    cursor:pointer; position:absolute; top:15px; right:-45px; border-radius:5px 0 0 5px; transition:0.3s;
    box-shadow:2px 2px 6px rgba(0,0,0,0.3);
}
#toggle-btn:hover { background:#cc0000; }

/* Hidden Sidebar */
.sidebar.hidden { transform:translateX(-100%); }

/* Main container */
.main { flex:1; padding:20px; margin-left:260px; transition: margin-left 0.3s ease; }
.main.full { margin-left:0; }
.container { background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }

/* Form styles */
form label { display:block; margin-top:15px; font-weight:600; }
form input, form select { width:100%; padding:8px; margin-top:5px; border-radius:4px; border:1px solid #ccc; }
form button { margin-top:20px; padding:10px 15px; background:#cc0000; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
form button:hover { background:#a00000; }
.back-link { margin-top:15px; }
.back-link a { color:#555; text-decoration:none; }
.back-link a:hover { text-decoration:underline; }
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
            <h1><?= $editMode ? "Edit Account" : "Add Account" ?></h1>
            <a class="btn" href="accounts.php">← Back to Accounts</a>
        </div>
        <form method="post">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label><?= $editMode ? "New Password (leave blank to keep current)" : "Password" ?></label>
            <input type="password" name="password" <?= $editMode ? "" : "required" ?>>

            <label>Role</label>
            <select name="role" required>
                <option value="admin" <?= $role=="admin"?"selected":"" ?>>Admin</option>
                <option value="cashier" <?= $role=="cashier"?"selected":"" ?>>Cashier</option>
            </select>

            <button type="submit"><?= $editMode ? "Update Account" : "Create Account" ?></button>
        </form>
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
