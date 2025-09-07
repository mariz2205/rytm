<?php
session_start();
include "db.php";

header('Content-Type: application/json');

$response = ["success" => false, "message" => "", "redirect" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName   = $_POST['first_name'] ?? '';
    $lastName    = $_POST['last_name'] ?? '';
    $username    = $_POST['username'] ?? '';
    $email       = $_POST['email'] ?? '';
    $password    = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone       = $_POST['phone_number'] ?? '';
    $address     = $_POST['address'] ?? '';
    $product_id  = $_POST['product_id'] ?? null;
    $quantity    = $_POST['quantity'] ?? 1;

    if ($password !== $confirm_password) {
        $response["message"] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    //check if email exists
    $check = $conn->prepare("SELECT Email FROM customerdetails WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response["message"] = "Email already registered.";
        echo json_encode($response);
        exit;
    }

    //Insert into customerdetails (using AddressCode=1 for now)
    $addressCode = 1;
    $stmt = $conn->prepare("INSERT INTO customerdetails (Username, LastName, FirstName, AddressCode, Email, `ContactNo.`) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $username, $lastName, $firstName, $addressCode, $email, $phone);

    if ($stmt->execute()) {
        $customerId = $stmt->insert_id;

        //Insert password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("INSERT INTO passwords (CustomerID, user_pass) VALUES (?, ?)");
        $stmt2->bind_param("is", $customerId, $hashed_password);
        $stmt2->execute();

        $_SESSION["email"] = $email;
        $_SESSION["fullname"] = $firstName . " " . $lastName;

        $response["success"] = true;
        $response["redirect"] = !empty($product_id)
            ? "checkout.php?product_id=$product_id&quantity=$quantity"
            : "index.php";
    } else {
        $response["message"] = "Database error: " . $conn->error;
    }
}

echo json_encode($response);
