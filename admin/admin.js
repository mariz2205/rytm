// Sidebar Navigation
const navLinks = document.querySelectorAll(".nav-link");
const sections = document.querySelectorAll(".section");
const pageTitle = document.getElementById("pageTitle");

navLinks.forEach(link => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    navLinks.forEach(l => l.classList.remove("active"));
    sections.forEach(s => s.classList.remove("active"));
    link.classList.add("active");
    const targetId = link.getAttribute("href").substring(1);
    document.getElementById(targetId).classList.add("active");
    pageTitle.textContent = link.textContent.replace(/[^a-zA-Z]/g, "").trim();
  });
});

let products = [];
let editingProduct = null;

// Switch to products section
function loadSection(section) {
  document.querySelectorAll(".section").forEach(sec => sec.classList.remove("active"));
  document.getElementById(section + "Section").classList.add("active");
  if (section === "products") fetchProducts();
}

// Fetch products from backend
async function fetchProducts() {
  try {
    const res = await fetch("http://localhost/rytm/backend/admin.php");
    products = await res.json();

    const tbody = document.getElementById("productsTableBody");
    tbody.innerHTML = "";

    products.forEach(prod => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${prod.ProductID}</td>
        <td>${prod.ProductName}</td>
        <td>${prod.ProductDescription}</td>
        <td><img src="../img/products/${prod.Image}" width="50" onerror="this.src='../img/no-image.png'"></td>
        <td>${prod.Category}</td>
        <td>${prod.Stock}</td>
        <td>${prod.ProductPrice}</td>
        <td>${prod.SellerID}</td>
        <td>
          <button class="btn btn-edit" onclick="openModal(${prod.ProductID})">Edit</button>
          <button class="btn btn-delete" onclick="deleteProduct(${prod.ProductID})">Delete</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } catch (err) {
    console.error("Error fetching products:", err);
  }
}

// Open Modal (Add/Edit)
function openModal(productId = null) {
  const modal = document.getElementById("productModal");
  modal.classList.remove("hidden");

  if (productId) {
    // Find product from loaded list
    const product = products.find(p => p.ProductID == productId);
    document.getElementById("modalTitle").textContent = "Edit Product";
    document.getElementById("prodId").value = product.ProductID;
    document.getElementById("prodName").value = product.ProductName;
    document.getElementById("prodDesc").value = product.ProductDescription;
    document.getElementById("prodImage").value = product.Image;
    document.getElementById("prodCategory").value = product.Category;
    document.getElementById("prodStock").value = product.Stock;
    document.getElementById("prodPrice").value = product.ProductPrice;
    document.getElementById("prodSeller").value = product.SellerID;
    editingProduct = product;

    // Show preview if image exists
    const preview = document.getElementById("imagePreview");
    if (product.Image) {
      preview.src = "../img/" + product.Image;
      preview.style.display = "block";
    } else {
      preview.style.display = "none";
    }
  } else {
    document.getElementById("modalTitle").textContent = "Add Product";
    document.getElementById("productForm").reset();
    document.getElementById("prodId").value = "";
    document.getElementById("imagePreview").style.display = "none";
    editingProduct = null;
  }
}

// Close Modal
function closeModal() {
  document.getElementById("productModal").classList.add("hidden");
}

// Show custom confirm modal
function showConfirm(message, onYes) {
  const modal = document.getElementById("confirmModal");
  const msg = document.getElementById("confirmMessage");
  const yesBtn = document.getElementById("confirmYes");
  const noBtn = document.getElementById("confirmNo");

  msg.textContent = message;
  modal.classList.remove("hidden");

  // Clean old listeners
  const newYesBtn = yesBtn.cloneNode(true);
  yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);

  // Yes button
  newYesBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    onYes();
  });

  // No button
  noBtn.onclick = () => {
    modal.classList.add("hidden");
  };
}


// Save Product (Add / Edit)
document.getElementById("productForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData();
  formData.append("ProductID", document.getElementById("prodId").value);
  formData.append("ProductName", document.getElementById("prodName").value.trim());
  formData.append("ProductDescription", document.getElementById("prodDesc").value.trim());
  formData.append("Category", document.getElementById("prodCategory").value.trim());
  formData.append("Stock", document.getElementById("prodStock").value);
  formData.append("ProductPrice", document.getElementById("prodPrice").value);
  formData.append("SellerID", document.getElementById("prodSeller").value);

  // If new file chosen, append it
  const fileInput = document.getElementById("prodImageFile");
  if (fileInput.files.length > 0) {
    formData.append("ImageFile", fileInput.files[0]);
  } else {
    formData.append("Image", document.getElementById("prodImage").value);
  }

  try {
    let res;
    if (editingProduct) {
      formData.append("_method", "PUT");
    }

    res = await fetch("http://localhost/rytm/backend/admin.php", {
      method: "POST", // always POST (PHP checks _method)
      body: formData
    });

    const result = await res.json();

    if (result.success) {
      closeModal();
      fetchProducts();
      alert(editingProduct ? "Product updated successfully!" : "Product added successfully!");
    } else {
      throw new Error(result.error || "Unknown error");
    }
  } catch (err) {
    console.error("Error saving product:", err);
    alert("Save failed: " + err.message);
  }
});


// Delete product
// Delete product using custom confirm modal
function deleteProduct(id) {
  if (!id) return alert("Missing product ID");

  showConfirm("Are you sure you want to delete this product?", async () => {
    try {
      const res = await fetch("http://localhost/rytm/backend/admin.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id })
      });

      const data = await res.json();
      if (data.success) {
        alert("Product deleted!");
        fetchProducts(); // reload table
      } else {
        alert("Delete failed: " + (data.error || "Unknown error"));
      }
    } catch (err) {
      console.error("Delete error:", err);
      alert("Delete request failed");
    }
  });
}

// Hook Add button
document.getElementById("addProductBtn").addEventListener("click", () => openModal());

// Image preview
document.getElementById("prodImageFile").addEventListener("change", function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById("imagePreview");
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(file);
    document.getElementById("prodImage").value = file.name;
  } else {
    document.getElementById("imagePreview").style.display = "none";
    document.getElementById("prodImage").value = "";
  }
});

// Initial load
fetchProducts();
