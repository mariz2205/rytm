<?php
session_start();
include "db.php";

header("Content-Type: application/json");

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo json_encode(["error" => "Order ID missing"]);
    exit;
}

// Fetch order items with product names
$stmt = $conn->prepare("
    SELECT o.ProductID, p.ProductName, o.OrderQuantity, o.ProductPrice, o.OrderDate
    FROM orderlist o
    JOIN productdetails p ON o.ProductID = p.ProductID
    WHERE o.OrderID = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$orderItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$orderItems) {
    echo json_encode(["error" => "Order not found in database"]);
    exit;
}

// Fetch checkoutinfo (transaction details)
$stmt = $conn->prepare("
    SELECT TransactionAmount, CustomerID, AmountPurchased, OrderStatus, OrderDate, DeliveryDate
    FROM checkoutinfo
    WHERE OrderID = ?
    LIMIT 1
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$checkoutResult = $stmt->get_result();
$checkoutInfo = $checkoutResult->fetch_assoc();
$stmt->close();

// Calculate total (backup, but checkoutinfo already has it)
$totalAmount = 0;
foreach ($orderItems as $item) {
    $totalAmount += $item['OrderQuantity'] * $item['ProductPrice'];
}

// Return JSON
echo json_encode([
    "items" => $orderItems,
    "checkout" => $checkoutInfo,
    "total" => $totalAmount
]);
