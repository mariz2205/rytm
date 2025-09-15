<?php
session_start();
include "db.php";
header("Content-Type: application/json");

$customerId = $_SESSION['CustomerID'] ?? 0;
if (!$customerId) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$cart = [];
$sql = "SELECT c.ProductID, c.Quantity, c.ProductPrice, c.ProductName, p.Image, p.ProductDescription
        FROM shoppingcart c
        JOIN productdetails p ON c.ProductID = p.ProductID
        WHERE c.CustomerID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $cart[$row['ProductID']] = $row;
}

$checkoutItems = [];
$total = 0;

// Buy now
$buyNowId = $_POST['buy_now'] ?? 0;
$buyNowQty = $_POST['qty'] ?? 1;

if ($buyNowId) {
    // Fetch product directly from productdetails
    $stmt = $conn->prepare("SELECT ProductID, ProductName, ProductPrice, ProductDescription, Image FROM productdetails WHERE ProductID=? LIMIT 1");
    $stmt->bind_param("i", $buyNowId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($item) {
        $qty = intval($buyNowQty);
        $subtotal = $item['ProductPrice'] * $qty;
        $checkoutItems[] = [
            "id" => $item['ProductID'],
            "name" => $item['ProductName'],
            "description" => $item['ProductDescription'],
            "price" => $item['ProductPrice'],
            "qty" => $qty,
            "subtotal" => $subtotal,
            "image" => $item['Image']
        ];
        $total += $subtotal;
    }
} 

// Cart checkout
else if (!empty($_POST['selected'])) {
    foreach ($_POST['selected'] as $pid => $v) {
        $pid = intval($pid);
        if (isset($cart[$pid])) {
            $item = $cart[$pid];
            $qty = $item['Quantity'];
            $subtotal = $qty * $item['ProductPrice'];
            $checkoutItems[] = [
                "id" => $pid,
                "name" => $item['ProductName'],
                "description" => $item['ProductDescription'],
                "price" => $item['ProductPrice'],
                "qty" => $qty,
                "subtotal" => $subtotal,
                "image" => $item['Image']
            ];
            $total += $subtotal;
        }
    }
}

echo json_encode([
    "items" => $checkoutItems,
    "total" => $total
]);
exit;
