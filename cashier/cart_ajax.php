<?php
require_once __DIR__ . '/../session.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cashier') {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

require_once __DIR__ . '/../db.php';

// Ensure cart exists
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'add':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = $_POST['name'] ?? '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $qty = isset($_POST['qty']) ? max(1,(int)$_POST['qty']) : 1;

        if ($id && $name && $price > 0) {
            if (!isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] = ['id'=>$id,'name'=>$name,'price'=>$price,'qty'=>$qty];
            } else {
                $_SESSION['cart'][$id]['qty'] += $qty;
            }
        }
        break;

    case 'update':
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $pid => $q) {
                $pid = (int)$pid;
                $q = max(0,(int)$q);
                if ($q <= 0) {
                    unset($_SESSION['cart'][$pid]);
                } elseif (isset($_SESSION['cart'][$pid])) {
                    $_SESSION['cart'][$pid]['qty'] = $q;
                }
            }
        }
        break;

    case 'remove':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        unset($_SESSION['cart'][$id]);
        break;

    case 'checkout':
        $grand_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $price = $item['price'] ?? 0;
            $qty = $item['qty'] ?? 0;
            $grand_total += $price * $qty;
        }

        $cash_given = isset($_POST['cash']) ? (float)$_POST['cash'] : 0;
        if ($cash_given < $grand_total) {
            echo json_encode(['success'=>false,'error'=>'Cash given is less than total.']);
            exit;
        }

        // Check stock availability before checkout
        foreach ($_SESSION['cart'] as $item) {
            $id = $item['id'] ?? 0;
            $qty = $item['qty'] ?? 0;
            if ($id && $qty > 0) {
                $stmt_check = $conn->prepare("SELECT stock, name FROM products WHERE id = ?");
                $stmt_check->bind_param("i", $id);
                $stmt_check->execute();
                $result = $stmt_check->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($row['stock'] < $qty) {
                        echo json_encode(['success'=>false,'error'=>'Insufficient stock for "' . $row['name'] . '". Available: ' . $row['stock'] . ' units.']);
                        exit;
                    }
                } else {
                    echo json_encode(['success'=>false,'error'=>'Product not found.']);
                    exit;
                }
                $stmt_check->close();
            }
        }

        // Insert sale
        $stmt = $conn->prepare("INSERT INTO sales (total_amount,cash_given,sale_date) VALUES (?,?,NOW())");
        $stmt->bind_param("dd", $grand_total, $cash_given);
        $stmt->execute();
        $saleId = $stmt->insert_id;

        // Insert sale items and update stock
        foreach ($_SESSION['cart'] as $item) {
            $id = $item['id'] ?? 0;
            $qty = $item['qty'] ?? 0;
            $price = $item['price'] ?? 0;

            if ($id && $qty && $price >= 0) {
                $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id,product_id,quantity,price) VALUES (?,?,?,?)");
                $stmt2->bind_param("iiid", $saleId, $id, $qty, $price);
                $stmt2->execute();

                // Update stock using prepared statement
                $stmt_update = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt_update->bind_param("ii", $qty, $id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }

        // Clear cart only after confirmed checkout
        $_SESSION['cart'] = [];

        echo json_encode(['success'=>true,'saleId'=>$saleId]);
        exit;
}

// --- Cart HTML Output ---
if (!empty($_SESSION['cart'])):
    $grand_total = 0;
?>
<form id="cart-form">
<table>
<tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Action</th></tr>
<?php foreach ($_SESSION['cart'] as $id => $item):
    $name = $item['name'] ?? 'Unknown Product';
    $price = $item['price'] ?? 0;
    $qty = $item['qty'] ?? 1;
    $subtotal = $price * $qty;
    $grand_total += $subtotal;
?>
<tr>
<td><?= htmlspecialchars($name) ?></td>
<td>
    <input type="number" name="qty[<?= $id ?>]" value="<?= $qty ?>" min="1" data-price="<?= $price ?>" data-id="<?= $id ?>" class="qty-input">
</td>
<td>₱<?= number_format($price,2) ?></td>
<td class="subtotal-<?= $id ?>">₱<?= number_format($subtotal,2) ?></td>
<td><button type="button" onclick="removeItem(<?= $id ?>)" style="background:#ff3333;color:#fff;border:none;padding:5px 10px;border-radius:4px;">Remove</button></td>
</tr>
<?php endforeach; ?>
<tr><th colspan="3">Grand Total</th><th id="grand-total">₱<?= number_format($grand_total,2) ?></th><th></th></tr>
</table>

<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
    <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" onclick="updateCart()" class="btn" style="background:#28a745; width: 150px; padding: 10px;">Update Cart</button>
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #111; font-size: 14px;">Cash Given:</label>
            <input type="number" step="0.01" name="cash" id="cash-input" required style="width: 100px; padding: 10px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; font-weight: 600;">
        </div>
    </div>
    <button type="button" onclick="checkout()" class="btn" style="background:#ff3333; width: 200px; padding: 12px;">Confirm & Checkout</button>
</div>
</form>

<script>
// Remove single item
function removeItem(id){
    fetch('cart_ajax.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=remove&id='+id
    }).then(res=>res.text()).then(()=>{
        location.reload();
    });
}

// Checkout payment
function checkout(){
    const cash = document.getElementById('cash-input').value;
    const formData = new FormData(document.getElementById('cart-form'));
    formData.append('action','checkout');
    formData.set('cash', cash);

    fetch('cart_ajax.php', {method:'POST', body: formData})
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                alert('Checkout successful. Sale ID: '+data.saleId);
                location.reload();
            } else {
                alert(data.error);
            }
        });
}

// Print receipt without clearing cart
function printReceipt(){
    let receiptContent = document.getElementById('cart-form').outerHTML;
    let newWin = window.open('', '', 'width=600,height=600');
    newWin.document.write('<html><head><title>Receipt</title></head><body>'+receiptContent+'</body></html>');
    newWin.document.close();
    newWin.print();
}
</script>
<?php else: ?>
<p>Cart is empty.</p>
<?php endif; ?>
