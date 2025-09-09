<?php
session_start();
include "db.php"; // DB connection

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['selected']) || !isset($_POST['qty'])) {
    die("No products selected for checkout.");
}

$orderId = uniqid("ORD"); // Primary order ID
$orderDate = date("Y-m-d H:i:s");

$cartSelect = $_POST['selected'];
$cartQtys   = $_POST['qty'];

foreach ($cartSelect as $id => $val) {
    $id  = (int)$id;
    $qty = isset($cartQtys[$id]) ? max(1, (int)$cartQtys[$id]) : 1;

    // Fetch product price from DB
    $stmt = $conn->prepare("SELECT ProductPrice FROM productdetails WHERE ProductID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) continue;

    $price = $product['ProductPrice'];

    // Insert into orderlist
    $stmt = $conn->prepare("INSERT INTO orderlist (OrderID, ProductID, OrderQuantity, ProductPrice, OrderDate) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siids", $orderId, $id, $qty, $price, $orderDate);
    $stmt->execute();

    // Remove purchased items from session cart
    if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
}

// Redirect to order success page
header("Location: ../frontend/order_success.html?id=" . $orderId);

echo json_encode([
    "success" => true,
    "orderId" => $orderId
]);
exit;

?>
