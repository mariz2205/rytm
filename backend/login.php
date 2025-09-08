<?php
session_start();
include "db.php";

header('Content-Type: application/json');
$response = ["success" => false, "message" => "", "redirect" => ""];

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response["message"] = "Email and password are required.";
        echo json_encode($response);
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT CustomerID, FirstName, LastName FROM customerdetails WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $stmt2 = $conn->prepare("SELECT user_pass FROM passwords WHERE CustomerID = ?");
        $stmt2->bind_param("i", $user['CustomerID']);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $pwdRow = $res2->fetch_assoc();

        if ($pwdRow && password_verify($password, $pwdRow['user_pass'])) {
            $_SESSION["CustomerID"] = $user["CustomerID"];
            $_SESSION["email"] = $email;
            $_SESSION["fullname"] = $user["FirstName"] . " " . $user["LastName"];

            $response["success"] = true;
            $response["redirect"] = "../frontend/index.html";
        } else {
            $response["message"] = "Invalid password.";
        }
    } else {
        $response["message"] = "No account found with that email.";
    }
}

echo json_encode($response);
