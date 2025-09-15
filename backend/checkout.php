<?php
session_start();
include "db.php";

$customerId = $_SESSION['CustomerID'] ?? 0;
if (!$customerId) {
    die("Not logged in");
}

<<<<<<< HEAD
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
=======
$cart = $_SESSION['cart'];
$selected = $_POST['selected'] ?? [];
$checkoutItems = [];
$total = 0;

// Buy now
if (isset($_GET['buy_now'])) {
    $pid = intval($_GET['buy_now']);
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;

    $sql = "SELECT ProductID, ProductName, ProductDescription, ProductPrice, Image 
            FROM productdetails 
            WHERE ProductID = $pid LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $subtotal = $qty * $row['ProductPrice'];
        $total += $subtotal;

        $checkoutItems[] = [
            "id" => $pid,
            "name" => $row['ProductName'],
            "description" => $row['ProductDescription'],
            "price" => $row['ProductPrice'],
            "qty" => $qty,
            "subtotal" => $subtotal,
            "image" => $row['Image']
        ];
    }
}

// Cart checkout
else if (!empty($selected)) {
    $ids = implode(",", array_map("intval", array_keys($selected)));
    $sql = "SELECT ProductID, ProductName, ProductDescription, ProductPrice, Image 
            FROM productdetails 
            WHERE ProductID IN ($ids)";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['ProductID'];
        if (isset($cart[$pid])) {
            $qty = intval($cart[$pid]);
            $subtotal = $qty * $row['ProductPrice'];
            $total += $subtotal;

            $checkoutItems[] = [
                "id" => $pid,
                "name" => $row['ProductName'],
                "description" => $row['ProductDescription'],
                "price" => $row['ProductPrice'],
                "qty" => $qty,
                "subtotal" => $subtotal,
                "image" => $row['Image']
            ];
        }
    }
}

echo json_encode([
    "items" => $checkoutItems,
    "total" => $total
]);
>>>>>>> 4cd1c6b8688da4f9996cc4e8715f2a098a045e2a
