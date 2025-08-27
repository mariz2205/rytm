<?php
session_start();
$product_id = $_GET['product_id'] ?? null;
$quantity = $_GET['quantity'] ?? 1;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $users = file_exists("users.json") ? json_decode(file_get_contents("users.json"), true) : [];
        foreach ($users as $user) {
            if ($user["email"] === $email) {
                $error = "Email already registered.";
                break;
            }
        }

        if (empty($error)) {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $users[] = [
                "fullname" => $fullname,
                "username" => $username,
                "email" => $email,
                "password" => $hashed_password, // Store the hashed password
                "phone" => $phone,
                "address" => $address
            ];
            file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT));
            $_SESSION["email"] = $email;
            $_SESSION["fullname"] = $fullname;

            if (!empty($_POST['product_id'])) {
                $pid = $_POST['product_id'];
                $qty = $_POST['quantity'];
                header("Location: checkout.php?product_id=$pid&quantity=$qty");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Create an Account</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="signup.php?product_id=<?= $product_id ?>&quantity=<?= $quantity ?>">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="number" name="phone_number" placeholder="Phone Number" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" name="quantity" value="<?= $quantity ?>">
            <button type="submit">Sign Up</button>
        </form>
        <a href="login.php?product_id=<?= $product_id ?>&quantity=<?= $quantity ?>" class="login-link">Already have an account? Log In</a>
    </div>
</body>
</html>