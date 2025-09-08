

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

      // Loop through orders
      data.orders.forEach(order => {
        const card = document.createElement("div");
        card.className = "order-card";

        card.innerHTML = `
          <img src="../${order.Image}" alt="${order.ProductName}" width="100">
          <h3>${order.ProductName}</h3>
          <p>Quantity: ${order.OrderQuantity}</p>
          <p>Price: ₱${Number(order.ProductPrice).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
          <p>Order Date: ${order.OrderDate}</p>
        `;

        container.appendChild(card);
      });
    })
    .catch(err => {
      console.error("Orders fetch error:", err);
      document.getElementById("orders-container").textContent = "Error loading orders.";
    });
}

// Run on page load
document.addEventListener("DOMContentLoaded", fetchOrders);
