<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['CustomerID'];

<<<<<<< HEAD
$sql = "SELECT o.OrderID, o.TotalAmount, o.TotalOrderQty, o.OrderDate,
               o.OrderStatus, o.DeliveryDate,
               p.ProductName, p.Image,
               oi.ProdOrdQty AS OrderQuantity,
               oi.ProductPrice
        FROM orderlist o
        INNER JOIN orderitems oi ON o.OrderID = oi.OrderID
        INNER JOIN productdetails p ON oi.ProductID = p.ProductID
        WHERE o.CustomerID = ?";

=======
// Fetch all checkout info for this user
$sql = "SELECT TransactionID, OrderID, TransactionAmount, AmountPurchased, 
               OrderStatus, OrderDate, DeliveryDate
        FROM checkoutinfo
        WHERE CustomerID = ?
        ORDER BY OrderDate DESC";
>>>>>>> 4cd1c6b8688da4f9996cc4e8715f2a098a045e2a
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($checkout = $result->fetch_assoc()) {
    $orderId = $checkout['OrderID'];

    // Fetch items for this order
    $sqlItems = "SELECT o.ProductID, o.OrderQuantity, o.ProductPrice,
                        p.ProductName, p.Image
                 FROM orderlist o
                 JOIN productdetails p ON o.ProductID = p.ProductID
                 WHERE o.OrderID = ?";
    $stmtItems = $conn->prepare($sqlItems);
    $stmtItems->bind_param("i", $orderId);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();

    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }

    // Attach items to checkout info
    $checkout['items'] = $items;

    $orders[] = $checkout;
}

echo json_encode(["success" => true, "orders" => $orders]);

?>
