// Fetch ORDERS
function fetchOrders() {
  fetch("../backend/orders.php", { credentials: "include" })
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("orders-container");
      container.innerHTML = "";

      if (!data.success) {
        container.textContent = data.message || "Failed to load orders.";
        return;
      }

      if (data.orders.length === 0) {
        container.textContent = "You have no orders yet.";
        return;
      }

      // Loop through each order
      data.orders.forEach(order => {
        const card = document.createElement("div");
        card.className = "order-card";

        // Order info (from checkoutinfo)
        let itemsHTML = "";
        order.items.forEach(item => {
          const imgPath = `../img/products/${item.Image}`;
          itemsHTML += `
            <div class="order-item">
              <img src="${imgPath}" alt="${item.ProductName}" width="80">
              <div>
                <h4>${item.ProductName}</h4>
                <p>Quantity: ${item.OrderQuantity}</p>
                <p>Price: ₱${Number(item.ProductPrice).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
              </div>
            </div>
          `;
        });

        card.innerHTML = `
          <h3>Order #${order.OrderID}</h3>
          <p>Status: ${order.OrderStatus}</p>
          <p>Order Date: ${order.OrderDate}</p>
          <p>Delivery Date: ${order.DeliveryDate || "N/A"}</p>
          <p>Total: ₱${Number(order.TransactionAmount).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
          <div class="order-items">
            ${itemsHTML}
          </div>
        `;

        container.appendChild(card);
      });
    })
    .catch(err => {
      console.error("Orders fetch error:", err);
      document.getElementById("orders-container").textContent =
        "Error loading orders.";
    });
}

// Run on page load
document.addEventListener("DOMContentLoaded", fetchOrders);
