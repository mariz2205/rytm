// admin.js
// Load products.js first in admin.html
// products is a shared array with index.html

let editingProductId = null;

function renderAdminProducts() {
  const tbody = document.querySelector("#productsTable tbody");
  tbody.innerHTML = "";
  products.forEach(prod => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${prod.id}</td>
      <td>${prod.name}</td>
      <td>₱${prod.price.toFixed(2)}</td>
      <td>${prod.stock}</td>
      <td>
        <button class="btn btn-edit" onclick="openProductModal(${prod.id})">Edit</button>
        <button class="btn btn-delete" onclick="deleteProduct(${prod.id})">Delete</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// ==== Modal Handling ====
function openProductModal(id = null) {
  const modal = document.getElementById("productModal");
  const form = document.getElementById("productForm");
  modal.style.display = "flex";

  if (id) {
    // Edit existing product
    const prod = products.find(p => p.id === id);
    if (!prod) return;
    editingProductId = id;
    form.name.value = prod.name;
    form.price.value = prod.price;
    form.stock.value = prod.stock;
  } else {
    // New product
    editingProductId = null;
    form.reset();
  }
}

function closeProductModal() {
  const modal = document.getElementById("productModal");
  modal.style.display = "none";
}

// ==== Save Product (Add or Edit) ====
function saveProduct(event) {
  event.preventDefault();
  const form = document.getElementById("productForm");
  const name = form.name.value.trim();
  const price = parseFloat(form.price.value);
  const stock = parseInt(form.stock.value);

  if (!name || isNaN(price) || isNaN(stock)) {
    alert("Please fill all fields correctly.");
    return;
  }

  if (editingProductId) {
    // Update product
    const prod = products.find(p => p.id === editingProductId);
    prod.name = name;
    prod.price = price;
    prod.stock = stock;
  } else {
    // Add new product
    const newProd = {
      id: Date.now(),
      name,
      price,
      stock,
      category: "misc",
      description: "New product"
    };
    products.push(newProd);
  }

  saveProducts();
  renderAdminProducts();
  closeProductModal();
}

function deleteProduct(id) {
  if (!confirm("Are you sure?")) return;
  products = products.filter(p => p.id !== id);
  saveProducts();
  renderAdminProducts();
}

// Initialize on load
document.addEventListener("DOMContentLoaded", () => {
  renderAdminProducts();

  // Modal close handler
  document.getElementById("closeModalBtn").addEventListener("click", closeProductModal);
  document.getElementById("productForm").addEventListener("submit", saveProduct);

  // Close modal if user clicks outside
  window.addEventListener("click", (e) => {
    const modal = document.getElementById("productModal");
    if (e.target === modal) closeProductModal();
  });
});
