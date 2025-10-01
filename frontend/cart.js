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
    const info = document.createElement("div");
    info.className = "cart-info";
    info.textContent = `${item.name}`;

    const priceSpan = document.createElement("div");
    priceSpan.className = "product-price";
    priceSpan.textContent =`₱${item.price}`;

    const subtotalSpan = document.createElement("div");
    subtotalSpan.className = "product-subtotal";
    subtotalSpan.textContent = `Subtotal: ₱${item.subtotal}`;

    const qtyControls = document.createElement("div");
    qtyControls.className = "qty-controls";

    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.addEventListener("click", () => {
      if (item.qty > 1) updateCart("update", item.id, item.qty - 1);
    });

    const qtyDisplay = document.createElement("span");
    qtyDisplay.textContent = item.qty;
    qtyDisplay.className = "qty-display";

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.addEventListener("click", () => {
      updateCart("update", item.id, item.qty + 1);
    });

    qtyControls.appendChild(minusBtn);
    qtyControls.appendChild(qtyDisplay);
    qtyControls.appendChild(plusBtn);

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "Remove";
    removeBtn.addEventListener("click", () => updateCart("remove", item.id));

    li.appendChild(checkbox);
    li.appendChild(img);
    li.appendChild(info);
    li.appendChild(qtyControls);
    li.appendChild(removeBtn);
    info.appendChild(priceSpan)
    info.appendChild(subtotalSpan);



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

  // Proceed to Checkout button
  let checkoutBtn = document.getElementById("checkoutBtn");
  if (!checkoutBtn) {
    checkoutBtn = document.createElement("button");
    checkoutBtn.id = "checkoutBtn";
    checkoutBtn.className = "btn";
    checkoutBtn.textContent = "Proceed to Checkout";
    cartList.parentElement.appendChild(checkoutBtn);
  }

  // (Re)attach event listener safely
  checkoutBtn.onclick = () => {
    const selected = {};
    document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
        const li = cb.parentElement;
        const qtyText = li.querySelector("span").textContent.match(/× (\d+)/);
        const qty = qtyText ? parseInt(qtyText[1], 10) : 1;
        selected[cb.dataset.id] = qty;
    });
    sessionStorage.setItem("checkoutSelected", JSON.stringify(selected));
    window.location.href = "checkout.html";
};

  updateCartTotal();
}

// Total calculation
function updateCartTotal() {
  const totalEl = document.getElementById("cart-total");
  if (!totalEl) return;

  let grand = 0;

  document.querySelectorAll("#cart-items .checkout-item:checked").forEach(cb => {
    const subtotalEl = cb.parentElement.querySelector(".product-subtotal");
    if (subtotalEl) {
      const match = subtotalEl.textContent.match(/₱([\d.,]+)/);
      if (match) {
        grand += parseFloat(match[1].replace(/,/g, ""));
      }
    }
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
