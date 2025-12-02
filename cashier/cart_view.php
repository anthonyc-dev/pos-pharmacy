<table>
<tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Action</th></tr>
<?php if(empty($_SESSION['cart'])): ?>
<tr><td colspan="5">Cart is empty.</td></tr>
<?php else:
$grand_total=0;
foreach($_SESSION['cart'] as $pid=>$item):
$qty=$item['qty'] ?? 0;
$subtotal = $qty * $item['price'];
$grand_total += $subtotal;
?>
<tr>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><input type="number" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="0" style="width:50px;"></td>
<td>₱<?= number_format($item['price'],2) ?></td>
<td>₱<?= number_format($subtotal,2) ?></td>
<td><button type="button" onclick="removeItem(<?= $pid ?>)">Remove</button></td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
<td colspan="2"><strong>₱<?= number_format($grand_total,2) ?></strong></td>
</tr>
<?php endif; ?>
</table>
