document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get("id");

    if (!orderId) {
        document.body.innerHTML = "<p>Order ID missing.</p>";
        return;
    }

    try {
        const res = await fetch(`../backend/get_order.php?id=${orderId}`);
        const data = await res.json();

        if (data.error) {
            document.body.innerHTML = `<p>${data.error}</p>`;
            return;
        }

        const tableBody = document.querySelector("#order-items tbody");
        let total = 0;
        tableBody.innerHTML = "";

        data.items.forEach(item => {
            const subtotal = item.OrderQuantity * item.ProductPrice;
            total += subtotal;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${item.ProductID}</td>
                <td>${item.ProductName}</td>
                <td>${item.OrderQuantity}</td>
                <td>₱${item.ProductPrice}</td>
                <td>₱${subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</td>
                
            `;
            tableBody.appendChild(tr);
        });

        document.getElementById("total-amount").textContent = "₱" + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");


    } catch (err) {
        console.error(err);
        document.body.innerHTML = "<p>Error loading order. Please try again later.</p>";
    }
});
