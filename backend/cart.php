<?php
session_start();
include "db.php"; // gives $conn

header("Content-Type: application/json");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = &$_SESSION['cart'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$qty    = max(1, (int)($_POST['qty'] ?? 1));

//actions
switch ($action) {
    case "add":
        if ($id) {
            if (!isset($cart[$id])) $cart[$id] = 0;
            $cart[$id] += $qty;
        }
        break;

    case "update":
        if ($id) {
            if ($qty > 0) $cart[$id] = $qty;
            else unset($cart[$id]);
        }
        break;

    case "remove":
        if ($id && isset($cart[$id])) {
            unset($cart[$id]);
        }
        break;
}

//build response
$response['items'] = [];
$total = 0;

// fetch product info from da DB
if (!empty($cart)) {
    $ids = implode(",", array_keys($cart));
    $sql = "SELECT ProductID, ProductName, ProductPrice, Image 
        FROM productdetails 
        WHERE ProductID IN ($ids)";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[$row['ProductID']] = $row;
}

foreach ($cart as $pid => $cqty) {
    if (!isset($products[$pid])) continue;
    $prod = $products[$pid];
    $subtotal = $prod['ProductPrice'] * $cqty;
    $total += $subtotal;

    $response['items'][] = [
        "id" => $pid,
        "name" => $prod['ProductName'],
        "price" => $prod['ProductPrice'],
        "qty" => $cqty,
        "subtotal" => $subtotal,
        "image" => $prod['Image']
    ];
}

}

$response['total'] = $total;

echo json_encode($response);
exit;
