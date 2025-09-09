<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['cart'])) {
    echo json_encode(["items" => [], "total" => 0]);
    exit;
}

$cart = $_SESSION['cart'];
$selected = $_POST['selected'] ?? [];

$checkoutItems = [];
$total = 0;

//buy now!
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


#add to cart
else if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    $selected = $_POST['selected'] ?? [];

    if (!empty($selected)) {
        $ids = implode(",", array_map("intval", array_keys($selected)));
        $sql = "SELECT ProductID, ProductName, ProductDescription, ProductPrice, Image 
                FROM productdetails 
                WHERE ProductID IN ($ids)";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
            $pid = $row['ProductID'];
            if (isset($cart[$pid])) {
                $qty = $cart[$pid];
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
}

echo json_encode([
    "items" => $checkoutItems,
    "total" => $total
]);