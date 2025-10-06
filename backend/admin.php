<?php

session_start();

// Prevent PHP warnings/notices from breaking JSON output shown to frontend
ini_set('display_errors', '0');
error_reporting(0);


header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$method = $_SERVER["REQUEST_METHOD"];
if ($method === "OPTIONS") {
    http_response_code(200);
    exit;
}

include "db.php";

/* AUTO UPDATE: Mark Delivered orders as Received if 2 days passed */
$conn->query("
    UPDATE orderlist
    SET OrderStatus = 'Received'
    WHERE OrderStatus = 'Delivered'
    AND DeliveryDate IS NOT NULL
    AND DeliveryDate <= DATE_SUB(NOW(), INTERVAL 2 DAY)
");

if ($method === "POST" && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
}

// Determine if handling products or orders
$type = $_GET["type"] ?? "products";


switch ($type) {
    case "products":
        switch ($method) {
            case "GET":
                $result = $conn->query("SELECT * FROM productdetails");
                $products = [];
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                echo json_encode($products);
                break;

            case "POST":
                $name = $_POST["ProductName"];
                $desc = $_POST["ProductDescription"];
                $cat = $_POST["Category"];
                $stock = $_POST["Stock"];
                $price = $_POST["ProductPrice"];
                $seller = $_POST["SellerID"];

                $image = $_POST["Image"] ?? "";

                if (isset($_FILES["ImageFile"]) && $_FILES["ImageFile"]["error"] === UPLOAD_ERR_OK) {
                    $image = basename($_FILES["ImageFile"]["name"]);
                    move_uploaded_file($_FILES["ImageFile"]["tmp_name"], "../img/products/" . $image);
                }

                $stmt = $conn->prepare("INSERT INTO productdetails (ProductName, ProductDescription, Image, Category, Stock, ProductPrice, SellerID) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssdds", $name, $desc, $image, $cat, $stock, $price, $seller);
                echo json_encode(["success" => $stmt->execute(), "id" => $stmt->insert_id]);
                break;

            case "PUT":
                $id = $_POST["ProductID"];
                $name = $_POST["ProductName"];
                $desc = $_POST["ProductDescription"];
                $cat = $_POST["Category"];
                $stock = $_POST["Stock"];
                $price = $_POST["ProductPrice"];
                $seller = $_POST["SellerID"];

                $image = $_POST["Image"] ?? "";

                if (isset($_FILES["ImageFile"]) && $_FILES["ImageFile"]["error"] === UPLOAD_ERR_OK) {
                    $image = basename($_FILES["ImageFile"]["name"]);
                    move_uploaded_file($_FILES["ImageFile"]["tmp_name"], "../img/products/" . $image);
                }

                $stmt = $conn->prepare("UPDATE productdetails 
                                        SET ProductName=?, ProductDescription=?, Image=?, Category=?, Stock=?, ProductPrice=?, SellerID=? 
                                        WHERE ProductID=?");
                $stmt->bind_param("ssssddsi", $name, $desc, $image, $cat, $stock, $price, $seller, $id);

                echo json_encode(["success" => $stmt->execute()]);
                break;

            case "DELETE":
                $input = json_decode(file_get_contents("php://input"), true);
                $id = $input["id"] ?? ($_GET["id"] ?? null);

                if (!$id) {
                    echo json_encode(["success" => false, "error" => "Missing ProductID"]);
                    break;
                }

                $stmt = $conn->prepare("DELETE FROM productdetails WHERE ProductID=?");
                if (!$stmt) {
                    echo json_encode(["success" => false, "error" => $conn->error]);
                    break;
                }
                $stmt->bind_param("i", $id);
                echo json_encode(["success" => $stmt->execute()]);
                break;


            default:
                http_response_code(405);
                echo json_encode(["error" => "Method not allowed"]);
        }
        break;

    case "orders":
        switch ($method) {
            case "GET":
                $sql = "SELECT 
                            o.OrderID, o.CustomerID, o.TotalAmount, o.TotalOrderQty, 
                            o.OrderDate, o.OrderStatus, o.DeliveryDate,
                            c.Username, c.LastName, c.FirstName, c.Email, c.CustomerAddress, c.ContactNo,
                            i.OrderItemID, i.ProductID, i.ProdOrdQty, i.ProductPrice,
                            p.ProductName, p.Image, p.Category
                        FROM orderlist o
                        JOIN customerdetails c ON o.CustomerID = c.CustomerID
                        JOIN orderitems i ON o.OrderID = i.OrderID
                        JOIN productdetails p ON i.ProductID = p.ProductID
                        ORDER BY o.OrderDate DESC";

                $result = $conn->query($sql);
                $orders = [];
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
                echo json_encode($orders);
                break;

            case "POST":
                // Expect: CustomerID, items[] = [{ProductID, Quantity}]
                $customerId = $_POST["CustomerID"];
                $items = json_decode($_POST["items"], true); 
                if (!$items || !is_array($items)) {
                    echo json_encode(["success" => false, "error" => "Invalid items"]);
                    break;
                }

                $conn->begin_transaction();
                try {
                    $totalAmount = 0;
                    $totalQty = 0;

                    // Insert into orderlist
                    $stmt = $conn->prepare("INSERT INTO orderlist (CustomerID, TotalAmount, TotalOrderQty, OrderDate, OrderStatus) VALUES (?, 0, 0, NOW(), 'Pending')");
                    $stmt->bind_param("i", $customerId);
                    $stmt->execute();
                    $orderId = $stmt->insert_id;

                    // Insert each item
                    foreach ($items as $item) {
                        $pid = $item["ProductID"];
                        $qty = $item["Quantity"];

                        // Get price
                        $res = $conn->query("SELECT ProductPrice, Stock FROM productdetails WHERE ProductID=$pid FOR UPDATE");
                        $prod = $res->fetch_assoc();
                        if (!$prod || $prod["Stock"] < $qty) {
                            throw new Exception("Insufficient stock for ProductID $pid");
                        }
                        $price = $prod["ProductPrice"];
                        $lineTotal = $price * $qty;

                        $stmt = $conn->prepare("INSERT INTO orderitems (OrderID, ProductID, ProdOrdQty, ProductPrice) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("iiid", $orderId, $pid, $qty, $price);
                        $stmt->execute();

                        // Deduct stock
                        $stmt = $conn->prepare("UPDATE productdetails SET Stock = Stock - ? WHERE ProductID=?");
                        $stmt->bind_param("ii", $qty, $pid);
                        $stmt->execute();

                        $totalAmount += $lineTotal;
                        $totalQty += $qty;
                    }

                    // Update totals
                    $stmt = $conn->prepare("UPDATE orderlist SET TotalAmount=?, TotalOrderQty=? WHERE OrderID=?");
                    $stmt->bind_param("dii", $totalAmount, $totalQty, $orderId);
                    $stmt->execute();

                    $conn->commit();
                    echo json_encode(["success" => true, "OrderID" => $orderId]);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(["success" => false, "error" => $e->getMessage()]);
                }
                break;

            case "PUT":
                $input = [];
                if (strpos($_SERVER["CONTENT_TYPE"] ?? "", "application/json") !== false) {
                    $input = json_decode(file_get_contents("php://input"), true);
                } else {
                    parse_str(file_get_contents("php://input"), $input);
                }

                $id = $input["OrderID"] ?? null;
                $status = $input["OrderStatus"] ?? null;

                if (!$id || !$status) {
                    echo json_encode(["success" => false, "error" => "Missing OrderID or OrderStatus"]);
                    break;
                }

                // Prevent admin from modifying already received orders
                $check = $conn->prepare("SELECT OrderStatus FROM orderlist WHERE OrderID=?");
                $check->bind_param("i", $id);
                $check->execute();
                $checkResult = $check->get_result()->fetch_assoc();
                if (!$checkResult) {
                    echo json_encode(["success" => false, "error" => "Order not found"]);
                    break;
                }

                $currentStatus = strtolower($checkResult["OrderStatus"]);

                if ($currentStatus === "received") {
                    echo json_encode(["success" => false, "error" => "Cannot modify a received order"]);
                    break;
                }

                // Allow only forward movement of status
                $allowedStatuses = ["pending", "accepted", "processing", "shipping", "delivered", "received"];
                $currentIndex = array_search($currentStatus, $allowedStatuses);
                $newIndex = array_search(strtolower($status), $allowedStatuses);

                if ($newIndex < $currentIndex) {
                    echo json_encode(["success" => false, "error" => "Cannot revert order status"]);
                    break;
                }

                // Update status
                $stmt = $conn->prepare("UPDATE orderlist SET OrderStatus=? WHERE OrderID=?");
                $stmt->bind_param("si", $status, $id);

                if (strtolower($status) === "delivered") {
                    $conn->query("UPDATE orderlist SET DeliveryDate = NOW() WHERE OrderID = " . intval($id));
                }

                if ($stmt->execute()) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => $stmt->error]);
                }
                break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Method not allowed"]);
        }
        break;

    case "users":
        switch ($method) {
            case "GET":
                $sql = " SELECT 
                            c.CustomerID,
                            c.Username,
                            c.LastName,
                            c.FirstName,
                            c.Email,
                            c.CustomerAddress,
                            c.ContactNo
                        FROM customerdetails c
                        ORDER BY c.CustomerID ";
                $result = $conn->query($sql);
                $users = [];
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
                echo json_encode($users);
                break;

            default:
                http_response_code(405);  
                echo json_encode(["error" => "Method not allowed"]);
        }
        break;

    case "seller":
        switch ($method) {
            case "GET":
                // Example: fetch current seller info (can also accept ?id=... if needed)
                $sellerID = $_GET['id'] ?? null;

                if ($sellerID) {
                    $stmt = $conn->prepare("SELECT SellerID, SellerUsername, SellerName, SellerEmail, SellerContactNo, SellerPassword FROM sellerinfo WHERE SellerID = ?");
                    $stmt->bind_param("i", $sellerID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $seller = $result->fetch_assoc();
                    echo json_encode($seller);
                } else {
                    // Fetch all sellers if no ID is passed
                    $result = $conn->query("SELECT SellerID, SellerUsername, SellerName, SellerEmail, SellerContactNo, SellerPassword FROM sellerinfo");
                    $sellerinfo = [];
                    while ($row = $result->fetch_assoc()) {
                        $sellerinfo[] = $row;
                    }
                    echo json_encode($sellerinfo);
                }
                break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
    }
    break;


    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid type"]);
}

$conn->close();
?>