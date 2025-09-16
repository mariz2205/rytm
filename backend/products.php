<?php
header("Content-Type: application/json; charset=UTF-8");
include "db.php";

$category = isset($_GET['category']) ? trim($_GET['category']) : "";

if ($category === "" || strtolower($category) === "all") {
    $stmt = $conn->prepare("SELECT * FROM productdetails");
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM productdetails WHERE Category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $imagePath = $row['Image'] ? "img/products/" . $row['Image'] : "images/products/default.png";
    $products[] = [
        "id" => $row["ProductID"],
        "name" => $row["ProductName"],
        "description" => $row["ProductDescription"],
        "image" => $imagePath,
        "category" => $row["Category"],
        "stock" => $row["Stock"],
        "price" => $row["ProductPrice"],
        "seller" => $row["SellerID"],
    ];
}

echo json_encode($products);
?>
