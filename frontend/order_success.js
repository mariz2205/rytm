document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get("id");

    if (!orderId) {
        document.body.innerHTML = "<p>Order ID missing.</p>";
        return;
    }

    try {
        const res = await fetch(`../backend/order_success.php?id=${orderId}`);
        const data = await res.json();

        if (data.error) {
            document.body.innerHTML = `<p>${data.error}</p>`;
            return;
        }

        const tableBody = document.querySelector("#order-items tbody");
        tableBody.innerHTML = "";

        let total = 0;
        data.items.forEach(item => {
            const qty = parseInt(item.OrderQuantity, 10);
            const price = parseFloat(item.ProductPrice);
            const subtotal = qty * price;
            total += subtotal;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><img src="../img/products/${item.Image}" alt="${item.ProductName}" width="80"></td>
                <td>${item.ProductName}</td>
                <td>${qty}</td>
                <td>₱${price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                <td>₱${subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
            `;
            tableBody.appendChild(tr);
        });

        const checkout = data.checkout;
        document.getElementById("order-id").textContent = orderId;
        document.getElementById("order-status").textContent = checkout.OrderStatus;
        document.getElementById("order-date").textContent = checkout.OrderDate;
        document.getElementById("delivery-date").textContent = checkout.DeliveryDate;
        document.getElementById("total-amount").textContent =
            "₱" + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    } catch (err) {
        console.error(err);
        document.body.innerHTML = "<p>Error loading order. Please try again later.</p>";
    }
});
