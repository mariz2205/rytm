<?php
session_start();

$id = intval($_GET['id'] ?? 0);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($id > 0) {
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 0;
    }
    $_SESSION['cart'][$id]++;
}

$cart_count = array_sum($_SESSION['cart']);

header('Content-Type: application/json');
echo json_encode(["cart_count" => $cart_count]);
