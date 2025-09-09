async function loadCheckout() {
  const selected = JSON.parse(sessionStorage.getItem("checkoutSelected") || "{}");

  if (!Object.keys(selected).length) {
    alert("No products selected for checkout.");
    return;
  }

  const formData = new FormData();
  for (const id in selected) {
    formData.append(`selected[${id}]`, 1);
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
      <td data-label="Product"><img src="../img/products/${item.image}" alt="${item.name}"></td>
      <td data-label="Name">${item.name}</td>
      <td data-label="Description">${item.description}</td>
      <td data-label="Quantity">${item.qty}</td>
      <td data-label="Price">₱${item.price}</td>
      <td data-label="Subtotal">₱${item.subtotal}</td>
    `;
    tableBody.appendChild(tr);
    total += item.subtotal;
  });

  totalEl.textContent = total.toFixed(2);
}

// Load checkout items on page load
document.addEventListener("DOMContentLoaded", loadCheckout);

// Confirm order button
document.getElementById("confirm-btn").addEventListener("click", () => {
  const selected = JSON.parse(sessionStorage.getItem("checkoutSelected") || "{}");
  if (!Object.keys(selected).length) {
    alert("No products selected for checkout.");
    return;
  }

  // Dynamically create form to submit to finalize_order.php
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../backend/finalize_order.php";

  // Add hidden inputs for selected items and their quantities
  for (const id in selected) {
    const inputSelected = document.createElement("input");
    inputSelected.type = "hidden";
    inputSelected.name = `selected[${id}]`;
    inputSelected.value = "1";
    form.appendChild(inputSelected);

    const qtyInput = document.createElement("input");
    qtyInput.type = "hidden";
    qtyInput.name = `qty[${id}]`;
    qtyInput.value = selected[id]; // quantity stored in sessionStorage
    form.appendChild(qtyInput);
  }

  document.body.appendChild(form);
  sessionStorage.removeItem("checkoutSelected");
  form.submit();
});
