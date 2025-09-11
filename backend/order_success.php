<?php
session_start();
include "db.php"; 

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    die(json_encode(["error" => "Order ID missing"]));
}

//fetch all items for this order
$stmt = $conn->prepare("SELECT ProductID, OrderQuantity, ProductPrice, OrderDate FROM orderlist WHERE OrderID=?");
$stmt->bind_param("s", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$orderItems = $result->fetch_all(MYSQLI_ASSOC);

if (!$orderItems) {
    die(json_encode(["error" => "Order not found in database"]));
}

//calculate total
$totalAmount = 0;
foreach ($orderItems as $item) {
    $totalAmount += $item['OrderQuantity'] * $item['ProductPrice'];
}

//send data to frontend
?>
