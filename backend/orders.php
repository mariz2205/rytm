<?php
session_start();
include "db.php";
include "auto_received.php"; // This will auto-update old delivered orders

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
    ORDER BY o.OrderID DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
    $orderId = $order['OrderID'];

    // Fetch order items
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

?>