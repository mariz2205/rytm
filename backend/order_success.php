<?php
session_start();

if (!isset($_SESSION['last_order']) || empty($_SESSION['last_order'])) {
    header("Location: index.php");
    exit;
}

$order = $_SESSION['last_order'];
$orderItems = $order['items'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Order Success</title>
</head>
<body>
  <h2>Thank you for your purchase!</h2>

  <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
  <p><strong>Date:</strong> <?= htmlspecialchars($order['date']) ?></p>

  <h3>Items:</h3>
  <ul>
    <?php foreach ($orderItems as $item): ?>
      <li>
        <?= htmlspecialchars($item['product_name']) ?> × <?= $item['qty'] ?>
        = ₱<?= number_format($item['total'], 2) ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <p><strong>Total:</strong> ₱<?= number_format($order['grand_total'], 2) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

  <a href="index.php">← Back to Home</a>
</body>
</html>
