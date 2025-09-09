async function updateCart(action, id = 0, qty = 1) {
  let formData = new FormData();
  formData.append("action", action);
  if (id) formData.append("id", id);
  if (qty) formData.append("qty", qty);

  const res = await fetch("../backend/cart.php", {
    method: "POST",
    body: formData
  });
  const data = await res.json();

  renderCart(data);
}

function renderCart(data) {
  const selectedState = {};
  document.querySelectorAll("#cart-items .checkout-item").forEach(cb => {
    selectedState[cb.dataset.id] = cb.checked;
  });

  const cartList = document.getElementById("cart-items");
  if (!cartList) return;

  cartList.innerHTML = "";

  if (!data.items || data.items.length === 0) {
    cartList.innerHTML = "<li>Your cart is empty.</li>";
    return;
  }

  data.items.forEach(item => {
    const li = document.createElement("li");
    li.innerHTML = `
      <input type="checkbox" class="checkout-item" data-id="${item.id}" 
        ${selectedState[item.id] !== undefined ? (selectedState[item.id] ? "checked" : "") : "checked"}>
      <img src="../img/products/${item.image}" alt="${item.name}" class="cart-img">
      <span>${item.name} - ₱${item.price} × ${item.qty} = ₱${item.subtotal}</span>
      <button onclick="updateCart('update', ${item.id}, ${item.qty - 1})">-</button>
      <button onclick="updateCart('update', ${item.id}, ${item.qty + 1})">+</button>
      <button onclick="updateCart('remove', ${item.id})">Remove</button>
    `;
    cartList.appendChild(li);
  });

  // Proceed to Checkout button
  const checkoutBtn = document.createElement("button");
  checkoutBtn.textContent = "Proceed to Checkout";
  checkoutBtn.className = "btn";
  checkoutBtn.onclick = () => {
    const selected = {};
    document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
      selected[cb.dataset.id] = 1;
    });

    if (!Object.keys(selected).length) {
      alert("Please select at least one product to checkout.");
      return;
    }

    // Save selected items to sessionStorage
    sessionStorage.setItem("checkoutSelected", JSON.stringify(selected));

    // Redirect to checkout.html
    window.location.href = "checkout.html";
  };

  cartList.appendChild(checkoutBtn);

  // Update total based only on checked items
  updateCartTotal();
}

function updateCartTotal() {
  const totalEl = document.getElementById("cart-total");
  if (!totalEl) return;

  let grand = 0;
  document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
    const span = cb.parentElement.querySelector("span");
    const match = span.textContent.match(/₱([\d.]+)/);
    if (match) grand += parseFloat(match[1]);
  });

  totalEl.textContent = "₱" + grand.toFixed(2);
}

// Initial load
updateCart("none");
