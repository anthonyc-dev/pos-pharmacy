<?php
// admin/products.php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../db.php';

// Fetch categories for modal dropdown
$categories = [];
$cat_res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($cat = $cat_res->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Fetch products with category name, barcode, and category_id
$sql = "
  SELECT p.id, p.name, p.barcode, p.price, p.stock, p.expiration, p.category_id, p.mg, p.medicine_type, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  ORDER BY p.id DESC
";
$res = $conn->query($sql);
$products = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $products[] = $r;
}

// Fetch low stock products (below 10, including negative stock)
$low_stock_products = [];
$low_stock_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.stock < 10
  ORDER BY p.stock ASC
";
$res = $conn->query($low_stock_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $low_stock_products[] = $r;
    }
}

// Fetch expired products
$expired_products = [];
$expired_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.expiration < CURDATE() AND p.expiration IS NOT NULL
  ORDER BY p.expiration ASC
";
$res = $conn->query($expired_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $expired_products[] = $r;
    }
}

// Fetch out of stock products (stock = 0)
$out_of_stock_products = [];
$out_of_stock_query = "
  SELECT p.id, p.name, p.price, p.stock, p.expiration, IFNULL(c.name,'--') AS category
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  WHERE p.stock = 0
  ORDER BY p.name ASC
";
$res = $conn->query($out_of_stock_query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $out_of_stock_products[] = $r;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Products - Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      display: flex;
      background: #f9f9f9;
      color: #111;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #000;
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
      margin-bottom: 25px;
      color: #ff0000;
      font-weight: 700;
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
      margin: 0;
    }
    .sidebar ul li {
      margin: 15px 0;
    }
    .sidebar ul li a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 10px 14px;
      border-radius: 6px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .sidebar ul li a:hover {
      background: #ff0000;
      color: #fff;
      transform: translateX(3px);
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

    /* Main */
    .main {
      flex: 1;
      padding: 40px;
      margin-left: 260px;
      transition: margin-left 0.3s ease;
    }
    .main.full {
      margin-left: 0;
    }
    h1 {
      color: #000;
      font-size: 26px;
      margin-bottom: 20px;
      border-left: 5px solid #ff0000;
      padding-left: 12px;
    }

    /* Container */
    .container {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      border: 2px solid #000;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    /* Top Actions */
    .top-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .btn {
      background: #000;
      color: #fff;
      padding: 10px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      border: 2px solid #ff0000;
      transition: all 0.2s ease;
    }
    .btn:hover {
      background: #ff0000;
      color: #fff;
      border-color: #000;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #ddd;
      border-radius: 6px;
      overflow: hidden;
      background: #fff;
    }
    th, td {
      padding: 12px 14px;
      border-bottom: 1px solid #eee;
      text-align: left;
      font-size: 15px;
    }
    th {
      background: #000;
      color: #fff;
    }
    tr:hover td {
      background: #fff5f5;
    }

    /* Actions */
    .actions a {
      margin-right: 12px;
      text-decoration: none;
      font-weight: bold;
      color: #ff0000;
      transition: color 0.2s;
    }
    .actions a:hover {
      color: #000;
      text-decoration: underline;
    }

    .empty {
      padding: 25px;
      text-align: center;
      color: #666;
      font-style: italic;
    }

    /* Low Stock Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    .low-stock-modal {
      background: #fff;
      width: 90%;
      max-width: 700px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      border: 3px solid #ff0000;
    }
    .low-stock-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      background: #ff9800;
      color: #fff;
    }
    .low-stock-header h3 {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
    }
    .low-stock-body {
      padding: 24px;
      max-height: 450px;
      overflow-y: auto;
    }
    .low-stock-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .low-stock-table th,
    .low-stock-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .low-stock-table th {
      background: #000;
      color: #fff;
      font-weight: bold;
    }
    .low-stock-table tr:hover {
      background: #fff5f5;
    }
    .stock-critical {
      color: #f44336;
      font-weight: bold;
    }
    .stock-warning {
      color: #ff9800;
      font-weight: bold;
    }
    .modal-footer {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      padding: 16px 24px;
      background: #f5f5f5;
      border-top: 1px solid #ddd;
    }
    .close-btn {
      background: rgba(0, 0, 0, 0.2);
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.2s;
    }
    .close-btn:hover {
      background: rgba(0, 0, 0, 0.4);
    }

    /* Expired Products Modal */
    .expired-modal {
      background: #fff;
      width: 90%;
      max-width: 700px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      border: 3px solid #f44336;
    }
    .expired-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      background: #f44336;
      color: #fff;
    }
    .expired-header h3 {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
    }
    .expired-body {
      padding: 24px;
      max-height: 450px;
      overflow-y: auto;
    }
    .expired-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .expired-table th,
    .expired-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .expired-table th {
      background: #000;
      color: #fff;
      font-weight: bold;
    }
    .expired-table tr:hover {
      background: #fff5f5;
    }

    /* Out of Stock Modal */
    .out-of-stock-modal {
      background: #fff;
      width: 90%;
      max-width: 700px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      border: 3px solid #dc3545;
    }
    .out-of-stock-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 24px;
      background: #dc3545;
      color: #fff;
    }
    .out-of-stock-header h3 {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
    }
    .out-of-stock-body {
      padding: 24px;
      max-height: 450px;
      overflow-y: auto;
    }
    .out-of-stock-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .out-of-stock-table th,
    .out-of-stock-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .out-of-stock-table th {
      background: #000;
      color: #fff;
      font-weight: bold;
    }
    .out-of-stock-table tr:hover {
      background: #fff5f5;
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
    <div class="container">
      <div class="top-actions">
        <h1>Products</h1>
        <a class="btn" href="product_form.php">+ Add Product</a>
      </div>

      <div style="margin-bottom:20px;">
        <label for="barcode-input" style="font-weight:bold; font-size:16px; display:block; margin-bottom:6px;">Scan Barcode:</label>
        <input type="text" id="barcode-input" name="barcode-input" placeholder="Scan or enter barcode here" style="width: 250px; padding: 8px; font-size:14px; border: 2px solid #000; border-radius: 6px;">
      </div>

      <?php if (count($products) === 0): ?>
        <div class="empty">
          No products yet. Click <a href="product_form.php" style="color:#ff0000; font-weight:bold;">Add Product</a> to create one.
        </div>
      <?php else: ?>
        <table id="products-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Barcode</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>mg</th>
              <th>Type of Medicine</th>
              <!-- Removed Quantity Column header -->
              <th>Expiration</th>
              <th style="width:160px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
                <tr data-barcode="<?= htmlspecialchars($p['barcode']) ?>" data-expiration="<?= htmlspecialchars(($p['expiration'] && $p['expiration'] !== '0000-00-00') ? $p['expiration'] : '') ?>">
                  <td><?= htmlspecialchars($p['id']) ?></td>
                  <td><?= htmlspecialchars($p['name']) ?></td>
                  <td><?= htmlspecialchars($p['barcode']) ?></td>
                <td data-category-id="<?= htmlspecialchars($p['category_id']) ?>"><?= htmlspecialchars($p['category']) ?></td>
                  <td>₱<?= number_format($p['price'], 2) ?></td>
                  <td><?= htmlspecialchars($p['stock']) ?></td>
                  <td><?= htmlspecialchars($p['mg'] ?? '') ?></td>
                  <td><?= htmlspecialchars($p['medicine_type'] ?? '--') ?></td>
                  <!-- Removed Quantity column data cell -->
      <td>
        <?php if ($p['expiration'] && $p['expiration'] !== '0000-00-00'): ?>
      <?php
        try {
          $exp_date = new DateTime($p['expiration']);
          $today = new DateTime();
          $interval = $today->diff($exp_date);
          $days_diff = (int)$interval->format('%R%a');

          // Color code based on expiration status
          if ($days_diff < 0) {
            echo '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
          } elseif ($days_diff <= 7) {
            echo '<span style="color:#ff9800; font-weight:bold;">Expires Soon</span><br>';
          }
          echo htmlspecialchars($exp_date->format('M d, Y'));
        } catch (Exception $e) {
          echo '<span style="color:#999;">Invalid Date</span>';
        }
      ?>
  <?php else: ?>
    <span style="color:#999;">--</span>
  <?php endif; ?>
    </td>
                <td class="actions">
                  <a href="#" class="edit-product-btn" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-barcode="<?= htmlspecialchars($p['barcode']) ?>" data-category-id="<?= htmlspecialchars($p['category_id']) ?>" data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock'] ?>" data-mg="<?= htmlspecialchars($p['mg']) ?>" data-medicine-type="<?= htmlspecialchars($p['medicine_type']) ?>" data-expiration="<?= htmlspecialchars(($p['expiration'] && $p['expiration'] !== '0000-00-00') ? $p['expiration'] : '') ?>">
                    Edit
                  </a>
                  <a href="product_delete.php?id=<?= $p['id'] ?>"
                     onclick="return confirm('Delete product <?= htmlspecialchars(addslashes($p['name'])) ?>?')">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal-overlay" style="display:none; align-items:center; justify-content:center; position: fixed; top: 0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); z-index: 11000;">
  <div class="modal" style="background:#fff; padding: 25px; border-radius:12px; max-width:600px; width:90%; box-shadow: 0 6px 15px rgba(0,0,0,0.2);">
    <h2 style="margin-top:0; margin-bottom:20px;">Edit Product</h2>
    <form id="editProductForm">
      <input type="hidden" name="id" id="editProductId" value="">
      <div style="margin-bottom:12px;">
        <label for="editProductName" style="font-weight:bold;">Product Name</label>
        <input type="text" id="editProductName" name="name" required style="width: 100%; padding:8px; font-size: 15px;">
      </div>
      <div style="margin-bottom:12px;">
        <label for="editProductBarcode" style="font-weight:bold;">Barcode</label>
        <input type="text" id="editProductBarcode" name="barcode" style="width: 100%; padding:8px; font-size: 15px;">
      </div>
      <div style="margin-bottom:12px;">
        <label for="editProductCategory" style="font-weight:bold;">Category</label>
        <select id="editProductCategory" name="category_id" style="width: 100%; padding:8px; font-size: 15px;">
          <option value="">-- None --</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex; gap:12px; margin-bottom:12px;">
        <div style="flex:1;">
          <label for="editProductPrice" style="font-weight:bold;">Price</label>
          <input type="number" step="0.01" id="editProductPrice" name="price" required style="width: 100%; padding: 8px; font-size: 15px;">
        </div>
        <div style="flex:1;">
          <label for="editProductStock" style="font-weight:bold;">Stock</label>
          <input type="number" id="editProductStock" name="stock" required style="width: 100%; padding: 8px; font-size: 15px;">
        </div>
        <div style="flex:1;">
          <label for="editProductMg" style="font-weight:bold;">Dose (mg)</label>
          <input type="text" id="editProductMg" name="mg" style="width: 100%; padding: 8px; font-size: 15px;">
        </div>
      </div>
      <div style="margin-bottom:12px;">
        <label for="editProductMedicineType" style="font-weight:bold;">Type of Medicine</label>
        <input type="text" id="editProductMedicineType" name="medicine_type" style="width: 100%; padding:8px; font-size: 15px;">
      </div>
      <div style="margin-bottom: 12px;">
        <label for="editProductExpiration" style="font-weight:bold;">Expiration Date</label>
        <input type="date" id="editProductExpiration" name="expiration" style="width: 100%; padding: 8px; font-size: 15px;">
      </div>
      <div style="text-align:right;">
        <button type="button" id="editProductCancel" class="btn" style="margin-right: 10px; background:#ccc; border-color:#999; color:#000;">Cancel</button>
        <button type="submit" class="btn" style="background:#000;">Save Changes</button>
      </div>
      <div id="editProductMessage" style="margin-top: 10px; font-weight: bold;"></div>
    </form>
  </div>
</div>

<script>
  const editModal = document.getElementById('editProductModal');
  const editForm = document.getElementById('editProductForm');
  const msgBox = document.getElementById('editProductMessage');
  const cancelBtn = document.getElementById('editProductCancel');
  const tbody = document.querySelector('#products-table tbody');

  function openEditModal(productData) {
    msgBox.textContent = '';
    document.getElementById('editProductId').value = productData.id;
    document.getElementById('editProductName').value = productData.name;
    document.getElementById('editProductBarcode').value = productData.barcode;
    document.getElementById('editProductCategory').value = productData.category_id || '';
    document.getElementById('editProductPrice').value = productData.price;
    document.getElementById('editProductStock').value = productData.stock;
    document.getElementById('editProductMg').value = productData.mg || '';
    document.getElementById('editProductMedicineType').value = productData.medicine_type || '';

    if (productData.expiration) {
      const parsedDate = new Date(productData.expiration);
      if (!isNaN(parsedDate.getTime())) {
        const formattedDate = parsedDate.toISOString().substr(0, 10);
        document.getElementById('editProductExpiration').value = formattedDate;
      } else {
        document.getElementById('editProductExpiration').value = '';
      }
    } else {
      document.getElementById('editProductExpiration').value = '';
    }

    editModal.style.display = 'flex';
  }

  function closeEditModal() {
    editModal.style.display = 'none';
  }

  cancelBtn.addEventListener('click', closeEditModal);

  // Close modal on clicking outside modal content
  editModal.addEventListener('click', function(e) {
    if (e.target === editModal) closeEditModal();
  });

  // Attach click listeners to Edit buttons dynamically
  tbody.querySelectorAll('.edit-product-btn').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const productData = {
        id: link.dataset.id,
        name: link.dataset.name,
        barcode: link.dataset.barcode,
        category_id: link.dataset.categoryId || '',
        price: parseFloat(link.dataset.price) || 0,
        stock: parseInt(link.dataset.stock) || 0,
        mg: link.dataset.mg || '',
        medicine_type: link.dataset.medicineType || '',
        expiration: link.dataset.expiration || ''
      };
      openEditModal(productData);
    });
  });

  // Submit edited data via fetch POST to product_save.php, then update the table row
  editForm.addEventListener('submit', function(e) {
    e.preventDefault();
    msgBox.textContent = 'Saving...';

    const formData = new FormData(editForm);
    fetch('product_save.php?ajax=1', {
      method: 'POST',
      body: formData
    }).then(response => response.json())
      .then(data => {
        if (data.success) {
          msgBox.textContent = 'Saved successfully.';

          const productId = document.getElementById('editProductId').value;
          let updatedRow = null;
          tbody.querySelectorAll('tr').forEach(tr => {
            if (tr.children[0].textContent === productId) {
              updatedRow = tr;
            }
          });
          if (updatedRow) {
            updatedRow.children[1].textContent = document.getElementById('editProductName').value;
            updatedRow.children[2].textContent = document.getElementById('editProductBarcode').value;
            const categorySelect = document.getElementById('editProductCategory');
            const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text || '--';
            updatedRow.children[3].textContent = categoryText;
            updatedRow.children[3].setAttribute('data-category-id', categorySelect.value);
            const priceVal = parseFloat(document.getElementById('editProductPrice').value);
            updatedRow.children[4].textContent = '₱' + priceVal.toFixed(2);
            updatedRow.children[5].textContent = document.getElementById('editProductStock').value;
            updatedRow.children[6].textContent = document.getElementById('editProductMg').value;
            updatedRow.children[7].textContent = document.getElementById('editProductMedicineType').value;
            const expirationInputValue = document.getElementById('editProductExpiration').value;
            let expirationHtml = '<span style="color:#999;">--</span>';
            if (expirationInputValue) {
              const expDate = new Date(expirationInputValue);
              if (!isNaN(expDate.getTime())) {
                const today = new Date();
                const diffTime = expDate.getTime() - today.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let statusSpan = '';
                if (diffDays < 0) {
                  statusSpan = '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
                } else if (diffDays <= 7) {
                  statusSpan = '<span style="color:#ff9800; font-weight:bold;">Expires Soon</span><br>';
                }
                const options = {year: 'numeric', month: 'short', day: 'numeric'};
                const expDateStr = expDate.toLocaleDateString('en-US', options);
                expirationHtml = statusSpan + expDateStr;
              }
            }
            updatedRow.children[8].innerHTML = expirationHtml;
          }
          setTimeout(() => {
            msgBox.textContent = '';
            closeEditModal();
          }, 1200);
        } else {
          msgBox.textContent = 'Failed to save: ' + (data.message || 'Please check input and try again.');
        }
      }).catch(err => {
        msgBox.textContent = 'Error saving product.';
        console.error('Save error:', err);
      });
</script>

  <!-- Low Stock Alert Modal -->
  <div class="modal-overlay" id="low-stock-modal">
    <div class="low-stock-modal" role="dialog" aria-modal="true" aria-labelledby="low-stock-modal-title">
      <div class="low-stock-header">
        <h3 id="low-stock-modal-title">⚠️ Low Stock Alert</h3>
        <button class="close-btn" onclick="closeLowStockModal()">✕</button>
      </div>
      <div class="low-stock-body">
        <p style="margin-top:0; color:#666; font-size:15px;">
          The following products have low stock (below 10 units). Please restock soon:
        </p>
        <table class="low-stock-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Current Stock</th>
              <th>Expiration</th>
              <th>Price</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($low_stock_products as $lsp): ?>
            <tr>
              <td><?= htmlspecialchars($lsp['id']) ?></td>
              <td><?= htmlspecialchars($lsp['name']) ?></td>
              <td><?= htmlspecialchars($lsp['category']) ?></td>
              <td class="<?= $lsp['stock'] <= 5 ? 'stock-critical' : 'stock-warning' ?>">
                <?= $lsp['stock'] ?> units
              </td>
              <td>
                <?php if ($lsp['expiration']): ?>
                  <?php
                    $exp_date = new DateTime($lsp['expiration']);
                    $today = new DateTime();
                    $interval = $today->diff($exp_date);
                    $days_diff = (int)$interval->format('%R%a');

                    if ($days_diff < 0) {
                      echo '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
                    } elseif ($days_diff <= 7) {
                      echo '<span style="color:#ff9800; font-weight:bold;">Soon</span><br>';
                    }
                    echo htmlspecialchars($exp_date->format('M d, Y'));
                  ?>
                <?php else: ?>
                  <span style="color:#999;">--</span>
                <?php endif; ?>
              </td>
              <td>₱<?= number_format($lsp['price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn" onclick="closeLowStockModal()">OK, Got It</button>
      </div>
    </div>
  </div>

  <!-- Expired Products Alert Modal -->
  <div class="modal-overlay" id="expired-modal">
    <div class="expired-modal" role="dialog" aria-modal="true" aria-labelledby="expired-modal-title">
      <div class="expired-header">
        <h3 id="expired-modal-title">🔴 EXPIRED PRODUCTS ALERT</h3>
        <button class="close-btn" onclick="closeExpiredModal()">✕</button>
      </div>
      <div class="expired-body">
        <p style="margin-top:0; color:#666; font-size:15px; font-weight:600;">
          <strong style="color:#f44336;">URGENT:</strong> The following products have expired and must be removed or replaced immediately:
        </p>
        <table class="expired-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Stock</th>
              <th>Expired Date</th>
              <th>Days Ago</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($expired_products as $ep): ?>
            <tr>
              <td><?= htmlspecialchars($ep['id']) ?></td>
              <td><strong><?= htmlspecialchars($ep['name']) ?></strong></td>
              <td><?= htmlspecialchars($ep['category']) ?></td>
              <td><?= $ep['stock'] ?> units</td>
              <td style="color:#f44336; font-weight:bold;">
                <?php
                  $exp_date = new DateTime($ep['expiration']);
                  echo htmlspecialchars($exp_date->format('M d, Y'));
                ?>
              </td>
              <td style="color:#f44336; font-weight:bold;">
                <?php
                  $today = new DateTime();
                  $interval = $today->diff($exp_date);
                  $days_ago = abs((int)$interval->format('%R%a'));
                  echo $days_ago . ' day' . ($days_ago != 1 ? 's' : '');
                ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn" style="background:#f44336; border-color:#f44336;" onclick="closeExpiredModal()">I Understand</button>
      </div>
    </div>
  </div>

  <!-- Out of Stock Alert Modal -->
  <div class="modal-overlay" id="out-of-stock-modal">
    <div class="out-of-stock-modal" role="dialog" aria-modal="true" aria-labelledby="out-of-stock-modal-title">
      <div class="out-of-stock-header">
        <h3 id="out-of-stock-modal-title">🚫 OUT OF STOCK ALERT</h3>
        <button class="close-btn" onclick="closeOutOfStockModal()">✕</button>
      </div>
      <div class="out-of-stock-body">
        <p style="margin-top:0; color:#666; font-size:15px; font-weight:600;">
          <strong style="color:#dc3545;">ATTENTION:</strong> The following products are completely out of stock and need immediate restocking:
        </p>
        <table class="out-of-stock-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Stock</th>
              <th>Expiration</th>
              <th>Price</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($out_of_stock_products as $osp): ?>
            <tr>
              <td><?= htmlspecialchars($osp['id']) ?></td>
              <td><strong style="color:#dc3545;"><?= htmlspecialchars($osp['name']) ?></strong></td>
              <td><?= htmlspecialchars($osp['category']) ?></td>
              <td style="color:#dc3545; font-weight:bold;">0 units</td>
              <td>
                <?php if ($osp['expiration']): ?>
                  <?php
                    $exp_date = new DateTime($osp['expiration']);
                    $today = new DateTime();
                    $interval = $today->diff($exp_date);
                    $days_diff = (int)$interval->format('%R%a');

                    if ($days_diff < 0) {
                      echo '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
                    } elseif ($days_diff <= 7) {
                      echo '<span style="color:#ff9800; font-weight:bold;">Soon</span><br>';
                    }
                    echo htmlspecialchars($exp_date->format('M d, Y'));
                  ?>
                <?php else: ?>
                  <span style="color:#999;">--</span>
                <?php endif; ?>
              </td>
              <td>₱<?= number_format($osp['price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn" style="background:#dc3545; border-color:#dc3545;" onclick="closeOutOfStockModal()">Restock Now</button>
      </div>
    </div>
  </div>

  <!-- Toggle Script -->
  <script>
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");
    const toggleBtn = document.getElementById("toggle-btn");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
      main.classList.toggle("full");
    });

    // Modal state tracking
    let lowStockModalActive = false;
    let expiredModalActive = false;
    let outOfStockModalActive = false;
    let lastExpiredCount = 0;
    let lastLowStockCount = 0;
    let lastOutOfStockCount = 0;

    // Function to fetch and check alerts
    function checkAlerts() {
      fetch('alerts.php')
        .then(response => response.json())
        .then(data => {
          const expiredCount = data.expired.length;
          const outOfStockCount = data.out_of_stock.length;
          const lowStockCount = data.low_stock.length;

          // Check for new expired products (highest priority)
          if (expiredCount > lastExpiredCount && !expiredModalActive) {
            openExpiredModal(data.expired);
          }

          // Check for new out of stock products (only if no expired modal is active)
          if (outOfStockCount > lastOutOfStockCount && !expiredModalActive && !outOfStockModalActive) {
            openOutOfStockModal(data.out_of_stock);
          }

          // Check for new low stock products (only if no higher priority modals are active)
          if (lowStockCount > lastLowStockCount && !expiredModalActive && !outOfStockModalActive && !lowStockModalActive) {
            openLowStockModal(data.low_stock);
          }

          lastExpiredCount = expiredCount;
          lastOutOfStockCount = outOfStockCount;
          lastLowStockCount = lowStockCount;
        })
        .catch(err => console.error('Error checking alerts:', err));
    }

    // Initialize alerts on page load
    window.addEventListener('load', function() {
      // Set initial counts
      lastExpiredCount = <?php echo count($expired_products); ?>;
      lastOutOfStockCount = <?php echo count($out_of_stock_products); ?>;
      lastLowStockCount = <?php echo count($low_stock_products); ?>;

      // Check expired products first (highest priority)
      <?php if (count($expired_products) > 0): ?>
        openExpiredModal();
      <?php elseif (count($out_of_stock_products) > 0): ?>
        openOutOfStockModal();
      <?php elseif (count($low_stock_products) > 0): ?>
        openLowStockModal();
      <?php endif; ?>

      // Start polling for alerts every 30 seconds
      setInterval(checkAlerts, 30000);
    });

    // === Expired Products Modal ===
    function openExpiredModal(expiredData = null) {
      if (expiredModalActive) return;

      const modal = document.getElementById('expired-modal');
      const tbody = modal.querySelector('.expired-table tbody');

      // If dynamic data is provided, populate the table
      if (expiredData && expiredData.length > 0) {
        tbody.innerHTML = '';
        expiredData.forEach(product => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${product.id}</td>
            <td><strong>${product.name}</strong></td>
            <td>${product.category}</td>
            <td>${product.stock} units</td>
            <td style="color:#f44336; font-weight:bold;">${new Date(product.expiration).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
            <td style="color:#f44336; font-weight:bold;">${Math.abs(Math.floor((new Date() - new Date(product.expiration)) / (1000 * 60 * 60 * 24)))} day${Math.abs(Math.floor((new Date() - new Date(product.expiration)) / (1000 * 60 * 60 * 24))) !== 1 ? 's' : ''}</td>
          `;
          tbody.appendChild(row);
        });
      }

      modal.style.display = 'flex';
      expiredModalActive = true;

      setTimeout(() => {
        document.addEventListener('click', handleExpiredOutsideClick);
        document.addEventListener('keydown', handleExpiredEscClose);
      }, 100);
    }

    function closeExpiredModal() {
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

    function handleExpiredOutsideClick(e) {
      if (!expiredModalActive) return;

      const modal = document.getElementById('expired-modal');
      const modalContent = modal.querySelector('.expired-modal');

      if (e.target === modal && !modalContent.contains(e.target)) {
        e.stopPropagation();
        closeExpiredModal();
      }
    }

    function handleExpiredEscClose(e) {
      if (!expiredModalActive) return;

      if (e.key === 'Escape' || e.key === 'Esc') {
        e.preventDefault();
        e.stopPropagation();
        closeExpiredModal();
      }
    }

    // === Low Stock Modal ===
    function openLowStockModal(lowStockData = null) {
      if (lowStockModalActive) return;

      const modal = document.getElementById('low-stock-modal');
      const tbody = modal.querySelector('.low-stock-table tbody');

      // If dynamic data is provided, populate the table
      if (lowStockData && lowStockData.length > 0) {
        tbody.innerHTML = '';
        lowStockData.forEach(product => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td class="${product.stock <= 5 ? 'stock-critical' : 'stock-warning'}">${product.stock} units</td>
            <td>
              ${product.expiration ? (() => {
                const expDate = new Date(product.expiration);
                const today = new Date();
                const diffTime = expDate.getTime() - today.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let status = '';
                if (diffDays < 0) {
                  status = '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
                } else if (diffDays <= 7) {
                  status = '<span style="color:#ff9800; font-weight:bold;">Soon</span><br>';
                }
                return status + expDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
              })() : '<span style="color:#999;">--</span>'}
            </td>
            <td>₱${parseFloat(product.price).toFixed(2)}</td>
          `;
          tbody.appendChild(row);
        });
      }

      modal.style.display = 'flex';
      lowStockModalActive = true;

      setTimeout(() => {
        document.addEventListener('click', handleLowStockOutsideClick);
        document.addEventListener('keydown', handleLowStockEscClose);
      }, 100);
    }

    function closeLowStockModal() {
      const modal = document.getElementById('low-stock-modal');
      modal.style.display = 'none';
      lowStockModalActive = false;

      document.removeEventListener('click', handleLowStockOutsideClick);
      document.removeEventListener('keydown', handleLowStockEscClose);
    }

    function handleLowStockOutsideClick(e) {
      if (!lowStockModalActive) return;

      const modal = document.getElementById('low-stock-modal');
      const modalContent = modal.querySelector('.low-stock-modal');

      if (e.target === modal && !modalContent.contains(e.target)) {
        e.stopPropagation();
        closeLowStockModal();
      }
    }

    function handleLowStockEscClose(e) {
      if (!lowStockModalActive) return;

      if (e.key === 'Escape' || e.key === 'Esc') {
        e.preventDefault();
        e.stopPropagation();
        closeLowStockModal();
      }
    }

    // === Out of Stock Modal ===
    function openOutOfStockModal(outOfStockData = null) {
      if (outOfStockModalActive) return;

      const modal = document.getElementById('out-of-stock-modal');
      const tbody = modal.querySelector('.out-of-stock-table tbody');

      // If dynamic data is provided, populate the table
      if (outOfStockData && outOfStockData.length > 0) {
        tbody.innerHTML = '';
        outOfStockData.forEach(product => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${product.id}</td>
            <td><strong style="color:#dc3545;">${product.name}</strong></td>
            <td>${product.category}</td>
            <td style="color:#dc3545; font-weight:bold;">0 units</td>
            <td>
              ${product.expiration ? (() => {
                const expDate = new Date(product.expiration);
                const today = new Date();
                const diffTime = expDate.getTime() - today.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let status = '';
                if (diffDays < 0) {
                  status = '<span style="color:#f44336; font-weight:bold;">Expired</span><br>';
                } else if (diffDays <= 7) {
                  status = '<span style="color:#ff9800; font-weight:bold;">Soon</span><br>';
                }
                return status + expDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
              })() : '<span style="color:#999;">--</span>'}
            </td>
            <td>₱${parseFloat(product.price).toFixed(2)}</td>
          `;
          tbody.appendChild(row);
        });
      }

      modal.style.display = 'flex';
      outOfStockModalActive = true;

      setTimeout(() => {
        document.addEventListener('click', handleOutOfStockOutsideClick);
        document.addEventListener('keydown', handleOutOfStockEscClose);
      }, 100);
    }

    function closeOutOfStockModal() {
      const modal = document.getElementById('out-of-stock-modal');
      modal.style.display = 'none';
      outOfStockModalActive = false;

      document.removeEventListener('click', handleOutOfStockOutsideClick);
      document.removeEventListener('keydown', handleOutOfStockEscClose);

      // After closing out of stock modal, check for low stock
      <?php if (count($low_stock_products) > 0): ?>
        setTimeout(() => {
          openLowStockModal();
        }, 300);
      <?php endif; ?>
    }

    function handleOutOfStockOutsideClick(e) {
      if (!outOfStockModalActive) return;

      const modal = document.getElementById('out-of-stock-modal');
      const modalContent = modal.querySelector('.out-of-stock-modal');

      if (e.target === modal && !modalContent.contains(e.target)) {
        e.stopPropagation();
        closeOutOfStockModal();
      }
    }

    function handleOutOfStockEscClose(e) {
      if (!outOfStockModalActive) return;

      if (e.key === 'Escape' || e.key === 'Esc') {
        e.preventDefault();
        e.stopPropagation();
        closeOutOfStockModal();
      }
    }
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const barcodeInput = document.getElementById('barcode-input');
      const productsTableBody = document.querySelector('#products-table tbody');

      barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          const barcode = barcodeInput.value.trim();
          if (barcode === '') {
            alert('Please enter a barcode.');
            return;
          }
          fetch(`product_lookup.php?barcode=${encodeURIComponent(barcode)}`)
            .then(response => response.json())
            .then(data => {
              if (data.status === 'found') {
                // Redirect to the full edit page instead of opening modal
                window.location.href = `product_form.php?id=${data.id}`;
              } else if (data.status === 'not_found') {
                alert('Product with barcode "' + barcode + '" not found.');
                barcodeInput.value = '';
              } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                barcodeInput.value = '';
              }
            })
            .catch(err => {
              console.error('Error fetching product:', err);
              alert('Error fetching product data.');
            });
        }
      });
      
      // Enhance openEditModal to properly parse ISO expiration date string input
      const originalOpenEditModal = openEditModal;
      openEditModal = function (productData) {
        // Attempt to parse expiration date string if present
        if (productData.expiration) {
          // Normalize expiration date string to ISO yyyy-mm-dd format
          const d = new Date(productData.expiration);
          if (!isNaN(d.getTime())) {
            productData.expiration = d.toISOString().substr(0, 10);
          } else {
            productData.expiration = '';
          }
        }
        originalOpenEditModal(productData);
      };

      function escapeHtml(text) {
        const map = {
          '&': '&amp;',
          '<': '<',
          '>': '>',
          '"': '"',
          "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
      }

      function escapeJs(text) {
        return text.replace(/'/g, "\\'");
      }
    });
  </script>
</body>
</html>
