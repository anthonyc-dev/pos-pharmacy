<?php
// admin/product_form.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

// Fetch categories
$categories = [];
$catsRes = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($catsRes) {
    while ($r = $catsRes->fetch_assoc()) $categories[] = $r;
}

// Fetch product if editing
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
}

// Fix expiration format for input[type=date]
$expirationValue = "";
if ($product && $product["expiration"] && $product["expiration"] !== "0000-00-00") {
    $expirationValue = htmlspecialchars($product["expiration"]);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $product ? 'Edit' : 'Add' ?> Product</title>
<style>
/* Reset and base */
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'Segoe UI', sans-serif; display:flex; background-color:#f4f4f9; color:#111; }

/* Sidebar */
.sidebar {
    width:220px; background:#111; color:#fff; min-height:100vh; padding:20px;
    position:fixed; left:0; top:0; transition: transform 0.3s ease; box-shadow:4px 0 12px rgba(0,0,0,.3); z-index:1000;
}
.sidebar.hidden { transform: translateX(-100%); }
.sidebar h2 { margin-bottom:25px; color:#ff4b4b; font-weight:700; text-transform:uppercase; }
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
.sidebar ul { list-style:none; }
.sidebar ul li { margin:15px 0; }
.sidebar ul li a {
    color:#fff; text-decoration:none; display:block; padding:10px 15px;
    border-radius:6px; font-weight:500; transition: all 0.2s ease;
}
.sidebar ul li a:hover { background:#ff4b4b; transform: translateX(3px); }

/* Toggle Button */
#toggle-btn {
    background:#ff4b4b; color:#fff; border:none; padding:10px 15px; font-size:18px;
    cursor:pointer; position:absolute; top:15px; right:-45px; border-radius:5px 0 0 5px;
    box-shadow:2px 2px 6px rgba(0,0,0,0.3); transition:0.3s;
}
#toggle-btn:hover { background:#e03e3e; }

/* Main content */
.main { flex:1; margin-left:260px; padding:40px; transition: margin-left 0.3s ease; }
.main.full { margin-left:0; }
.main h1 { font-size:28px; margin-bottom:25px; color:#111; border-left:5px solid #ff4b4b; padding-left:12px; }

/* Form Card */
.form-card {
    max-width:720px; background:#fff; padding:30px; border-radius:12px;
    border:2px solid #ddd; box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

/* Form elements */
label { display:block; margin-bottom:6px; font-weight:600; color:#111; }
input[type="text"], input[type="number"], input[type="date"], select {
    width:100%; padding:10px; margin-bottom:16px; border:1px solid #ccc;
    border-radius:6px; font-size:15px; transition: all 0.2s ease;
}
input:focus, select:focus {
    outline:none; border-color:#ff4b4b; box-shadow:0 0 6px rgba(255,75,75,0.3);
}

/* Row layout */
.row { display:flex; gap:12px; }
.row .col { flex:1; }

/* Buttons */
.actions { margin-top:18px; }
button.btn {
    padding:10px 20px; background:#111; color:#fff; border:2px solid #ff4b4b;
    border-radius:6px; font-weight:bold; cursor:pointer; transition: all 0.2s;
}
button.btn:hover { background:#ff4b4b; border-color:#111; }
a.cancel { margin-left:10px; color:#ff4b4b; text-decoration:none; font-weight:bold; transition:color 0.2s; }
a.cancel:hover { color:#111; text-decoration:underline; }

/* Responsive */
@media(max-width:768px){
    .row{ flex-direction:column; }
    .main{ margin-left:0; padding:20px; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
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

<!-- MAIN -->
<div class="main" id="main">
    <h1><?= $product ? 'Edit' : 'Add' ?> Product</h1>

    <div class="form-card">
        <form method="post" action="product_save.php" id="productForm">
            <input type="hidden" name="id" value="<?= $product ? intval($product['id']) : 0 ?>">

            <label>Product Name</label>
            <input type="text" name="name" required value="<?= $product ? htmlspecialchars($product['name']) : '' ?>">

            <label>Barcode</label>
            <input type="text" name="barcode" value="<?= $product ? htmlspecialchars($product['barcode']) : '' ?>">

            <div class="row">
                <div class="col">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">-- None --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($product && $product['category_id']==$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col">
                    <label>Type of Medicine</label>
                    <select name="medicine_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="Tablet" <?= ($product && $product['medicine_type'] === 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                        <option value="Capsule" <?= ($product && $product['medicine_type'] === 'Capsule') ? 'selected' : '' ?>>Capsule</option>
                        <option value="Syrup" <?= ($product && $product['medicine_type'] === 'Syrup') ? 'selected' : '' ?>>Syrup</option>
                    </select>
                </div>

                <div class="col">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" required value="<?= $product ? htmlspecialchars($product['price']) : '0.00' ?>">
                </div>

                <div class="col">
                    <label>Stock</label>
                    <input type="number" name="stock" required value="<?= $product ? htmlspecialchars($product['stock']) : '0' ?>">
                </div>

                <div class="col">
                    <label>Dose (mg)</label>
                    <input type="text" name="mg" value="<?= $product ? htmlspecialchars($product['mg']) : '' ?>">
                </div>
            </div>

            <label>Expiration Date</label>
            <input type="date" name="expiration" value="<?= $expirationValue ?>">

            <div class="actions">
                <button class="btn" type="submit"><?= $product ? 'Update' : 'Add' ?> Product</button>
                <a class="cancel" href="products.php">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Sidebar toggle
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
