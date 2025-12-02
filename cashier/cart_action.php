<?php
require_once __DIR__ . '/../session.php';
if(!isset($_SESSION['username']) || $_SESSION['role']!=='cashier'){
    exit;
}
require_once __DIR__.'/../db.php';

$action = $_POST['action'] ?? '';

if($action==='add'){
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    if($row = $res->fetch_assoc()){
        $found=false;
        foreach($_SESSION['cart'] as &$item){
            if($item['id']==$id){ $item['qty']++; $found=true; break;}
        }
        if(!$found){
            $_SESSION['cart'][$id]=[
                'id'=>$id,
                'name'=>$row['name'],
                'price'=>$row['price'],
                'qty'=>1
            ];
        }
    }
    include 'cart_view.php';
}
elseif($action==='update'){
    foreach($_POST['qty'] as $pid => $qty){
        if(isset($_SESSION['cart'][$pid])){
            if($qty>0) $_SESSION['cart'][$pid]['qty']=$qty;
            else unset($_SESSION['cart'][$pid]);
        }
    }
    include 'cart_view.php';
}
elseif($action==='remove'){
    $id=intval($_POST['id']);
    unset($_SESSION['cart'][$id]);
    include 'cart_view.php';
}
elseif($action==='checkout'){
    $cash = floatval($_POST['cash']);
    $grand_total=0;
    foreach($_SESSION['cart'] as $item) $grand_total += $item['price']*$item['qty'];
    if($cash<$grand_total){
        echo json_encode(['success'=>false,'error'=>'Cash is less than total']);
        exit;
    }
    $change = $cash-$grand_total;
    $stmt = $conn->prepare("INSERT INTO sales (cashier,total_amount,cash_given,change_amount,sale_date) VALUES (?,?,?,?,NOW())");
    $stmt->bind_param("sddd", $_SESSION['username'],$grand_total,$cash,$change);
    $stmt->execute();
    $saleId = $stmt->insert_id;
    foreach($_SESSION['cart'] as $item){
        $stmt2 = $conn->prepare("INSERT INTO sale_items (sale_id,product_id,product_name,price,quantity) VALUES (?,?,?,?,?)");
        $stmt2->bind_param("iisdi",$saleId,$item['id'],$item['name'],$item['price'],$item['qty']);
        $stmt2->execute();
        // Reduce stock
        $conn->query("UPDATE products SET stock=stock-{$item['qty']} WHERE id={$item['id']}");
    }
    $_SESSION['cart']=[];
    echo json_encode(['success'=>true,'saleId'=>$saleId]);
}
