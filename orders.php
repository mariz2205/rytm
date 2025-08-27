<?php
session_start();

//load orders from JSON
$ordersFile = "orders.json";
$orders = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) : [];

//load products from JSON
$productsFile = "products.json";
$products = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) : [];

//delivery status
function getDeliveryStatus($created_at) {
    $orderDate = DateTime::createFromFormat('Y-m-d H:i:s', $created_at);
    if (!$orderDate) $orderDate = new DateTime(); 
    $today = new DateTime();
    $daysPassed = $orderDate->diff($today)->days + 1;

    if ($daysPassed == 1) return "Order Placed";
    if ($daysPassed == 2) return "In Transit";
    return "Delivered";
}

function getValue($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders</title>
<link rel="stylesheet" href="index.css"> 
<link rel="stylesheet" href="orders.css"> 
</head>
<body>

<div class="sidebar">
    <h3>Menu</h3>
    <a href="index.php" class="sidebar-link">Back to Home</a>
    <a href="cart.php" class="sidebar-link">Cart</a>
</div>

<header>
    <h1>My Orders</h1>
</header>

<main>
    <?php
    $validOrders = array_filter($orders, function($o) {
    return isset($o['order_id']) &&
           !empty($o['items']) &&
           is_array($o['items']) &&
           isset($_SESSION['user_id']) &&
           isset($o['user_id']) &&
           $o['user_id'] == $_SESSION['user_id'];
});

    ?>

    <?php if (empty($validOrders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <?php foreach ($validOrders as $order):
            $orderId   = getValue($order, 'order_id', 'N/A');
            $createdAt = getValue($order, 'date', date('Y-m-d H:i:s'));
            $total     = getValue($order, 'grand_total', 0);
            $items     = getValue($order, 'items', []);
        ?>
            <div class="order">
                <h3>Order ID: <?= htmlspecialchars($orderId) ?></h3>
                <small>Placed on: <?= htmlspecialchars($createdAt) ?></small>

                <div class="order-items">
                    <?php foreach ($items as $item):
                        $productName = getValue($item, 'product_name', 'Unknown');
                        $qty         = getValue($item, 'qty', 1);
                        $itemTotal   = getValue($item, 'total', 0);
                        $productId   = getValue($item, 'product_id', 0);

                        $productData = null;
                        foreach ($products as $p) {
                            if (getValue($p, 'id') == $productId) {
                                $productData = $p;
                                break;
                            }
                        }
                        $productImg = $productData ? getValue($productData, 'img', '') : '';
                    ?>
                        <div class="item">
                            <?php if ($productImg): ?>
                                <img src="<?= htmlspecialchars($productImg) ?>" alt="<?= htmlspecialchars($productName) ?>">
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($productName) ?></strong><br>
                            Qty: <?= $qty ?><br>
                            ₱<?= number_format($itemTotal, 2) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p><strong>Total:</strong> ₱<?= number_format($total, 2) ?></p>
                <p class="status">Status: <?= getDeliveryStatus($createdAt) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

</body>
</html>
