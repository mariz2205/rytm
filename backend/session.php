<?php

session_start();
header('Content-Type: application/json');

if (isset($_SESSION["CustomerID"])) {
    echo json_encode([
        "loggedIn" => true,
        "fullname" => $_SESSION["fullname"],
        "email" => $_SESSION["email"]
    ]);
} else {
    echo json_encode(["loggedIn" => false]);
}
