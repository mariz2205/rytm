<?php
session_start();

//redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$products = json_decode(file_get_contents("products.json"), true);

$cart = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected'], $_POST['qty'])) {
    $cart = [];
    foreach ($_POST['selected'] as $id => $val) {
        $id  = (int) $id;
        $qty = isset($_POST['qty'][$id]) ? max(1, (int)$_POST['qty'][$id]) : 1;
        $cart[$id] = $qty;
    }
}

//buy Now (single product)
$productId = (int)($_GET['id'] ?? 0);
$qty       = max(1, (int)($_GET['qty'] ?? 1));
$directProduct = null;

if ($productId) {
    foreach ($products as $p) {
        if ((int)$p['id'] === $productId) {
            $directProduct = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - RYTM</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="checkout.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-content">
        <div class="sidebar-profile">
            <div class="sidebar-user-status">
                <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>
                Online
            </div>
        </div>
        <a href="index.php" class="sidebar-link">Home</a>
        <a href="orders.php" class="sidebar-link">Orders</a>
        <a href="cart.php" class="sidebar-link">Cart</a>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</div>

<header>
    <h1>RYTM</h1>
</header>

<main>
<section class="checkout-section">
    <h2>Checkout</h2>

    <?php if ($directProduct): ?>
        <div id="checkout-summary"> 
            <p><strong>Product:</strong> <?= htmlspecialchars($directProduct['name']) ?></p>
            <p><strong>Price:</strong> ₱<?= number_format($directProduct['price'], 2) ?></p>

            <div style="margin:10px 0;">
                <button type="button" id="minus-btn">-</button>
                <input type="number" id="qty-input" value="<?= $qty ?>" min="1">
                <button type="button" id="plus-btn">+</button>
            </div>

            <p><strong>Total:</strong> ₱<span id="checkout-total"><?= $directProduct['price'] * $qty ?></span></p>
        </div>

        <form method="POST" action="finalize_order.php">
            <input type="hidden" name="id" value="<?= $directProduct['id'] ?>">
            <input type="hidden" name="qty" id="checkout-qty-input" value="<?= $qty ?>">
            <button type="submit">Confirm Purchase</button>
        </form>

    <?php elseif (!empty($cart)): ?>
        <form method="POST" action="finalize_order.php">
            <input type="hidden" name="cart_checkout" value="1">
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Select</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
                <?php
                $grandTotal = 0;
                foreach ($cart as $id => $quantity):
                    foreach ($products as $p) {
                        if ((int)$p['id'] === (int)$id):
                            $subtotal = $p['price'] * $quantity;
                            $grandTotal += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected[<?= $id ?>]" value="1" checked class="checkout-item">
                                </td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td>₱<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <button type="button" onclick="updateQty(<?= $id ?>, -1)">-</button>
                                    <input type="number" 
                                           name="qty[<?= $id ?>]" 
                                           id="qty-<?= $id ?>" 
                                           value="<?= $quantity ?>" 
                                           min="1">
                                    <button type="button" onclick="updateQty(<?= $id ?>, 1)">+</button>
                                </td>
                                <td id="subtotal-<?= $id ?>" data-price="<?= $p['price'] ?>">
                                    ₱<?= number_format($subtotal, 2) ?>
                                </td>
                            </tr>
                        <?php
                        endif;
                    }
                endforeach; ?>
                <tr>
                    <td colspan="4" align="right"><strong>Total:</strong></td>
                    <td><strong id="grand-total">₱<?= number_format($grandTotal, 2) ?></strong></td>
                </tr>
            </table>
            <button type="submit">Confirm Order</button>
        </form>

        <script>
        function updateQty(id, change) {
            let input = document.getElementById("qty-" + id);
            let subtotalCell = document.getElementById("subtotal-" + id);
            let grandTotalCell = document.getElementById("grand-total");

            let price = parseFloat(subtotalCell.getAttribute("data-price"));
            let qty = parseInt(input.value) + change;
            if (qty < 1) qty = 1;
            input.value = qty;

            //subtotal
            let subtotal = qty * price;
            subtotalCell.textContent = "₱" + subtotal.toFixed(2);

            //grand total
            let subtotals = document.querySelectorAll("[id^='subtotal-']");
            let grand = 0;
            subtotals.forEach(td => {
                let val = parseFloat(td.textContent.replace(/[^\d.]/g, '')) || 0;
                let checkbox = td.parentElement.querySelector(".checkout-item");
                if (checkbox && checkbox.checked) grand += val;
            });
            grandTotalCell.textContent = "₱" + grand.toFixed(2);
        }

        //grand total if checkboxes change
        document.querySelectorAll(".checkout-item").forEach(cb => {
            cb.addEventListener("change", () => {
                updateGrandTotal();
            });
        });

        function updateGrandTotal() {
            let subtotals = document.querySelectorAll("[id^='subtotal-']");
            let grand = 0;
            subtotals.forEach(td => {
                let checkbox = td.parentElement.querySelector(".checkout-item");
                if (checkbox && checkbox.checked) {
                    let val = parseFloat(td.textContent.replace(/[^\d.]/g, '')) || 0;
                    grand += val;
                }
            });
            document.getElementById("grand-total").textContent = "₱" + grand.toFixed(2);
        }
        </script>

    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>

    <button onclick="window.location.href='index.php'" class="btn-secondary">← Back to Home</button>
</section>
</main>

<?php if ($directProduct): ?>
<script>
const productPrice = <?= $directProduct['price'] ?>;
const qtyInput = document.getElementById("qty-input");
const checkoutTotal = document.getElementById("checkout-total");
const qtyHidden = document.getElementById("checkout-qty-input");

function updateTotal() {
    let currentQty = parseInt(qtyInput.value);
    if (isNaN(currentQty) || currentQty < 1) currentQty = 1;
    qtyInput.value = currentQty;
    checkoutTotal.textContent = (productPrice * currentQty).toFixed(2);
    qtyHidden.value = currentQty;
}

document.getElementById("plus-btn").addEventListener("click", () => {
    qtyInput.value = parseInt(qtyInput.value) + 1;
    updateTotal();
});
document.getElementById("minus-btn").addEventListener("click", () => {
    if (parseInt(qtyInput.value) > 1) {
        qtyInput.value = parseInt(qtyInput.value) - 1;
        updateTotal();
    }
});
qtyInput.addEventListener("input", updateTotal);
</script>
<?php endif; ?>
</body>
</html>
