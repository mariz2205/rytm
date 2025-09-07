<?php
session_start();
include "db.php"; // brings in $conn

$product_id = $_POST['product_id'] ?? null;
$quantity   = $_POST['quantity'] ?? 1;

$response = ["success" => false, "message" => "", "redirect" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT c.CustomerID, c.Username, c.FirstName, c.LastName, c.Email, p.user_pass
            FROM customerdetails c
            JOIN passwords p ON c.CustomerID = p.CustomerID
            WHERE c.Username = ? OR c.Email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['user_pass'])) {
            // Set session
            $_SESSION["CustomerID"] = $row['CustomerID'];
            $_SESSION["username"]   = $row['Username'];
            $_SESSION["fullname"]   = $row['FirstName'] . " " . $row['LastName'];
            $_SESSION["email"]      = $row['Email'];

            $response["success"] = true;
            if (!empty($product_id)) {
                $response["redirect"] = "checkout.php?product_id=$product_id&quantity=$quantity";
            } else {
                $response["redirect"] = "index.html"; // redirect to main page
            }
        } else {
            $response["message"] = "Invalid username or password!";
        }
    } else {
        $response["message"] = "Invalid username or password!";
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    exit;
}
?>
