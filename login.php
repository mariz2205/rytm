<?php
session_start();

$product_id = $_GET['product_id'] ?? null;
$quantity = $_GET['quantity'] ?? 1;

$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    foreach ($users as $user) {
        if (($user['email'] === $username || $user['fullname'] === $username) && $user['password'] === $password) {
            $_SESSION["email"] = $user['email'];
            $_SESSION["fullname"] = $user['fullname'];

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
    $error = "Invalid username or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="login_style.css">
</head>
<body>
<div class="login-container">
  <h2>Login</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form action="login.php?product_id=<?= $product_id ?>&quantity=<?= $quantity ?>" method="POST">
    <input type="text" name="username" placeholder="Email or Full Name" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="hidden" name="product_id" value="<?= $product_id ?>">
    <input type="hidden" name="quantity" value="<?= $quantity ?>">
    <button type="submit">Log In</button>
  </form>

  <a class="signup-link" href="signup.php?product_id=<?= $product_id ?>&quantity=<?= $quantity ?>">Don't have an account? Sign Up</a>
</div>
</body>
</html>
