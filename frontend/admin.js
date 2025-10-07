  //session
  document.addEventListener("DOMContentLoaded", () => {

    const sellerNameEl = document.getElementById("sellername");
    const sellerUsernameEl = document.getElementById("sellerusername");

    fetch("../backend/session.php", {
      method: "GET",
      credentials: "include"
    })
      .then(res => res.json())
      .then(data => {
        if (data.loggedIn && data.type === "seller") {
          sellerNameEl.textContent = data.sellername || "Admin";
          sellerUsernameEl.textContent = data.sellerusername || "";
        } 
      })  
      .catch(err => {
        console.error("Session check failed:", err);
        window.location.href = "adminlogin.html";
      });
  });



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
      method: "POST",
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

// DELETE product
function deleteProduct(id) {
  if (!id) return alert("Missing product ID");

  showConfirm("Are you sure you want to delete this product?", async () => {
    try {
      const res = await fetch("http://localhost/rytm/backend/admin.php?type=products", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id })
      });

      const data = await res.json();
      if (data.success) {
        alert("Product deleted!");
        fetchProducts(); 
      } else {
        alert("Delete failed: " + (data.error || "Unknown error"));
      }
    } catch (err) {
      console.error("Delete error:", err);
      alert("Delete request failed");
    }
  });
}

async function deleteOrder(orderId) {
  if (!orderId) return;
  if (!confirm(`Are you sure you want to delete Order #${orderId}?`)) return;

  try {
    const res = await fetch("http://localhost/rytm/backend/admin.php?type=orders", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: orderId })
    });

    const data = await res.json();
    if (data.success) {
      alert(`Order #${orderId} deleted successfully.`);
      fetchOrders(); // refresh list
    } else {
      alert("Delete failed: " + (data.error || "Unknown error"));
    }
  } catch (err) {
    console.error("Delete order error:", err);
    alert("Network error deleting order");
  }
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

/*Orders*/

async function fetchOrders() {
  try {
    const res = await fetch("http://localhost/rytm/backend/admin.php?type=orders");
    if (!res.ok) throw new Error("Network response not OK");
    const rows = await res.json();

    // Group items by OrderID
    const grouped = {};
    rows.forEach(r => {
      if (!grouped[r.OrderID]) {
        grouped[r.OrderID] = {
          OrderID: r.OrderID,
          CustomerID: r.CustomerID,
          CustomerName: `${r.FirstName} ${r.LastName}`,
          ContactNo: r.ContactNo,
          Email: r.Email,
          CustomerAddress: r.CustomerAddress,
          OrderDate: r.OrderDate,
          DeliveryDate: r.DeliveryDate,
          OrderStatus: r.OrderStatus,
          TotalAmount: r.TotalAmount,
          TotalOrderQty: r.TotalOrderQty,
          items: []
        };
      }
      grouped[r.OrderID].items.push({
        ProductID: r.ProductID,
        ProductName: r.ProductName,
        ProdOrdQty: r.ProdOrdQty,
        ProductPrice: r.ProductPrice
      });
    });

    const orders = Object.values(grouped);

    const tbody = document.getElementById("ordersTableBody");
    const empty = document.getElementById("ordersEmpty");
    tbody.innerHTML = "";

    if (!orders || orders.length === 0) {
      empty.style.display = "block";
      return;
    }
    empty.style.display = "none";

    orders.forEach(order => {
      const tr = document.createElement("tr");

      // Build items list
      const itemsHtml = order.items.map(it => {
        return `<div style="margin-bottom:4px;">
                  <strong>${it.ProductName}</strong> (x${it.ProdOrdQty}) 
                  <span style="font-size:12px;color:#777;">₱${it.ProductPrice}</span>
                </div>`;
      }).join("");

      // Build action buttons
      let actionHtml = "";
      const status = (order.OrderStatus || "").toLowerCase();

      if (status === "pending") {
        actionHtml = `<button class="btn btn-edit" onclick="updateOrderStatus(${order.OrderID}, 'Accepted')">Accept</button>`;
      } else if (status === "accepted") {
        actionHtml = `<button class="btn" onclick="updateOrderStatus(${order.OrderID}, 'Processing')">Set Processing</button>`;
      } else if (status === "processing") {
        actionHtml = `<button class="btn" onclick="updateOrderStatus(${order.OrderID}, 'Shipping')">Set Shipping</button>`;
      } else if (status === "shipping") {
        actionHtml = `<button class="btn" onclick="updateOrderStatus(${order.OrderID}, 'Delivered')">Set Delivered</button>`;
      } else if (status === "delivered") {
        actionHtml = `<span style="color:green;font-weight:bold">Delivered</span>`;
      } else if (status === "received") {
        actionHtml = `<span style="color:blue;font-weight:bold">Received</span>`;
      } else {
        actionHtml = `<span style="color:#999;">Unknown</span>`;
      }

      tr.innerHTML = `
        <td>${order.OrderID}</td>
        <td>
          ${order.CustomerName}<br>
          <small>${order.Email}<br>${order.ContactNo}<br>${order.CustomerAddress}</small>
        </td>
        <td>${order.OrderDate}</td>
        <td>${order.TotalOrderQty}</td>
        <td>₱${order.TotalAmount}</td>
        <td>${order.OrderStatus || ''}</td>
        <td>${order.DeliveryDate || ''}</td>
        <td style="text-align:left;max-width:300px;">${itemsHtml}</td>
        <td>${actionHtml}</td>
      `;
      tbody.insertBefore(tr, tbody.firstChild);
    });

  } catch (err) {
    console.error("Error fetching orders:", err);
    alert("Failed to load orders: " + err.message);
  }
}

// show/hide message modal
function showMsg(title, body) {
  const modal = document.getElementById("msgModal");
  document.getElementById("msgTitle").textContent = title || "";
  document.getElementById("msgBody").textContent = body || "";
  modal.classList.remove("hidden");
  modal.setAttribute("aria-hidden", "false");
}
function hideMsg() {
  const modal = document.getElementById("msgModal");
  modal.classList.add("hidden");
  modal.setAttribute("aria-hidden", "true");
}

// update order status (uses PUT JSON)
async function updateOrderStatus(orderId, newStatus) {
  if (!orderId || !newStatus) return;
  if (!confirm(`Change order ${orderId} status to "${newStatus}"?`)) return;

  try {
    const res = await fetch("http://localhost/rytm/backend/admin.php?type=orders", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ OrderID: orderId, OrderStatus: newStatus })
    });

    // Try to parse JSON; if not JSON, show raw text
    let data;
    const text = await res.text();
    try {
      data = JSON.parse(text);
    } catch (parseErr) {
      // backend returned non-JSON content (HTML or error) — show it to user
      console.error("Non-JSON response updating order:", text);
      showMsg("Server response (not JSON)", text.substring(0, 200));
      return;
    }

    if (data && data.success) {
      showMsg("Success", `Order ${orderId} updated to "${newStatus}".`);
      fetchOrders();
    } else {
      const errMsg = (data && data.error) ? data.error : "Unknown error";
      console.error("Update error:", data);
      showMsg("Update failed", errMsg);
    }
  } catch (err) {
    console.error("Update order error:", err);
    showMsg("Network error", err.message || String(err));
  }
}

// When opening Orders section
function openOrdersSection() {
  loadSection("orders");
  fetchOrders();
}
window.openOrdersSection = openOrdersSection;



async function fetchUsers() {
  const tableBody = document.querySelector('#usersTableBody');
  tableBody.innerHTML = '';

  try {
    const res = await fetch('http://localhost/rytm/backend/admin.php?type=users');
    const data = await res.json();

    if (data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No users found</td></tr>`;
      return;
    }

    data.forEach(user => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${user.CustomerID}</td>
        <td>${user.Username}</td>
        <td>${user.FirstName} ${user.LastName}</td>
        <td>${user.Email}</td>
        <td>${user.ContactNo}</td>
        <td>${user.CustomerAddress}</td>
      `;
      tableBody.appendChild(tr);
    });
  } catch (err) {
    console.error('Failed to fetch users', err);
  }
}

