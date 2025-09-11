<?php
session_start();
include("db.php");

if (!isset($_SESSION['CustomerID'])) {
    die("User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['selected']) && !empty($_POST['qty'])) {
    $customerId = $_SESSION['CustomerID'];
    $selected = $_POST['selected'];
    $quantities = $_POST['qty'];
    $orderDate = date("Y-m-d");
    $deliveryDate = date("Y-m-d", strtotime("+3 days"));

    $dummyProductId = array_key_first($selected);
    $dummyQty = $quantities[$dummyProductId];
    $stmt = $conn->prepare("INSERT INTO orderlist (ProductID, OrderQuantity, ProductPrice, OrderDate) VALUES (?, ?, 0, ?)");
    $stmt->bind_param("iis", $dummyProductId, $dummyQty, $orderDate);
    $stmt->execute();
    $orderId = $stmt->insert_id; 
    $stmt->close();

    $totalAmount = 0;
    $stmt = $conn->prepare("UPDATE orderlist SET ProductPrice=? WHERE OrderID=? AND ProductID=?");
    $stmtInsert = $conn->prepare("INSERT INTO orderlist (OrderID, ProductID, OrderQuantity, ProductPrice, OrderDate) VALUES (?, ?, ?, ?, ?)");

    foreach ($selected as $id => $val) {
        $qty = intval($quantities[$id]);

        //product price
        $result = $conn->query("SELECT ProductPrice FROM productdetails WHERE ProductID=$id");
        $row = $result->fetch_assoc();
        $price = $row['ProductPrice'];

        if ($id == $dummyProductId) {
            $stmt->bind_param("dii", $price, $orderId, $id);
            $stmt->execute();
        } else {
            $stmtInsert->bind_param("iiids", $orderId, $id, $qty, $price, $orderDate);
            $stmtInsert->execute();
        }

        $totalAmount += $price * $qty;
    }
    $stmt->close();
    $stmtInsert->close();

    $status = "Pending";
    $amountPurchased = array_sum($quantities);

    $stmt = $conn->prepare("INSERT INTO checkoutinfo (OrderID, TransactionAmount, CustomerID, AmountPurchased, OrderStatus, OrderDate, DeliveryDate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idiisss", $orderId, $totalAmount, $customerId, $amountPurchased, $status, $orderDate, $deliveryDate);
    $stmt->execute();
    $stmt->close();

    #redirect
    header("Location: ../frontend/order_success.html?id=" . $orderId);
    exit;
}
?>
