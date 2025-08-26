<?php
session_start();
$products = json_decode(file_get_contents("products.json"), true);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = &$_SESSION['cart'];

function getProduct($products, $id) {
    foreach ($products as $p) {
        if ((int)$p['id'] === (int)$id) return $p;
    }
    return null;
}

//api
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    switch ($action) {
        case 'add':
            if ($id) {
                if (!isset($cart[$id])) $cart[$id] = 0;
                $cart[$id] += $qty;
            }
            break;

        case 'update':
            if ($id) {
                if ($qty > 0) $cart[$id] = $qty;
                else unset($cart[$id]);
            }
            break;

        case 'remove':
            if ($id && isset($cart[$id])) {
                unset($cart[$id]);
            }
            break;
    }

    //HTML for JS
    $output = "";
    $total = 0;
    foreach ($cart as $cid => $cqty) {
        $product = getProduct($products, $cid);
        if (!$product) continue;
        $subtotal = $product['price'] * $cqty;
        $total += $subtotal;

        $output .= "<li data-id='{$cid}' data-name='" . htmlspecialchars($product['name']) . "' data-price='{$product['price']}' data-qty='{$cqty}'>";
        $output .= "<input type='checkbox' class='checkout-item' value='{$cid}' checked> ";
        $output .= htmlspecialchars($product['name']) . " - ₱" . $product['price'];
        $output .= " × <span id='qty-{$cid}'>{$cqty}</span> ";
        $output .= "<button type='button' data-action='minus' data-id='{$cid}'>-</button>";
        $output .= "<button type='button' data-action='plus' data-id='{$cid}'>+</button>";
        $output .= "<button type='button' data-action='remove' data-id='{$cid}'>Remove</button>";
        $output .= "</li>";
    }

    if ($output === "") $output = "<li>Your cart is empty.</li>";
    $output .= "<li><strong>Total: ₱{$total}</strong></li>";
    echo $output;
    exit;
}

$total = 0;
foreach ($cart as $id => $qty) {
    $p = getProduct($products, $id);
    if ($p) $total += $p['price'] * $qty;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="cart.css">
<link rel="stylesheet" href="sidebar.css">
<script src="script.js" defer></script>
</head>
<body>

<header>
  <h1>RYTM</h1>
</header>

<div class="sidebar">
  <div class="sidebar-profile">
    <img src="avatar.png" alt="User Avatar" class="sidebar-avatar">
    <div class="sidebar-user-status"><strong>Anna Cruz</strong> Online</div>
  </div>

  <div class="sidebar-content">
    <a href="index.php" class="sidebar-link">Home</a>
    <a href="orders.php" class="sidebar-link">Orders</a>
    <a href="cart.php" class="sidebar-link">Cart</a>

    <div class="sidebar-actions">
      <button class="sidebar-button logout-btn">Logout</button>
    </div>
  </div>

  <div class="cart-panel">
    <h3>Cart</h3>
    <ul id="cart-items-sidebar">
    </ul>
  </div>
</div>

<main>
<h1>Your Cart</h1>

<?php if (empty($cart)): ?>
  <p>Your cart is empty.</p>
  <a href="index.php" class="btn-secondary">Continue Shopping</a>
<?php else: ?>
  <table class="cart-table">
    <tr>
      <th>Product</th>
      <th>Qty</th>
      <th>Price</th>
      <th>Subtotal</th>
      <th>Action</th>
    </tr>
    <?php foreach ($cart as $id => $qty): ?>
      <?php $product = getProduct($products, $id); ?>
      <?php if ($product): ?>
        <tr>
          <td><?= htmlspecialchars($product['name']) ?></td>
          <td>
            <button type="button" data-action="minus" data-id="<?= $id ?>">-</button>
            <span id="qty-<?= $id ?>"><?= $qty ?></span>
            <button type="button" data-action="plus" data-id="<?= $id ?>">+</button>
          </td>
          <td>₱<?= $product['price'] ?></td>
          <td id="subtotal-<?= $id ?>" data-price="<?= $product['price'] ?>">₱<?= $product['price'] * $qty ?></td>
          <td><button type="button" data-action="remove" data-id="<?= $id ?>">Remove</button></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </table>

  <h3>Total: ₱<span id="cart-total"><?= $total ?></span></h3>

  <form method="POST" action="checkout.php" id="cart-checkout-form">
    <?php foreach ($cart as $id => $qty): ?>
      <input type="hidden" name="qty[<?= $id ?>]" id="form-qty-<?= $id ?>" value="<?= $qty ?>">
      <input type="hidden" name="selected[<?= $id ?>]" id="form-sel-<?= $id ?>" value="1">
    <?php endforeach; ?>
    <button type="button" class="btn" onclick="submitSelectedItems()">Proceed to Checkout</button>
  </form>

  <button onclick="window.location.href='index.php'" class="btn-secondary">← Back to Home</button>
<?php endif; ?>
</main>

</body>
</html>
