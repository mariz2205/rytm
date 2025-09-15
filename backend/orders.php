<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['CustomerID'];

$sql = "SELECT o.OrderID, o.TotalAmount, o.TotalOrderQty, o.OrderDate,
               o.OrderStatus, o.DeliveryDate,
               p.ProductName, p.Image,
               oi.ProdOrdQty AS OrderQuantity,
               oi.ProductPrice
        FROM orderlist o
        INNER JOIN orderitems oi ON o.OrderID = oi.OrderID
        INNER JOIN productdetails p ON oi.ProductID = p.ProductID
        WHERE o.CustomerID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(["success" => true, "orders" => $orders]);

?>
