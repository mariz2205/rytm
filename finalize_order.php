<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$jsonPath = __DIR__ . '/products.json';
if (!file_exists($jsonPath)) {
    http_response_code(500);
    die("Products file missing.");
}
$products = json_decode(file_get_contents($jsonPath), true);
if (!is_array($products)) $products = [];

$ordersFile = __DIR__ . '/orders.json';
if (!file_exists($ordersFile)) file_put_contents($ordersFile, json_encode([]));
$allOrders = json_decode(file_get_contents($ordersFile), true);
if (!is_array($allOrders)) $allOrders = [];

$_SESSION['last_order'] = [];
$orderId = uniqid("ORD");

//buy Now
if (isset($_POST['id'], $_POST['qty']) && !isset($_POST['cart_checkout'])) {
    $id  = (int)$_POST['id'];
    $qty = max(1, (int)$_POST['qty']);

    $product = null;
    foreach ($products as $p) {
        if ((int)$p['id'] === $id) {
            $product = $p;
            break;
        }
    }
    if (!$product) die("Product not found.");

    $orderItem = [
        'product_id'   => $id,
        'product_name' => $product['name'],
        'unit_price'   => (float)$product['price'],
        'qty'          => $qty,
        'total'        => (float)$product['price'] * $qty
    ];

    $newOrder = [
        'order_id' => $orderId,
        'email'    => $_SESSION['email'],
        'items'    => [$orderItem],
        'grand_total' => $orderItem['total'],
        'status'   => 'Order Placed',
        'date'     => date('Y-m-d H:i:s')
    ];

    $_SESSION['last_order'] = $newOrder;
    $allOrders[] = $newOrder;

    //clear cart if buy Now
    unset($_SESSION['cart']);

//cart Checkout
} elseif (isset($_POST['cart_checkout'], $_POST['qty'], $_POST['selected'])) {
    $cartQtys   = $_POST['qty'];
    $cartSelect = $_POST['selected'];
    $orderItems = [];
    $grandTotal = 0;

    foreach ($cartSelect as $id => $val) {
        $id  = (int)$id;
        $qty = isset($cartQtys[$id]) ? max(1, (int)$cartQtys[$id]) : 1;

        $product = null;
        foreach ($products as $p) {
            if ((int)$p['id'] === $id) {
                $product = $p;
                break;
            }
        }
        if (!$product) continue;

        $orderItem = [
            'product_id'   => $id,
            'product_name' => $product['name'],
            'unit_price'   => (float)$product['price'],
            'qty'          => $qty,
            'total'        => (float)$product['price'] * $qty
        ];
        $orderItems[] = $orderItem;
        $grandTotal += $orderItem['total'];

        // Remove purchased items from session cart
        if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
    }

    if (empty($orderItems)) {
        die("No valid items selected for checkout.");
    }

    $newOrder = [
        'order_id' => $orderId,
        'email'    => $_SESSION['email'],
        'items'    => $orderItems,
        'grand_total' => $grandTotal,
        'status'   => 'Order Placed',
        'date'     => date('Y-m-d H:i:s')
    ];

    $_SESSION['last_order'] = $newOrder;
    $allOrders[] = $newOrder;

} else {
    http_response_code(400);
    die("Invalid checkout request.");
}

//save orders
file_put_contents($ordersFile, json_encode($allOrders, JSON_PRETTY_PRINT));

header("Location: order_success.php?id=" . $orderId);
exit;
?>
