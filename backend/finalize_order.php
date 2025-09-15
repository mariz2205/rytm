<?php
session_start();
include "db.php";

header("Content-Type: application/json");

// Check if user is logged in
$customerId = $_SESSION['CustomerID'] ?? 0;
if (!$customerId) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// Get input JSON
$input = json_decode(file_get_contents("php://input"), true) ?? [];
$items = [];

// --- Prepare items to checkout ---
// Buy Now
if (!empty($input['buy_now']) && !empty($input['productId'])) {
    $items[intval($input['productId'])] = intval($input['qty'] ?? 1);
}

// Cart checkout
else if (!empty($input['selected'])) {
    foreach ($input['selected'] as $pid => $qty) {
        $items[intval($pid)] = intval($qty);
    }
}

if (empty($items)) {
    echo json_encode(["success" => false, "error" => "No items selected"]);
    exit;
}

// --- Calculate total amount and total quantity ---
$totalAmount = 0;
$totalQty = 0;
$orderItems = [];

foreach ($items as $pid => $qty) {
    $stmt = $conn->prepare("SELECT ProductPrice FROM productdetails WHERE ProductID=? LIMIT 1");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $price = $row['ProductPrice'];
        $subtotal = $price * $qty;
        $totalAmount += $subtotal;
        $totalQty += $qty;
        $orderItems[] = [
            "ProductID" => $pid,
            "Qty" => $qty,
            "Price" => $price
        ];
    } else {
        echo json_encode(["success" => false, "error" => "Product not found (ID: $pid)"]);
        exit;
    }
    $stmt->close();
}

// --- Insert into orderlist ---
$orderDate = date("Y-m-d");
$status = "Pending";
$delivery = date("Y-m-d", strtotime("+3 days"));

$insOrder = $conn->prepare("
    INSERT INTO orderlist (CustomerID, TotalAmount, TotalOrderQty, OrderDate, OrderStatus, DeliveryDate)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insOrder->bind_param("idisss", $customerId, $totalAmount, $totalQty, $orderDate, $status, $delivery);
if (!$insOrder->execute()) {
    echo json_encode(["success" => false, "error" => "Order insert failed: ".$insOrder->error]);
    exit;
}
$orderId = $conn->insert_id;
$insOrder->close();

// --- Insert each product into orderitems ---
$insItem = $conn->prepare("INSERT INTO orderitems (OrderID, ProductID, ProdOrdQty, ProductPrice) VALUES (?, ?, ?, ?)");
foreach ($orderItems as $oi) {
    $insItem->bind_param("iiid", $orderId, $oi['ProductID'], $oi['Qty'], $oi['Price']);
    if (!$insItem->execute()) {
        echo json_encode(["success" => false, "error" => "Order item insert failed: ".$insItem->error]);
        exit;
    }
}
$insItem->close();

// --- Remove checked-out items from shoppingcart ---
$cartIds = array_keys($items);
if (!empty($cartIds)) {
    $ids = implode(",", array_map("intval", $cartIds));
    $conn->query("DELETE FROM shoppingcart WHERE CustomerID=$customerId AND ProductID IN ($ids)");
}

// --- Return success ---
echo json_encode(["success" => true, "orderId" => $orderId]);
exit;
