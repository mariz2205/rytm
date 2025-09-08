async function updateCart(action, id = 0, qty = 1) {
  let formData = new FormData();
  formData.append("action", action);
  if (id) formData.append("id", id);
  if (qty) formData.append("qty", qty);

  const res = await fetch("cart_api.php", {
    method: "POST",
    body: formData
  });
  const data = await res.json();

  renderCart(data);
}

function renderCart(data) {
  const cartList = document.getElementById("cart-items");
  cartList.innerHTML = "";

  if (!data.items || data.items.length === 0) {
    cartList.innerHTML = "<li>Your cart is empty.</li>";
  } else {
    data.items.forEach(item => {
      const li = document.createElement("li");
      li.innerHTML = `
        ${item.name} - ₱${item.price} × ${item.qty} = ₱${item.subtotal}
        <button onclick="updateCart('minus', ${item.id}, ${item.qty - 1})">-</button>
        <button onclick="updateCart('plus', ${item.id}, ${item.qty + 1})">+</button>
        <button onclick="updateCart('remove', ${item.id})">Remove</button>
      `;
      cartList.appendChild(li);
    });
  }

  document.getElementById("cart-total").textContent = data.total;
}

// Initial load
updateCart("none");
