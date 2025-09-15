<?php
session_start();
include "db.php";

$customerId = $_SESSION['CustomerID'] ?? 0;
if (!$customerId) {
    die("Not logged in");
}

// calculate total
$sql = "SELECT ProductID, Quantity, ProductPrice FROM shoppingcart WHERE CustomerID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$res = $stmt->get_result();

$totalAmount = 0;
$totalQty = 0;
$items = [];

while ($row = $res->fetch_assoc()) {
    $totalAmount += $row['ProductPrice'] * $row['Quantity'];
    $totalQty += $row['Quantity'];
    $items[] = $row;
}

// insert into orderlist
$orderDate = date("Y-m-d");
$status = "Pending";
$delivery = date("Y-m-d", strtotime("+3 days"));

$ins = $conn->prepare("INSERT INTO orderlist (CustomerID, TotalAmount, TotalOrderQty, OrderDate, OrderStatus, DeliveryDate)
VALUES (?, ?, ?, ?, ?, ?)");
$ins->bind_param("idisss", $customerId, $totalAmount, $totalQty, $orderDate, $status, $delivery);
$ins->execute();
$orderId = $conn->insert_id;

// insert into orderitems
foreach ($items as $it) {
    $insItem = $conn->prepare("INSERT INTO orderitems (OrderID, ProductID, ProdOrdQty, ProductPrice) VALUES (?, ?, ?, ?)");
    $insItem->bind_param("iiid", $orderId, $it['ProductID'], $it['Quantity'], $it['ProductPrice']);
    $insItem->execute();
}

// clear cart
$del = $conn->prepare("DELETE FROM shoppingcart WHERE CustomerID=?");
$del->bind_param("i", $customerId);
$del->execute();

echo json_encode(["success" => true, "orderId" => $orderId]);
?>
