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
        let total = 0;
        tableBody.innerHTML = "";

        data.items.forEach(item => {
            const qty = parseInt(item.OrderQuantity, 10);
            const price = parseFloat(item.ProductPrice);
            const subtotal = qty * price;
            total += subtotal;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${item.ProductID}</td>
                <td>${item.ProductName}</td>
                <td>${qty}</td>
                <td>₱${price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                <td>₱${subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
            `;
            tableBody.appendChild(tr);
        });

        // Display total
        document.getElementById("total-amount").textContent =
            "₱" + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    } catch (err) {
        console.error(err);
        document.body.innerHTML = "<p>Error loading order. Please try again later.</p>";
    }
});
