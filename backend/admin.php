<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

//Handle preflight (CORS)
$method = $_SERVER["REQUEST_METHOD"];
if ($method === "OPTIONS") {
    http_response_code(200);
    exit;
}

include "db.php";

// Handle method override (for PUT via FormData)
if ($method === "POST" && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
}

file_put_contents("php_errors.log", print_r($_SERVER, true) . "\n" . file_get_contents("php://input") . "\n\n", FILE_APPEND);

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

$conn->close();
?>
