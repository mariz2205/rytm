<?php
session_start();
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$products = json_decode(file_get_contents("products.json"), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mini E-Commerce</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="sidebar.css">
</head>
<body>

<header>
  <h1>RYTM</h1>
</header>

<aside class="sidebar">
  <div class="sidebar-content">

    <div class="sidebar-profile">
      <img src="assets/avatar.png" alt="User Avatar" class="sidebar-avatar">
      <?php if (isset($_SESSION['email'])): ?>
        <div class="sidebar-user-status">
          <strong><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['email'])?></strong>
          <small><?= htmlspecialchars($_SESSION['email'])?></small>
        </div>
      <?php else: ?>
        <div class="sidebar-user-status">
          <strong>Guest</strong>
          <small>Not Logged In</small>
        </div>
      <?php endif; ?>
    </div>

    <hr>

    <nav class="sidebar-nav">
      <a href="index.php" class="sidebar-link">Home</a>
      <a href="orders.php" class="sidebar-link">Orders</a>
      <a href="cart.php" class="sidebar-link">CART</a>
    </nav>
    
    <div class="cart-panel" id="cartPanel" style="display:none;">
      <h3>Your Cart</h3>
      <ul id="cart-items"></ul>
      <p>Total: ₱<span id="total">0</span></p>
    </div>

    <?php if (isset($_SESSION['email'])): ?>
          <a href="logout.php" class="sidebar-link logout-btn">Logout</a>
        <?php else: ?>
          <a href="login.php" class="sidebar-link login-btn">Login / Sign Up</a>
        <?php endif; ?>

  </div>
</aside>

<main>
  <section class="filter-bar">
    <label for="category-select">Filter by Category:</label>
    <select id="category-select">
      <option value="all">All</option>
      <option value="acoustic">Acoustic</option>
      <option value="electric">Electric</option>
      <option value="ukulele">Ukulele</option>
      <option value="capo">Capo</option>
      <option value="pick">Pick</option>
    </select>
  </section>

  <div class="products" id="products-container"></div>
</main>

<div id="productModal" class="modal">
  <img id="modalImage" src="" alt="">
  <h3 id="modalTitle"></h3>
  <p id="modalPrice"></p>
  <p id="modalDescription"></p>
  <div>
  </div>
  <button class="modal-close" onclick="modal.style.display='none'">Close</button>
</div>

<script src="script.js"></script>
</body>
</html>
