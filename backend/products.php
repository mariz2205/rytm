<?php
header("Content-Type: application/json; charset=UTF-8");

include "db.php";

$sql = "SELECT * FROM productdetails";
$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imagePath = $row['Image'] ? "img/products/" . $row['Image'] : "assets/images/default.png";

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
}

error_log(print_r($products, true));

echo json_encode($products);
?>
