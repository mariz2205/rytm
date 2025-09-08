<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['CustomerID'];

$sql = "SELECT o.OrderID, o.ProductID, o.OrderQuantity, o.ProductPrice, o.OrderDate,
               p.ProductName, p.Image
        FROM orderlist o
        JOIN productdetails p ON o.ProductID = p.ProductID
        JOIN checkoutinfo c ON o.OrderID = c.OrderID
        WHERE c.CustomerID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(["success" => true, "orders" => $orders]);
