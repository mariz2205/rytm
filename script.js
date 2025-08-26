// ===== PRODUCTS =====
const products = [
  { id: 1, name: "Takamine GD37CE 12-String Dreadnought", price: 3200, category: "acoustic", img: "https://i.pinimg.com/736x/eb/37/62/eb3762f9dca0ef1568dc412715a844f4.jpg", description: "A rich, full-sounding 12-string dreadnought ideal for experienced players." },
  { id: 2, name: "Fender CD-60S- Black", price: 3500, category: "acoustic", img: "https://i.pinimg.com/736x/e4/25/b0/e425b055f045be26d9e700181f971297.jpg", description: "A classic black Fender acoustic—great for both beginners and intermediate players." },
  { id: 3, name: "Oscar Schmidt OG1P-A 3/4 Dreadnought ", price: 2900, category: "acoustic", img: "https://i.pinimg.com/736x/11/46/08/114608278bfeee0e660851db3f72426f.jpg", description: "Compact dreadnought design, ideal for younger players or travelers." },
  { id: 4, name: "Dimavery ST-312 Electric Guitar ", price: 5800, category: "electric", img: "https://i.pinimg.com/736x/f8/38/7a/f8387afc0a8533057f5df80d5fd00ecc.jpg", description: "A sleek, beginner-friendly electric guitar with a classic Strat shape." },
  { id: 5, name: "Fender Artist Series Eric Clapton Stratocaster", price: 6900, category: "electric", img: "https://i.pinimg.com/736x/4c/a7/81/4ca781851ea4e2a2137551ee11de36d3.jpg", description: "A premium Stratocaster with Clapton's signature sound and feel." },
  { id: 6, name: "JS-380 Roasted Poplar Body", price: 7500, category: "electric", img: "https://i.pinimg.com/736x/12/a1/f4/12a1f4694962bdc30f1aff5e43fa4c62.jpg", description: "Bold design and premium build for modern electric guitarists." },
  { id: 7, name: "Ukulele 21 inch (Soprano) - Multi", price: 100, category: "ukulele", img: "https://i.pinimg.com/736x/4c/cd/78/4ccd7867a85f6e98f3e1cef45c60311a.jpg", description: "Colorful soprano ukulele—perfect for casual strumming or learning." },
  { id: 8, name: "Kala Waterman Soprano SWG BL", price: 250, category: "ukulele", img: "https://i.pinimg.com/736x/6f/34/b9/6f34b9107c6a57f9f02eca97437d39ee.jpg", description: "Water-resistant, fun, and travel-friendly ukulele." },
  { id: 9, name: "Kala Ukadelic UK-BAMBOO", price: 150, category: "ukulele", img: "https://i.pinimg.com/736x/5f/6a/0a/5f6a0ad6438d0d2c802e10db9f31a842.jpg", description: "Eco-friendly ukulele with a unique bamboo look." },
  { id: 10, name: "Zinc Alloy Shark", price: 350, category: "capo", img: "https://i.pinimg.com/736x/e1/1e/85/e11e857ec8d0776a38163bf8e8ed5866.jpg", description: "Eye-catching shark-shaped capo with strong clamp." },
  { id: 11, name: "Dunlop Guitar Capo", price: 350, category: "capo", img: "https://i.pinimg.com/736x/cd/bb/dd/cdbbddfec2b61a942a7029637bb9d9de.jpg", description: "Reliable capo from Dunlop—simple and effective." },
  { id: 12, name: "Tanglewood Speedbar", price: 350, category: "capo", img: "https://i.pinimg.com/736x/9b/49/22/9b49226927efc4e010dd78de591e32da.jpg", description: "Quick-release capo that’s easy to adjust on stage." },
  { id: 13, name: "Celluloid Red Pearl", price: 350, category: "pick", img: "https://i.pinimg.com/736x/05/61/c9/0561c9e3bdf25d34cdae3969b1d32948.jpg", description: "Classic red pearl pick for smooth tone and grip." },
  { id: 14, name: "Exotic Plectrums Delrin", price: 350, category: "pick", img: "https://i.pinimg.com/736x/b0/1e/36/b01e36a7fef123443f3eda6ede121ea0.jpg", description: "Durable and sleek plectrum made from Delrin." },
  { id: 15, name: "Petroglyph Spiral Tribal", price: 350, category: "pick", img: "https://i.pinimg.com/736x/96/ef/7f/96ef7f1728d60ff8a878716261de2a78.jpg", description: "Stylish tribal design with solid tone performance." }
];

const productsContainer = document.getElementById("products-container");
const cartPanel = document.getElementById("cart-panel");
const cartItems = document.querySelector(".cart-table") || document.getElementById("cart-items");
const totalEl = document.getElementById("cart-total");
const categorySelect = document.getElementById("category-select");
const modal = document.getElementById("productModal");

let cartView = []; //{id, name, price, qty}

//RENDER PRODUCTS
function renderProducts(selectedCategory = "all") {
  if (!productsContainer) return;
  productsContainer.innerHTML = "";

  const filtered = products.filter(
    (p) => selectedCategory === "all" || p.category === selectedCategory
  );

  filtered.forEach((product) => {
    const card = document.createElement("div");
    card.className = "product-card";
    card.innerHTML = `
      <img src="${product.img}" alt="${product.name}">
      <h3>${product.name}</h3>
      <p>₱${product.price}</p>
      <button type="button" onclick="showDetails(${product.id})">View Details</button>
      <button type="button" onclick="addToCart(${product.id})">Add to Cart</button>
      <button type="button" onclick="buyNow(${product.id},1)">Buy Now</button>
    `;
    productsContainer.appendChild(card);
  });
}

//VIEW DETAILS
function showDetails(id) {
  const product = products.find((p) => p.id === id);
  if (!product) return;
  document.getElementById("modalTitle").textContent = product.name;
  document.getElementById("modalPrice").textContent = `₱${product.price}`;
  document.getElementById("modalDescription").textContent = product.description;
  document.getElementById("modalImage").src = product.img;
  modal.style.display = "block";
}
function closeDetails() { modal.style.display = "none"; }
window.addEventListener("click", (e) => { if (e.target === modal) closeDetails(); });
window.addEventListener("keydown", (e) => { if (e.key === "Escape") closeDetails(); });

//CART
async function loadCart() {
  try {
    const res = await fetch("cart.php?action=view");
    const html = await res.text();
    if (cartItems) cartItems.innerHTML = html;

    cartView = [];
    document.querySelectorAll("li[data-id]").forEach(li => {
      const id = parseInt(li.dataset.id);
      const name = li.dataset.name;
      const price = parseFloat(li.dataset.price);
      const qty = parseInt(li.dataset.qty) || 1;
      cartView.push({ id, name, price, qty });

      //attach plus/minus/remove listeners dynamically
      li.querySelectorAll("button[data-action]").forEach(btn => {
        const action = btn.dataset.action;
        btn.onclick = () => handleCartAction(id, action);
      });
    });

    updateCartTotal();
  } catch (err) {
    console.error("Failed to load cart:", err);
    if (cartItems) cartItems.innerHTML = "<p>Unable to load cart.</p>";
  }
}

async function handleCartAction(id, action) {
  const fd = new FormData();
  fd.append("id", id);

  if (action === "plus") {
    fd.append("action", "update");
    const currentQty = cartView.find(item => item.id === id)?.qty || 1;
    fd.append("qty", currentQty + 1);
  } else if (action === "minus") {
    fd.append("action", "update");
    const currentQty = cartView.find(item => item.id === id)?.qty || 1;
    fd.append("qty", Math.max(currentQty - 1, 1));
  } else if (action === "remove") {
    fd.append("action", "remove");

    //remove hidden form inputs from checkout form
    const qtyInput = document.getElementById("form-qty-" + id);
    const selInput = document.getElementById("form-sel-" + id);
    if (qtyInput) qtyInput.remove();
    if (selInput) selInput.remove();
  }

  await fetch("cart.php", { method: "POST", body: fd });
  await loadCart();
}


async function addToCart(id, qty = 1) {
  const fd = new FormData();
  fd.append("action", "add");
  fd.append("id", id);
  fd.append("qty", qty);
  await fetch("cart.php", { method: "POST", body: fd });
  await loadCart();
}

function toggleCartPanel() {
  cartPanel.style.display = cartPanel.style.display === "block" ? "none" : "block";
  if (cartPanel.style.display === "block") loadCart();
}

//TOTAL & SIDEBAR COUNT
function updateCartTotal() {
  let total = 0;
  cartView.forEach(item => total += item.price * item.qty);
  if (totalEl) totalEl.textContent = total.toFixed(2);

  const sidebarCount = document.getElementById("sidebar-cart-count");
  if (sidebarCount) sidebarCount.textContent = cartView.reduce((sum, item) => sum + item.qty, 0);
}

//CHECKOUT
function submitSelectedItems() {
  const form = document.getElementById("cart-checkout-form");
  const checkboxes = document.querySelectorAll(".checkout-item");
  checkboxes.forEach(chk => {
    const id = chk.value;
    const selInput = document.getElementById("form-sel-" + id);
    if (!chk.checked) selInput.remove();
  });
  form.submit();
}

//FILTER
if (categorySelect) {
  categorySelect.addEventListener("change", () => renderProducts(categorySelect.value));
}

//BUY NOW
function buyNow(id, qty = 1) {
  window.location.href = `checkout.php?id=${id}&qty=${qty}`;
}

//INIT
window.addToCart = addToCart;
window.buyNow = buyNow;
window.showDetails = showDetails;
window.closeDetails = closeDetails;
window.toggleCartPanel = toggleCartPanel;
window.submitSelectedItems = submitSelectedItems;

document.addEventListener("DOMContentLoaded", () => {
  renderProducts();
  loadCart();
});
