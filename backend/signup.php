<?php
session_start();
include "db.php";

header('Content-Type: application/json');

$response = ["success" => false, "message" => "", "redirect" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $firstName   = trim($_POST['first_name'] ?? '');
    $lastName    = trim($_POST['last_name'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone       = trim($_POST['phone_number'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $product_id  = $_POST['product_id'] ?? null;
    $quantity    = (int)($_POST['quantity'] ?? 1);

    // Check required fields
    if (!$firstName || !$lastName || !$username || !$email || !$password || !$confirm_password) {
        $response["message"] = "Please fill in all required fields.";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["message"] = "Invalid email format.";
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response["message"] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT Email FROM customerdetails WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response["message"] = "Email already registered.";
        echo json_encode($response);
        exit;
    }

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT Username FROM customerdetails WHERE Username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        $response["message"] = "Username already taken.";
        echo json_encode($response);
        exit;
    }

    // Insert customer
    $stmt = $conn->prepare("
        INSERT INTO customerdetails (Username, LastName, FirstName, Email, CustomerAddress, ContactNo) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $username, $lastName, $firstName, $email, $address, $phone);

    if ($stmt->execute()) {
        $customerId = $stmt->insert_id;

        // Insert password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("INSERT INTO passwords (CustomerID, user_pass) VALUES (?, ?)");
        $stmt2->bind_param("is", $customerId, $hashed_password);

        if ($stmt2->execute()) {
            // Set session
            $_SESSION["CustomerID"] = $customerId;
            $_SESSION["email"] = $email;
            $_SESSION["fullname"] = $firstName . " " . $lastName;

            $response["success"] = true;
            $response["redirect"] = !empty($product_id)
                ? "checkout.php?product_id=$product_id&quantity=$quantity"
                : "index.html";
        } else {
            $response["message"] = "Error saving password: " . $conn->error;
        }

        $stmt2->close();
    } else {
        $response["message"] = "Database error: " . $conn->error;
    }

    $stmt->close();
}

echo json_encode($response);
