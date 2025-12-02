<?php
// cashier/index.php
require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../db.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch categories
$categories = [];
$res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch products
$products = [];
$query = "SELECT * FROM products";
if ($selected_category) {
    $query .= " WHERE category_id = " . intval($selected_category);
}
$query .= " ORDER BY name ASC";

$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

// We do not change the $products array, so after fetch we do nothing here
// The medicine_type will be displayed below in the products grid

// Fetch low stock products (below 10)
$low_stock_products = [];
$low_stock_query = "SELECT * FROM products WHERE stock < 10 AND stock > 0 ORDER BY stock ASC";
$res = $conn->query($low_stock_query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $low_stock_products[] = $row;
    }
}

// Fetch expired products
$expired_products = [];
$expired_query = "SELECT * FROM products WHERE expiration < CURDATE() AND expiration IS NOT NULL ORDER BY expiration ASC";
$res = $conn->query($expired_query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $expired_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cashier Dashboard</title>
<style>
/* Basic styling */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    display: flex;
    background: #f9f9f9;
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
    z-index: 1000;
}
.sidebar h2 { margin-bottom: 20px; color: #ff3333; }
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
.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li { margin: 10px 0; }
.sidebar ul li a { color: #fff; text-decoration: none; display: block; padding: 5px; border-radius: 4px; transition: 0.3s; }
.sidebar ul li a:hover { background: #ff3333; }
.sidebar.hidden { transform: translateX(-100%); }

/* Main content */
.main {
    flex: 1;
    padding: 20px;
    padding-left: 50px;
    padding-right: 30px;
    padding-bottom: 270px;
    margin-left: 220px;
    transition: all 0.3s ease;
    max-width: calc(100% - 220px);
}
.main.full {
    margin-left: 0;
    max-width: 100%;
}

/* Products grid */
.products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px,1fr));
    gap: 10px;
    margin-top: 20px;
    padding-bottom: 30px;
}
.product {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    text-align: center;
    border: 1px solid #ddd;
    transition: transform 0.2s, box-shadow 0.2s;
}
.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
.product h3 { margin: 10px 0; color: #111; font-size: 16px; }
.btn {
    padding: 10px 16px;
    background: #ff3333;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    transition: 0.3s;
    font-size: 14px;
    font-weight: 600;
}
.btn:hover { background: #cc0000; transform: scale(1.05); }

/* Cart Icon Button - Hidden since cart is always visible */
#cart-icon-btn {
    display: none;
}

/* Cart Badge Text */
#cart-badge-text {
    font-weight: 600;
    letter-spacing: 0.5px;
}
#cart-total-text {
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    background: rgba(255,51,51,0.1);
    padding: 2px 8px;
    border-radius: 4px;
}

/* Fixed Bottom Cart Bar */
.cart-drawer {
    position: fixed;
    bottom: 0;
    left: 260px;
    right: 0;
    width: calc(100% - 260px);
    height: 250px;
    background: #fff;
    box-shadow: 0 -3px 15px rgba(0,0,0,0.2);
    z-index: 999;
    display: flex;
    flex-direction: column;
    border-top: 3px solid #ff3333;
    transition: all 0.3s ease;
}
.cart-drawer.full {
    left: 0;
    width: 100%;
}
.cart-header {
    background: #111;
    color: #fff;
    padding: 12px 20px 12px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #ff3333;
    flex-shrink: 0;
}
.cart-header h2 {
    margin: 0;
    font-size: 18px;
}
.cart-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px 15px 15px 30px;
}
.cart-body table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.cart-body th, .cart-body td {
    padding: 10px 6px;
    border-bottom: 1px solid #eee;
    text-align: center;
}
.cart-body th {
    background: #f5f5f5;
    color: #111;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 13px;
}
.cart-body th:nth-child(1),
.cart-body td:nth-child(1) {
    text-align: left;
    max-width: 150px;
    word-wrap: break-word;
}
.cart-body input[type="number"] {
    width: 55px;
    padding: 6px 4px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 13px;
}
.cart-body .btn {
    padding: 6px 10px;
    font-size: 12px;
}
.cart-footer {
    padding: 15px 20px 15px 30px;
    border-top: 2px solid #eee;
    background: #f9f9f9;
    flex-shrink: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Dropdown */
.dropdown { position: relative; }
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #111;
    min-width: 180px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 4px;
}
.dropdown-content a { color: #fff; padding: 8px 12px; text-decoration: none; display: block; }
.dropdown-content a:hover { background-color: #ff3333; }
.dropdown:hover .dropdown-content { display: block; }

/* Receipt modal */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
.modal { background: #fff; width: 90%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #111; color: #fff; }
.modal-body { padding: 0; background: #f7f7f7; }
.modal-body iframe { width: 100%; height: 70vh; border: 0; background: #fff; }
.modal-footer { display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #fff; }

/* Toggle button */
#toggle-btn {
    background:#ff3333;
    color:#fff;
    border:none;
    padding:10px 15px;
    cursor:pointer;
    border-radius:4px;
    position: fixed;
    left: 230px;
    top: 20px;
    z-index: 1001;
    transition: left 0.3s ease;
    font-size: 18px;
}
#toggle-btn.shifted { left: 10px; }

/* Low stock alert modal */
.low-stock-modal { background: #fff; width: 90%; max-width: 600px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
.low-stock-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #ff9800; color: #fff; }
.low-stock-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; }
.low-stock-body { padding: 20px; max-height: 400px; overflow-y: auto; }
.low-stock-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.low-stock-table th, .low-stock-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
.low-stock-table th { background: #f5f5f5; font-weight: bold; color: #111; }
.low-stock-table tr:hover { background: #f9f9f9; }
.stock-critical { color: #f44336; font-weight: bold; }
.stock-warning { color: #ff9800; font-weight: bold; }

/* Expired products modal */
.expired-modal { background: #fff; width: 90%; max-width: 600px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; border: 3px solid #f44336; }
.expired-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f44336; color: #fff; }
.expired-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; font-size: 18px; }
.expired-body { padding: 20px; max-height: 400px; overflow-y: auto; }
.expired-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.expired-table th, .expired-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
.expired-table th { background: #f5f5f5; font-weight: bold; color: #111; }
.expired-table tr:hover { background: #fff5f5; }
.close-btn { background: rgba(0,0,0,0.2); color: #fff; border: none; border-radius: 4px; padding: 6px 10px; cursor: pointer; transition: 0.2s; }
.close-btn:hover { background: rgba(0,0,0,0.4); }

/* Responsive Design */
@media (max-width: 1200px) {
    .cart-drawer {
        height: 240px;
    }
    .main {
        padding-bottom: 260px;
    }
}

@media (max-width: 1024px) {
    .cart-drawer {
        height: 230px;
    }
    .main {
        padding-bottom: 250px;
    }
    .products {
        grid-template-columns: repeat(auto-fill, minmax(180px,1fr));
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }
    .main {
        margin-left: 200px;
        padding: 15px;
        padding-left: 30px;
        padding-bottom: 240px;
    }
    .main.full {
        margin-left: 0;
    }
    #toggle-btn {
        left: 210px;
        width: 45px;
        height: 45px;
        padding: 8px;
        font-size: 16px;
    }
    #toggle-btn.shifted {
        left: 10px;
    }
    .cart-drawer {
        height: 220px;
        left: 230px;
        width: calc(100% - 230px);
    }
    .cart-drawer.full {
        left: 0;
        width: 100%;
    }
    .cart-header {
        padding: 10px 15px 10px 20px;
    }
    .cart-header h2 {
        font-size: 16px;
    }
    #cart-badge-text {
        font-size: 13px;
    }
    #cart-total-text {
        font-size: 15px;
    }
    .products {
        grid-template-columns: repeat(auto-fill, minmax(150px,1fr));
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 180px;
    }
    .main {
        margin-left: 180px;
        padding: 10px;
        padding-left: 25px;
        padding-bottom: 230px;
    }
    #toggle-btn {
        left: 190px;
        width: 40px;
        height: 40px;
        padding: 6px;
        font-size: 14px;
    }
    .cart-drawer {
        height: 210px;
        left: 205px;
        width: calc(100% - 205px);
    }
    .cart-drawer.full {
        left: 0;
        width: 100%;
    }
    .cart-header {
        padding: 8px 12px 8px 18px;
    }
    .cart-header h2 {
        font-size: 14px;
    }
    #cart-badge-text {
        font-size: 11px;
    }
    #cart-total-text {
        font-size: 13px;
        padding: 1px 6px;
    }
    .cart-body {
        padding: 10px;
    }
    .cart-body table {
        font-size: 11px;
    }
    .cart-body th, .cart-body td {
        padding: 6px 3px;
    }
    .cart-footer {
        padding: 10px 12px;
    }
    .products {
        grid-template-columns: repeat(auto-fill, minmax(140px,1fr));
        gap: 12px;
    }
    .product {
        padding: 12px;
    }
    .product h3 {
        font-size: 14px;
    }
    .btn {
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h2>💵 Cashier</h2>
    <div class="user-info">
      <div class="role"><?= ucfirst($_SESSION['role']) ?></div>
      <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
    </div>
    <ul>
        <li><a href="index.php">Dashboard</a></li>
        <li class="dropdown">
            <a href="javascript:void(0)">Categories ▾</a>
            <div class="dropdown-content">
                <a href="index.php">All Products</a>
                <?php foreach ($categories as $c): ?>
                <a href="index.php?category=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main" id="main">
    <button id="toggle-btn">☰</button>
    <div style="margin-bottom:20px; padding-left:30px;">
        <h1 style="margin:0; color:#111;">Products</h1>
    </div>

    <div style="padding-left: 30px; margin-bottom: 10px;">
        <input type="text" id="product-search" placeholder="Search products..." style="width: 100%; max-width: 400px; padding: 8px 12px; font-size: 15px; border-radius: 6px; border: 1px solid #ddd;">
    </div>
    <div class="products" id="products-container">
        <?php foreach ($products as $p): ?>
        <div class="product" data-id="<?= $p['id'] ?>" data-stock="<?= $p['stock'] ?>">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p style="font-size: 14px; color: #555; margin: 2px 0; font-weight: 600;">
                Dose: <?= htmlspecialchars($p['mg'] ?? '--') ?>
                <br>
                <small>Type: <?= htmlspecialchars($p['medicine_type'] ?? '--') ?></small>
            </p>
            <p style="color:#ff3333; font-size: 18px; font-weight: 700; margin: 8px 0;">₱<?= number_format($p['price'],2) ?></p>
            <p class="product-stock-count" style="font-size: 13px; margin: 5px 0; font-weight: 600; color: <?= $p['stock'] <= 5 ? '#f44336' : ($p['stock'] <= 10 ? '#ff9800' : '#4caf50') ?>">
                Stock: <?= $p['stock'] ?> pieces
            </p>
            <button class="btn" onclick="addToCart(<?= $p['id'] ?>,'<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>',<?= $p['price'] ?>,<?= $p['stock'] ?>)">Buy</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Fixed Bottom Cart Bar -->
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-header">
        <h2>🛒 Shopping Cart</h2>
        <div style="font-size: 14px; color: rgba(255,255,255,0.9); display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
            <span id="cart-badge-text" style="font-weight: 600;">0 items</span>
        
        </div>
    </div>
    <div class="cart-body" id="cart-container"></div>
</div>

<!-- Receipt Modal -->
<div class="modal-overlay" id="receipt-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="receipt-modal-title">
        <div class="modal-header">
            <h3 id="receipt-modal-title" style="margin:0;">Receipt Preview</h3>
            <button onclick="closeReceiptModal()" style="background:#ff3333;color:#fff;border:none;border-radius:4px;padding:6px 10px;cursor:pointer;">✕</button>
        </div>
        <div class="modal-body">
            <iframe id="receipt-frame" title="Receipt"></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn" style="background:#2d8cf0;" onclick="printReceiptInModal()">Print</button>
            <button class="btn" style="background:#666;" onclick="closeReceiptModal()">Close</button>
        </div>
    </div>
</div>

<!-- Low Stock Alert Modal -->
<div class="modal-overlay" id="low-stock-modal">
    <div class="low-stock-modal" role="dialog" aria-modal="true" aria-labelledby="low-stock-modal-title">
        <div class="low-stock-header">
            <h3 id="low-stock-modal-title">⚠️ Low Stock Alert</h3>
            <button class="close-btn" onclick="closeLowStockModal()">✕</button>
        </div>
        <div class="low-stock-body">
            <p style="margin-top:0;color:#666;">The following products have low stock (below 10 units):</p>
            <table class="low-stock-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Current Stock</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_products as $lsp): ?>
                    <tr>
                        <td><?= htmlspecialchars($lsp['name']) ?></td>
                        <td class="<?= $lsp['stock'] <= 5 ? 'stock-critical' : 'stock-warning' ?>">
                            <?= $lsp['stock'] ?> units
                        </td>
                        <td>₱<?= number_format($lsp['price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeLowStockModal()">OK</button>
        </div>
    </div>
</div>

<!-- Expired Products Alert Modal -->
<div class="modal-overlay" id="expired-modal">
    <div class="expired-modal" role="dialog" aria-modal="true" aria-labelledby="expired-modal-title">
        <div class="expired-header">
            <h3 id="expired-modal-title">🔴 EXPIRED PRODUCTS</h3>
            <button class="close-btn" onclick="closeExpiredModal()">✕</button>
        </div>
        <div class="expired-body">
            <p style="margin-top:0;color:#666;font-weight:600;">
                <strong style="color:#f44336;">ALERT:</strong> The following products have expired. Do NOT sell these items:
            </p>
            <table class="expired-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Stock</th>
                        <th>Expired Date</th>
                        <th>Days Ago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expired_products as $ep): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ep['name']) ?></strong></td>
                        <td><?= $ep['stock'] ?> units</td>
                        <td style="color:#f44336;font-weight:bold;">
                            <?php
                                if ($ep['expiration']) {
                                    $exp_date = new DateTime($ep['expiration']);
                                    echo htmlspecialchars($exp_date->format('M d, Y'));
                                }
                            ?>
                        </td>
                        <td style="color:#f44336;font-weight:bold;">
                            <?php
                                if ($ep['expiration']) {
                                    $exp_date = new DateTime($ep['expiration']);
                                    $today = new DateTime();
                                    $interval = $today->diff($exp_date);
                                    $days_ago = abs((int)$interval->format('%R%a'));
                                    echo $days_ago . ' day' . ($days_ago != 1 ? 's' : '');
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn" style="background:#f44336;" onclick="closeExpiredModal()">I Understand</button>
        </div>
    </div>
</div>

<script>
// Elements
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main');
const cartContainer = document.getElementById('cart-container');
const cartBadgeText = document.getElementById('cart-badge-text');
const cartTotalText = document.getElementById('cart-total-text');
const cartDrawer = document.getElementById('cart-drawer');
const toggleBtn = document.getElementById('toggle-btn');

// Sidebar toggle
toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    main.classList.toggle('full');
    cartDrawer.classList.toggle('full');
    toggleBtn.classList.toggle('shifted');
});

// Load cart and update badge
function loadCart() {
    fetch('cart_ajax.php')
    .then(res => res.text())
    .then(html => {
        cartContainer.innerHTML = html;
        updateCartBadge();

        // Add event listeners to quantity inputs for real-time updates
        const qtyInputs = document.querySelectorAll('.qty-input');
        qtyInputs.forEach(input => {
            let updateTimeout = null;
            input.addEventListener('input', (e) => {
                const qty = parseInt(e.target.value) || 0;
                const price = parseFloat(e.target.dataset.price) || 0;
                const id = e.target.dataset.id;
                const subtotal = qty * price;

                // Update subtotal for this row
                const subtotalCell = document.querySelector('.subtotal-' + id);
                if (subtotalCell) {
                    subtotalCell.textContent = '₱' + subtotal.toFixed(2);
                }

                // Update grand total in cart table
                updateGrandTotal();

                // Update badge
                updateCartBadge();

                // Debounce the backend update
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(() => {
                    updateCart();
                }, 500); // Update backend after 500ms of no input
            });
        });
    });
}

// Update grand total in cart table
function updateGrandTotal() {
    const qtyInputs = document.querySelectorAll('.qty-input');
    let grandTotal = 0;

    qtyInputs.forEach(input => {
        const qty = parseInt(input.value) || 0;
        const price = parseFloat(input.dataset.price) || 0;
        grandTotal += qty * price;
    });

    const grandTotalCell = document.getElementById('grand-total');
    if (grandTotalCell) {
        grandTotalCell.textContent = '₱' + grandTotal.toFixed(2);
    }
}

// Update cart badge count and total
function updateCartBadge() {
    const cartForm = document.getElementById('cart-form');
    if (cartForm) {
        const qtyInputs = cartForm.querySelectorAll('input[name^="qty["]');
        const priceInputs = cartForm.querySelectorAll('input[name^="price["]');
        let totalItems = 0;
        let totalPrice = 0;

        qtyInputs.forEach((qtyInput, index) => {
            const qty = parseInt(qtyInput.value) || 0;
            const price = parseFloat(priceInputs[index]?.value || 0);
            totalItems += qty;
            totalPrice += qty * price;
        });

        cartBadgeText.textContent = totalItems + ' item' + (totalItems !== 1 ? 's' : '');
        cartTotalText.textContent = '₱' + totalPrice.toFixed(2);
    } else {
        cartBadgeText.textContent = '0 items';
        cartTotalText.textContent = '₱0.00';
    }
}

// Add item
function addToCart(id,name,price,stock) {
    if (stock <= 0) {
        alert("Out of stock!");
        return;
    }
    fetch('cart_ajax.php', {
        method:'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=add&id=${id}&name=${encodeURIComponent(name)}&price=${price}&qty=1`
    }).then(() => {
        loadCart();
    });
}

// Update cart
function updateCart() {
    const form = document.getElementById('cart-form');
    const data = new FormData(form);
    data.append('action','update');
    fetch('cart_ajax.php', {method:'POST', body:data})
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return res.json();
        } else {
            return res.text();
        }
    })
    .then(response => {
        if (typeof response === 'object' && response.messages) {
            // Show stock adjustment messages in modal
            showStockModal(response.messages);
        }
        // Reload cart to reflect any stock-limited quantity adjustments
        loadCart();
    });
}

// Remove item
function removeItem(id) {
    const data = new FormData();
    data.append('action','remove');
    data.append('id',id);
    fetch('cart_ajax.php', {method:'POST', body:data}).then(() => loadCart());
}

// Checkout
function checkout() {
    const form = document.getElementById('cart-form');
    const data = new FormData(form);
    data.append('action','checkout');
    fetch('cart_ajax.php', {method:'POST', body:data})
    .then(res => res.json())
    .then(resp => {
        if(resp.success){
            openReceiptModal(resp.saleId);
        }
        else { alert(resp.error); }
    });
}

window.onload = function() {
    loadCart();

    // Check expired products first (highest priority)
    <?php if (count($expired_products) > 0): ?>
        openExpiredModal();
    <?php elseif (count($low_stock_products) > 0): ?>
        openLowStockModal();
    <?php endif; ?>
};

// Receipt modal handlers
let receiptModalActive = false;

const searchInput = document.getElementById('product-search');
let searchTimeout = null;

function fetchProductsBySearch(query) {
    const category = new URLSearchParams(window.location.search).get('category') || '';

    const url = new URL('product_search_ajax.php', window.location.origin + window.location.pathname);
    url.searchParams.set('q', query);
    if (category) {
        url.searchParams.set('category', category);
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.products) {
                const container = document.getElementById('products-container');
                container.innerHTML = '';

                if (data.products.length === 0) {
                    container.innerHTML = '<p style="grid-column: 1 / -1; color:#888; font-style: italic;">No products found</p>';
                    return;
                }

                data.products.forEach(p => {
                    const productDiv = document.createElement('div');
                    productDiv.className = 'product';
                    productDiv.dataset.id = p.id;

                    const stockColor =
                        p.stock <= 5 ? '#f44336' :
                        p.stock <= 10 ? '#ff9800' :
                        '#4caf50';

                    productDiv.innerHTML = `
                        <h3>${p.name}</h3>
                        <p style="color:#ff3333; font-size: 18px; font-weight: 700; margin: 8px 0;">₱${p.price.toFixed(2)}</p>
                        <p class="product-stock-count" style="font-size: 13px; margin: 5px 0; font-weight: 600; color: ${stockColor}">
                            Stock: ${p.stock} pieces
                        </p>
                        <button class="btn" onclick="addToCart(${p.id},'${p.name.replace(/'/g, "\\'")}',${p.price},${p.stock})">Buy</button>
                    `;
                    container.appendChild(productDiv);
                });
            }
        });
}

if (searchInput) {
    searchInput.addEventListener('input', e => {
        const val = e.target.value.trim();

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchProductsBySearch(val);
        }, 300);
    });
}

function openReceiptModal(saleId){
    const modal = document.getElementById('receipt-modal');
    const frame = document.getElementById('receipt-frame');
    frame.removeAttribute('src');
    modal.style.display = 'flex';
    frame.src = 'receipt.php?id=' + encodeURIComponent(saleId);
    receiptModalActive = true;

    // Use setTimeout to prevent immediate triggering
    setTimeout(() => {
        document.addEventListener('click', handleReceiptOutsideClick);
        document.addEventListener('keydown', handleReceiptEscClose);
    }, 100);
}

function closeReceiptModal(){
    const modal = document.getElementById('receipt-modal');
    const frame = document.getElementById('receipt-frame');
    modal.style.display = 'none';
    receiptModalActive = false;

    // Clean up event listeners
    document.removeEventListener('click', handleReceiptOutsideClick);
    document.removeEventListener('keydown', handleReceiptEscClose);

    try { frame.src = 'about:blank'; } catch(e) {}
}

function refreshStock(){
    fetch('product_stock_ajax.php')
        .then(res => res.json())
        .then(data => {
            if(data.stocks){
                for(const [id, stock] of Object.entries(data.stocks)){
                    const stockElems = document.querySelectorAll(`.product[data-id='${id}'] .product-stock-count`);
                    stockElems.forEach(elem => {
                        elem.textContent = stock + ' units';
                        // update color based on stock level
                        if(stock <= 5){
                            elem.style.color = '#f44336'; // red
                            elem.style.fontWeight = '700';
                        } else if(stock <= 10){
                            elem.style.color = '#ff9800'; // orange
                            elem.style.fontWeight = '600';
                        } else {
                            elem.style.color = '#4caf50'; // green
                            elem.style.fontWeight = '400';
                        }
                    });
                }
            }
        });
}

function printReceiptInModal(){
    const frame = document.getElementById('receipt-frame');
    if(frame && frame.contentWindow){
        function afterPrint() {
            loadCart();
            refreshStock();
            // Check for low stock products after printing
            checkLowStockAfterPrint();
            frame.contentWindow.removeEventListener('afterprint', afterPrint);
        }
        frame.contentWindow.addEventListener('afterprint', afterPrint);
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }
}

function handleReceiptOutsideClick(e){
    if(!receiptModalActive) return;

    const modal = document.getElementById('receipt-modal');
    const modalContent = modal.querySelector('.modal');

    // Check if click is outside modal content
    if(e.target === modal && !modalContent.contains(e.target)){
        e.stopPropagation();
        closeReceiptModal();
    }
}

function handleReceiptEscClose(e){
    if(!receiptModalActive) return;

    if(e.key === 'Escape' || e.key === 'Esc'){
        e.preventDefault();
        e.stopPropagation();
        closeReceiptModal();
    }
}

// Modal state tracking
let lowStockModalActive = false;
let expiredModalActive = false;

// === Expired Products Modal ===
function openExpiredModal(){
    if(expiredModalActive) return;

    const modal = document.getElementById('expired-modal');
    modal.style.display = 'flex';
    expiredModalActive = true;

    setTimeout(() => {
        document.addEventListener('click', handleExpiredOutsideClick);
        document.addEventListener('keydown', handleExpiredEscClose);
    }, 100);
}

function closeExpiredModal(){
    const modal = document.getElementById('expired-modal');
    modal.style.display = 'none';
    expiredModalActive = false;

    document.removeEventListener('click', handleExpiredOutsideClick);
    document.removeEventListener('keydown', handleExpiredEscClose);

    // After closing expired modal, check for low stock
    <?php if (count($low_stock_products) > 0): ?>
        setTimeout(() => {
            openLowStockModal();
        }, 300);
    <?php endif; ?>
}

function handleExpiredOutsideClick(e){
    if(!expiredModalActive) return;

    const modal = document.getElementById('expired-modal');
    const modalContent = modal.querySelector('.expired-modal');

    if(e.target === modal && !modalContent.contains(e.target)){
        e.stopPropagation();
        closeExpiredModal();
    }
}

function handleExpiredEscClose(e){
    if(!expiredModalActive) return;

    if(e.key === 'Escape' || e.key === 'Esc'){
        e.preventDefault();
        e.stopPropagation();
        closeExpiredModal();
    }
}

// Stock adjustment modal
function showStockModal(messages) {
    // Create modal HTML
    const modalHTML = `
        <div class="modal-overlay" id="stock-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10000;">
            <div class="stock-modal" style="background: #fff; width: 90%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="stock-header" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #ff9800; color: #fff;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">⚠️ Stock Adjustment</h3>
                    <button class="close-btn" onclick="closeStockModal()">✕</button>
                </div>
                <div class="stock-body" style="padding: 20px;">
                    <p style="margin-top:0;color:#666;">The following adjustments were made to your cart due to stock limitations:</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        ${messages.map(msg => `<li style="margin-bottom: 8px; color: #333;">${msg}</li>`).join('')}
                    </ul>
                </div>
                <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #fff;">
                    <button class="btn" onclick="closeStockModal()">OK</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Add event listeners
    setTimeout(() => {
        document.addEventListener('click', handleStockOutsideClick);
        document.addEventListener('keydown', handleStockEscClose);
    }, 100);
}

function closeStockModal() {
    const modal = document.getElementById('stock-modal');
    if (modal) {
        modal.remove();
        document.removeEventListener('click', handleStockOutsideClick);
        document.removeEventListener('keydown', handleStockEscClose);
    }
}

function handleStockOutsideClick(e) {
    const modal = document.getElementById('stock-modal');
    if (!modal) return;

    const modalContent = modal.querySelector('.stock-modal');
    if (e.target === modal && !modalContent.contains(e.target)) {
        e.stopPropagation();
        closeStockModal();
    }
}

function handleStockEscClose(e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
        e.preventDefault();
        e.stopPropagation();
        closeStockModal();
    }
}

// === Low Stock Modal ===
function openLowStockModal(){
    if(lowStockModalActive) return;

    const modal = document.getElementById('low-stock-modal');
    modal.style.display = 'flex';
    lowStockModalActive = true;

    setTimeout(() => {
        document.addEventListener('click', handleLowStockOutsideClick);
        document.addEventListener('keydown', handleLowStockEscClose);
    }, 100);
}

function closeLowStockModal(){
    const modal = document.getElementById('low-stock-modal');
    modal.style.display = 'none';
    lowStockModalActive = false;

    document.removeEventListener('click', handleLowStockOutsideClick);
    document.removeEventListener('keydown', handleLowStockEscClose);
}

function handleLowStockOutsideClick(e){
    if(!lowStockModalActive) return;

    const modal = document.getElementById('low-stock-modal');
    const modalContent = modal.querySelector('.low-stock-modal');

    if(e.target === modal && !modalContent.contains(e.target)){
        e.stopPropagation();
        closeLowStockModal();
    }
}

function handleLowStockEscClose(e){
    if(!lowStockModalActive) return;

    if(e.key === 'Escape' || e.key === 'Esc'){
        e.preventDefault();
        e.stopPropagation();
        closeLowStockModal();
    }
}
</script>
</body>
</html>
