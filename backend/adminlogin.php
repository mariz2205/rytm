<?php
session_start();
include "db.php";

header('Content-Type: application/json');
$response = ["success" => false, "message" => "", "redirect" => ""];

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $adminid = $_POST['adminid'] ??  '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($adminid) || empty($username) || empty($password)) {
        $response["message"] = "ID, USername and Password are required.";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT SellerID, SellerUsername, SellerName, SellerPassword FROM sellerinfo WHERE SellerID = ? AND SellerUsername = ?");
    $stmt->bind_param("ss", $adminid, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Secure password verification
        if (password_verify($password, $user['SellerPassword'])) {
            $_SESSION['sellerinfo'] = [
                "sellerid" => $user["SellerID"],
                "sellername" => $user["SellerName"],
                "sellerusername" => $user["SellerUsername"]
            ];

            $response["success"] = true;
            $response["redirect"] = "../frontend/admin.html";
        } else {
            $response["message"] = "Invalid password.";
        }
    } else {
        $response["message"] = "No account found with that ID and username.";
    }
}

echo json_encode($response);