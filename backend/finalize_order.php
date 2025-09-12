<?php
session_start();
include("db.php");

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerId = $_SESSION['CustomerID'];
    $orderDate = date("Y-m-d");
    $deliveryDate = date("Y-m-d", strtotime("+3 days"));

    $items = [];

    // Buy now
    if (!empty($input['buy_now']) && !empty($input['productId'])) {
        $pid = intval($input['productId']);
        $qty = intval($input['qty'] ?? 1);

        $result = $conn->query("SELECT ProductPrice FROM productdetails WHERE ProductID=$pid");
        if (!$result || $result->num_rows == 0) {
            echo json_encode(["success" => false, "error" => "Product not found"]);
            exit;
        }
        $items[$pid] = $qty;
    }
    // Cart checkout
    else if (!empty($input['selected'])) {
        $items = $input['selected'];
    }

    if (empty($items)) {
        echo json_encode(["success" => false, "error" => "No items selected"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO orderlist (ProductID, OrderQuantity, ProductPrice, OrderDate) VALUES (?, ?, ?, ?)");
    $totalAmount = 0;
    $amountPurchased = 0;
    $orderId = null;

    foreach ($items as $id => $qty) {
        $id = intval($id);
        $qty = intval($qty);

        $result = $conn->query("SELECT ProductPrice FROM productdetails WHERE ProductID=$id");
        if (!$result || $result->num_rows == 0) continue;
        $row = $result->fetch_assoc();
        $price = $row['ProductPrice'];

        $subtotal = $price * $qty;
        $totalAmount += $subtotal;
        $amountPurchased += $qty;

        $stmt->bind_param("iiis", $id, $qty, $price, $orderDate);
        $stmt->execute();

        // Capture the last inserted order ID
        if ($orderId === null) {
            $orderId = $stmt->insert_id;
        }
    }
    $stmt->close();

    // Insert into checkoutinfo
    if ($orderId !== null) {
        $status = "Pending";
        $stmt = $conn->prepare("INSERT INTO checkoutinfo (OrderID, TransactionAmount, CustomerID, AmountPurchased, OrderStatus, OrderDate, DeliveryDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idiisss", $orderId, $totalAmount, $customerId, $amountPurchased, $status, $orderDate, $deliveryDate);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "orderId" => $orderId]);
    } else {
        echo json_encode(["success" => false, "error" => "Order not created"]);
    }
}
?>
