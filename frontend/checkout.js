async function loadCheckout() {
  const urlParams = new URLSearchParams(window.location.search);
  const buyNowId = urlParams.get("buy_now");
  const buyNowQty = urlParams.get("qty") || 1;

  let formData;

  if (buyNowId) {
    // Direct buy now
    formData = new FormData();
    formData.append("buy_now", buyNowId);
    formData.append("qty", buyNowQty);
  } else {
    // Cart checkout
    const selected = JSON.parse(sessionStorage.getItem("checkoutSelected") || "{}");
    if (!Object.keys(selected).length) {
      alert("No products selected for checkout.");
      return;
    }
    formData = new FormData();
    for (const id in selected) {
      formData.append(`selected[${id}]`, selected[id]);
    }
  }

  const res = await fetch("../backend/checkout.php", {
    method: "POST",
    body: formData
  });
  const data = await res.json();

  const tableBody = document.querySelector("#checkout-table tbody");
  const totalEl = document.getElementById("checkout-total");
  tableBody.innerHTML = "";

  if (!data.items || data.items.length === 0) {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td colspan="6">No products selected for checkout.</td>`;
    tableBody.appendChild(tr);
    totalEl.textContent = "0";
    return;
  }

  let total = 0;

  data.items.forEach(item => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><img src="../img/products/${item.image}" alt="${item.name}"></td>
      <td>${item.name}</td>
      <td>${item.description}</td>
      <td>${item.qty}</td>
      <td>₱${item.price}</td>
      <td>₱${item.subtotal}</td>
    `;
    tableBody.appendChild(tr);
    total += item.subtotal;
  });

  totalEl.textContent = total.toFixed(2);
}

document.addEventListener("DOMContentLoaded", loadCheckout);

// Confirm order button
document.getElementById("confirm-btn").addEventListener("click", async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const buyNowId = urlParams.get("buy_now");
  const buyNowQty = urlParams.get("qty") || 1;

  let payload;

  if (buyNowId) {
    // Buy Now flow
    payload = {
      buy_now: true,
      productId: buyNowId,
      qty: buyNowQty
    };
  } else {
    // Cart checkout flow
    const selected = JSON.parse(sessionStorage.getItem("checkoutSelected") || "{}");
    if (!Object.keys(selected).length) {
      alert("No products selected for checkout.");
      return;
    }
    payload = { selected: selected };
  }

  try {
    const res = await fetch("../backend/finalize_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
      sessionStorage.removeItem("checkoutSelected");
      window.location.href = `order_success.html?id=${data.orderId}`;
    } else {
      alert("Failed: " + data.error);
    }
  } catch (err) {
    console.error(err);
    alert("Something went wrong.");
  }
});
