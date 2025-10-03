<?php
// Prevent PHP warnings/notices from breaking JSON output shown to frontend
ini_set('display_errors', '0');
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$method = $_SERVER["REQUEST_METHOD"];
if ($method === "OPTIONS") {
    http_response_code(200);
    exit;
}

include "db.php";

if ($method === "POST" && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
}

file_put_contents("php_errors.log", print_r($_SERVER, true) . "\n" . file_get_contents("php://input") . "\n\n", FILE_APPEND);

// Check if request is for products or orders
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

            case "POST": // Add product
                $name = $_POST["ProductName"];
                $desc = $_POST["ProductDescription"];
                $cat = $_POST["Category"];
                $stock = $_POST["Stock"];
                $price = $_POST["ProductPrice"];
                $seller = $_POST["SellerID"];

                // Handle image upload
                $image = $_POST["Image"] ?? "";
                if (isset($_FILES["ImageFile"]) && $_FILES["ImageFile"]["error"] === UPLOAD_ERR_OK) {
                    $image = basename($_FILES["ImageFile"]["name"]);
                    move_uploaded_file($_FILES["ImageFile"]["tmp_name"], "../img/products/" . $image);
                }

                $stmt = $conn->prepare("INSERT INTO productdetails (ProductName, ProductDescription, Image, Category, Stock, ProductPrice, SellerID) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssdds", $name, $desc, $image, $cat, $stock, $price, $seller);

                if ($stmt->execute()) {
                    echo json_encode(["success" => true, "id" => $stmt->insert_id]);
                } else {
                    echo json_encode(["success" => false, "error" => $stmt->error]);
                }
                break;

            case "PUT": // Update product
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

                $stmt = $conn->prepare("UPDATE productdetails SET ProductName=?, ProductDescription=?, Image=?, Category=?, Stock=?, ProductPrice=?, SellerID=? WHERE ProductID=?");
                $stmt->bind_param("ssssddsi", $name, $desc, $image, $cat, $stock, $price, $seller, $id);

                if ($stmt->execute()) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => $stmt->error]);
                }
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
                if ($stmt->execute()) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => $stmt->error]);
                }
                $stmt->close();
                break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Method not allowed"]);
        }
        break;

    case "orders":
        switch ($method) {
            case "GET":
                // Join orderlist + orderitems + productdetails + customerdetails
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

                // Deduct stock only when moving from Pending → Accepted
                if (strtolower($status) === "accepted") {
                    $conn->begin_transaction();
                    try {
                        $res = $conn->query("SELECT ProductID, ProdOrdQty FROM orderitems WHERE OrderID=" . intval($id));
                        while ($row = $res->fetch_assoc()) {
                            $pid = (int)$row["ProductID"];
                            $qty = (int)$row["ProdOrdQty"];

                            // Deduct stock safely
                            $stmt = $conn->prepare("UPDATE productdetails SET Stock = Stock - ? WHERE ProductID=? AND Stock >= ?");
                            $stmt->bind_param("iii", $qty, $pid, $qty);
                            $stmt->execute();

                            if ($stmt->affected_rows === 0) {
                                throw new Exception("Not enough stock for ProductID $pid");
                            }
                        }

                        // Update order status
                        $stmt = $conn->prepare("UPDATE orderlist SET OrderStatus=? WHERE OrderID=?");
                        $stmt->bind_param("si", $status, $id);
                        $stmt->execute();

                        $conn->commit();
                        echo json_encode(["success" => true]);
                    } catch (Exception $e) {
                        $conn->rollback();
                        echo json_encode(["success" => false, "error" => $e->getMessage()]);
                    }
                } else {
                    // For other statuses (Processing, Shipping, Delivered)
                    $stmt = $conn->prepare("UPDATE orderlist SET OrderStatus=? WHERE OrderID=?");
                    $stmt->bind_param("si", $status, $id);
                    if ($stmt->execute()) {
                        echo json_encode(["success" => true]);
                    } else {
                        echo json_encode(["success" => false, "error" => $stmt->error]);
                    }
                }
                break;

            case "DELETE":
                $input = json_decode(file_get_contents("php://input"), true);
                $id = $input["id"] ?? ($_GET["id"] ?? null);

                if (!$id) {
                    echo json_encode(["success" => false, "error" => "Missing OrderID"]);
                    break;
                }

                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("DELETE FROM orderitems WHERE OrderID=?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();

                    $stmt = $conn->prepare("DELETE FROM orderlist WHERE OrderID=?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();

                    $conn->commit();
                    echo json_encode(["success" => true]);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(["success" => false, "error" => $e->getMessage()]);
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
