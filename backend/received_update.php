<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['CustomerID'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Get raw input
$input = json_decode(file_get_contents("php://input"), true);
$orderId = isset($input["OrderID"]) ? intval($input["OrderID"]) : 0;
$customerId = $_SESSION['CustomerID'];

if (!$orderId) {
    echo json_encode(["success" => false, "message" => "Order ID missing"]);
    exit;
}

// Verify the order belongs to the logged-in customer and is delivered
$check = $conn->prepare("
    SELECT OrderStatus FROM orderlist 
    WHERE OrderID = ? AND CustomerID = ?
");
$check->bind_param("ii", $orderId, $customerId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Order not found"]);
    exit;
}

$order = $result->fetch_assoc();
if (strtolower($order["OrderStatus"]) !== "delivered") {
    echo json_encode(["success" => false, "message" => "Order is not marked as delivered"]);
    exit;
}

// Update to Received
$update = $conn->prepare("
    UPDATE orderlist 
    SET OrderStatus = 'Received' 
    WHERE OrderID = ? AND CustomerID = ?
");
$update->bind_param("ii", $orderId, $customerId);
$update->execute();

if ($update->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Order marked as received"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update order"]);
}

$update->close();
$conn->close();
?>
