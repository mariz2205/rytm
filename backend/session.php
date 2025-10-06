<?php
session_start();
header('Content-Type: application/json');

$response = [
    "loggedIn" => false
];


if (isset($_SESSION["CustomerID"])) {
    $response = [
        "loggedIn" => true,
        "type" => "customer",
        "fullname" => $_SESSION["fullname"],
        "email" => $_SESSION["email"]
    ];
}

elseif (isset($_SESSION["SellerID"])) {
    $response = [
        "loggedIn" => true,
        "type" => "seller",
        "sellername" => $_SESSION["SellerName"],
        "sellerusername" => $_SESSION["SellerUsername"]
    ];
}


echo json_encode($response);
?>
