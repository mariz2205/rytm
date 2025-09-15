<?php
session_start();
include "db.php";

header("Content-Type: application/json");

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo json_encode(["error" => "Order ID missing"]);
    exit;
}

// Fetch order summary
$stmt = $conn->prepare("
    SELECT CustomerID, TotalAmount, TotalOrderQty, OrderStatus, OrderDate, DeliveryDate
    FROM orderlist
    WHERE OrderID = ?
    LIMIT 1
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderSummary = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$orderSummary) {
    echo json_encode(["error" => "Order not found"]);
    exit;
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT oi.ProductID, p.ProductName, oi.ProdOrdQty AS OrderQuantity, oi.ProductPrice, p.Image
    FROM orderitems oi
    JOIN productdetails p ON oi.ProductID = p.ProductID
    WHERE oi.OrderID = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "items" => $orderItems,
    "checkout" => $orderSummary
]);
exit;
