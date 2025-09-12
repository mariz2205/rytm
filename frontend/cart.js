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
  const cartList = document.getElementById("cart-items");
  if (!cartList) return;

  // save previously checked items
  const prevSelected = JSON.parse(sessionStorage.getItem("cartSelected") || "{}");

  cartList.innerHTML = "";

  if (!data.items || data.items.length === 0) {
    cartList.innerHTML = "<li>Your cart is empty.</li>";
    document.getElementById("cart-total").textContent = "₱0.00";
    return;
  }

  data.items.forEach(item => {
    const li = document.createElement("li");
    li.className = "cart-item";

    // Checkbox
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "checkout-item";
    checkbox.dataset.id = item.id;
    checkbox.checked = prevSelected[item.id] !== undefined ? prevSelected[item.id] : true;
    checkbox.addEventListener("change", updateCartTotal);

    // Image
    const img = document.createElement("img");
    img.src = `../img/products/${item.image}`;
    img.alt = item.name;
    img.className = "cart-img";

    // Description & subtotal
    const span = document.createElement("span");
    span.textContent = `${item.name} - ₱${item.price} × ${item.qty} = ₱${item.subtotal}`;

    // Quantity buttons
    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.addEventListener("click", () => {
      if (item.qty > 1) updateCart("update", item.id, item.qty - 1);
    });

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.addEventListener("click", () => {
      updateCart("update", item.id, item.qty + 1);
    });

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "Remove";
    removeBtn.addEventListener("click", () => updateCart("remove", item.id));

    li.appendChild(checkbox);
    li.appendChild(img);
    li.appendChild(span);
    li.appendChild(minusBtn);
    li.appendChild(plusBtn);
    li.appendChild(removeBtn);

    cartList.appendChild(li);
  });

  // Save checkbox state to sessionStorage
  document.querySelectorAll("#cart-items .checkout-item").forEach(cb => {
    cb.addEventListener("change", () => {
      const selected = {};
      document.querySelectorAll("#cart-items .checkout-item").forEach(c => {
        selected[c.dataset.id] = c.checked;
      });
      sessionStorage.setItem("cartSelected", JSON.stringify(selected));
      updateCartTotal();
    });
  });

  updateCartTotal();
}

// Total calculation
function updateCartTotal() {
  const totalEl = document.getElementById("cart-total");
  if (!totalEl) return;

  let grand = 0;
  document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
    const span = cb.parentElement.querySelector("span");
    const match = span.textContent.match(/= ₱([\d.,]+)/);
    if (match) grand += parseFloat(match[1].replace(/,/g, ""));
  });

  totalEl.textContent = "₱" + grand.toFixed(2);
}

// Attach checkout button event once
document.addEventListener("DOMContentLoaded", () => {
  const checkoutBtn = document.getElementById("checkoutBtn");
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      const selected = {};
      document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
        selected[cb.dataset.id] = 1;
      });

      if (!Object.keys(selected).length) {
        alert("Please select at least one product to checkout.");
        return;
      }

      sessionStorage.setItem("checkoutSelected", JSON.stringify(selected));
      window.location.href = "checkout.html";
    });
  }
});

// Initial load
updateCart("none");
