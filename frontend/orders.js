function fetchOrders() {
  fetch("../backend/orders.php", { credentials: "include" })
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("orders-container");
      container.innerHTML = "";

      if (!data.success || !data.orders.length) {
        container.textContent = data.message || "You have no orders yet.";
        return;
      }

      data.orders.forEach(order => {
        const card = document.createElement("div");
        card.className = "order-card";

        let itemsHTML = "";
        order.items.forEach(item => {
          itemsHTML += `
            <div class="order-item">
              <img src="../img/products/${item.Image}" alt="${item.ProductName}" width="80">
              <div>
                <h4>${item.ProductName}</h4>
                <p>Quantity: ${item.OrderQuantity}</p>
                <p>Price: ₱${Number(item.ProductPrice).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
                <p>Subtotal: ₱${(item.ProductPrice * item.OrderQuantity).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
              </div>
            </div>
          `;
        });

        // Determine button display
        let receiveBtnHTML = "";
        if (order.OrderStatus.toLowerCase() === "delivered") {
          receiveBtnHTML = `
            <button class="receive-btn" data-order-id="${order.OrderID}">
              Receive
            </button>
          `;
        } else if (order.OrderStatus.toLowerCase() === "received") {
          receiveBtnHTML = `
            <button class="receive-btn received" disabled>
              Received
            </button>
          `;
        }

        card.innerHTML = `
          <h3>Order #${order.OrderID}</h3>
          <p>Status: ${order.OrderStatus}</p>
          <p>Order Date: ${order.OrderDate}</p>
          <p>Delivery Date: ${order.DeliveryDate || "N/A"}</p>
          <p><strong>Total Qty:</strong> ${order.TotalOrderQty}</p>
          <p><strong>Total Amount:</strong> ₱${Number(order.TotalAmount).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
          <div class="order-items">${itemsHTML}</div>
          <div class="order-actions">${receiveBtnHTML}</div>
        `;

        container.appendChild(card);
      });

      // Add event listener for receive button
      document.querySelectorAll(".receive-btn").forEach(btn => {
        btn.addEventListener("click", e => {
          const orderId = e.target.getAttribute("data-order-id");
          if (confirm(`Mark Order #${orderId} as received?`)) {
            receiveOrder(orderId);
          }
        });
      });
    })
    .catch(err => {
      console.error("Orders fetch error:", err);
      document.getElementById("orders-container").textContent = "Error loading orders.";
    });
}

function receiveOrder(orderId) {
  fetch("../backend/received_update.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ OrderID: orderId }),
    credentials: "include"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        const btn = document.querySelector(`.receive-btn[data-order-id="${orderId}"]`);
        if (btn) {
          btn.textContent = "Received";
          btn.disabled = true;
          btn.classList.add("received");
        }
      }
    })
    .catch(err => {
      console.error("Receive order error:", err);
      alert("Error updating order status.");
    });
}

document.addEventListener("DOMContentLoaded", fetchOrders);
