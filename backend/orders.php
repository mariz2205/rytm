<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['CustomerID'];

// Fetch all orders for this user
$stmt = $conn->prepare("
    SELECT o.OrderID, o.TotalAmount, o.TotalOrderQty, o.OrderDate,
           o.OrderStatus, o.DeliveryDate
    FROM orderlist o
    WHERE o.CustomerID = ?
    ORDER BY o.OrderDate DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
    $orderId = $order['OrderID'];

    $orderDate = new DateTime($order['OrderDate']);
    $today = new DateTime();
    $daysPassed = $orderDate->diff($today)->days;

    if ($daysPassed < 1) {
        $newStatus = "Pending";       
    } elseif ($daysPassed <= 2) {
        $newStatus = "Shipping";       
    } else {
        $newStatus = "Delivered";     
    }

    // If DB status is different, update it
    if ($order['OrderStatus'] !== $newStatus) {
        $updateStmt = $conn->prepare("UPDATE orderlist SET OrderStatus = ? WHERE OrderID = ?");
        $updateStmt->bind_param("si", $newStatus, $orderId);
        $updateStmt->execute();
        $updateStmt->close();
        $order['OrderStatus'] = $newStatus; 
    }

    // Fetch items for this order
    $stmtItems = $conn->prepare("
        SELECT oi.ProductID, oi.ProdOrdQty AS OrderQuantity, oi.ProductPrice,
               p.ProductName, p.Image
        FROM orderitems oi
        JOIN productdetails p ON oi.ProductID = p.ProductID
        WHERE oi.OrderID = ?
    ");
    $stmtItems->bind_param("i", $orderId);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();

    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    $stmtItems->close();

    $order['items'] = $items;
    $orders[] = $order;
}

$stmt->close();

echo json_encode(["success" => true, "orders" => $orders]);
