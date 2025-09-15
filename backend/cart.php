<?php
session_start();
include "db.php";
header("Content-Type: application/json");

$customerId = $_SESSION['CustomerID'] ?? 0; 
if (!$customerId) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$qty    = max(1, (int)($_POST['qty'] ?? 1));

switch ($action) {
    case "add":
        if ($id) {
            // check if already in cart
            $check = $conn->prepare("SELECT Quantity FROM shoppingcart WHERE CustomerID=? AND ProductID=?");
            $check->bind_param("ii", $customerId, $id);
            $check->execute();
            $result = $check->get_result();

            if ($row = $result->fetch_assoc()) {
                // update qty
                $newQty = $row['Quantity'] + $qty;
                $upd = $conn->prepare("UPDATE shoppingcart SET Quantity=? WHERE CustomerID=? AND ProductID=?");
                $upd->bind_param("iii", $newQty, $customerId, $id);
                $upd->execute();
            } else {
                // insert new
                $ins = $conn->prepare("INSERT INTO shoppingcart (ProductID, ProductName, Quantity, ProductPrice, CustomerID)
                    SELECT ProductID, ProductName, ?, ProductPrice, ? FROM productdetails WHERE ProductID=?");
                $ins->bind_param("iii", $qty, $customerId, $id);
                $ins->execute();
            }
        }
        break;

    case "update":
        if ($id) {
            if ($qty > 0) {
                $upd = $conn->prepare("UPDATE shoppingcart SET Quantity=? WHERE CustomerID=? AND ProductID=?");
                $upd->bind_param("iii", $qty, $customerId, $id);
                $upd->execute();
            } else {
                $del = $conn->prepare("DELETE FROM shoppingcart WHERE CustomerID=? AND ProductID=?");
                $del->bind_param("ii", $customerId, $id);
                $del->execute();
            }
        }
        break;

    case "remove":
        if ($id) {
            $del = $conn->prepare("DELETE FROM shoppingcart WHERE CustomerID=? AND ProductID=?");
            $del->bind_param("ii", $customerId, $id);
            $del->execute();
        }
        break;

    case "checkout":
        $selected = $_POST['selected'] ?? [];
        if (!empty($selected) && is_array($selected)) {
            foreach ($selected as $pid => $val) {
                unset($cart[$pid]); // remove each checked-out item from session cart
            }
        }
        break;
}

// build response
$response['items'] = [];
$total = 0;

$sql = "SELECT c.ProductID, c.Quantity, c.ProductPrice, c.ProductName, p.Image 
        FROM shoppingcart c 
        JOIN productdetails p ON c.ProductID = p.ProductID
        WHERE c.CustomerID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $subtotal = $row['ProductPrice'] * $row['Quantity'];
    $total += $subtotal;

    $response['items'][] = [
        "id" => $row['ProductID'],
        "name" => $row['ProductName'],
        "price" => $row['ProductPrice'],
        "qty" => $row['Quantity'],
        "subtotal" => $subtotal,
        "image" => $row['Image']
    ];
}

$response['total'] = $total;

echo json_encode($response);
exit;
