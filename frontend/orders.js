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

<<<<<<< HEAD
      // Group products by OrderID
      const grouped = {};
=======
      // Loop through each order
>>>>>>> 4cd1c6b8688da4f9996cc4e8715f2a098a045e2a
      data.orders.forEach(order => {
        if (!grouped[order.OrderID]) {
          grouped[order.OrderID] = {
            info: order, // order header details
            items: []
          };
        }
        grouped[order.OrderID].items.push(order);
      });

      // Loop through grouped orders
      Object.values(grouped).forEach(group => {
        const orderInfo = group.info;

        const card = document.createElement("div");
        card.className = "order-card";

<<<<<<< HEAD
        // Order header
        let html = `
          <h2>Order #${orderInfo.OrderID}</h2>
          <p>Date: ${orderInfo.OrderDate}</p>
          <p>Status: ${orderInfo.OrderStatus}</p>
          <p>Delivery Date: ${orderInfo.DeliveryDate}</p>
          <ul class="order-items">
=======
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
>>>>>>> 4cd1c6b8688da4f9996cc4e8715f2a098a045e2a
        `;

        // Order items
        group.items.forEach(item => {
          html += `
            <li>
              alt="${item.ProductName}" width="60">
              ${item.ProductName} × ${item.OrderQuantity} = ₱${(item.ProductPrice * item.OrderQuantity).toLocaleString("en-PH", { minimumFractionDigits: 2 })}
            </li>
          `;
        });

        html += `</ul>
          <p><strong>Total Qty:</strong> ${orderInfo.TotalOrderQty}</p>
          <p><strong>Total Amount:</strong> ₱${Number(orderInfo.TotalAmount).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
        `;

        card.innerHTML = html;
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
