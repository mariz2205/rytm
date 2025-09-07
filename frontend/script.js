// When the page loads, fetch products
document.addEventListener("DOMContentLoaded", () => {
  fetchProducts();

  // Filter by category
  const categorySelect = document.getElementById("category-select");
  if (categorySelect) {
    categorySelect.addEventListener("change", () => {
      const selectedCategory = categorySelect.value;
      fetchProducts(selectedCategory);
    });
  }
});

// Fetch PRODUCTS from backend/products.php
function fetchProducts(category = "all") {
  fetch("http://localhost/rytm/backend/products.php?category=" + category)
    .then(res => res.json())
    .then(data => {
      console.log("Fetched:", data);
      displayProducts(data);
    })
    .catch(err => console.error("Fetch error:", err));
  }


// Render products in the container
function displayProducts(products) {
  const container = document.getElementById("products-container");
  if (!container) {
    console.error("No #products-container found in HTML!");
    return;
  }

  container.replaceChildren(); 

  if (!Array.isArray(products) || products.length === 0) {
    container.textContent = "No products found.";
    return;
  }

  //Loop products to product cards
  for (const p of products) {
    const card = document.createElement("div");
    card.className = "product-card";

    card.innerHTML = `
      <img src="../${p.image}" alt="${p.name}">
      <h3>${p.name}</h3>
      <p>₱${Number(p.price).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</p>
      <button>View</button>
    `;

    container.appendChild(card);
  }
}


// Open modal
function openModal(image, title, price, description) {
  const modal = document.getElementById("productModal");
  document.getElementById("modalImage").src = image;
  document.getElementById("modalTitle").textContent = title;
  document.getElementById("modalPrice").textContent = "₱" + price;
  document.getElementById("modalDescription").textContent = description;
  modal.style.display = "block";
}

// Close modal when clicked outside
window.onclick = function (event) {
  const modal = document.getElementById("productModal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
};

// Handle login form via AJAX
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(loginForm);

      fetch("../backend/login.php", {  // FIXED PATH
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          document.getElementById("error").textContent = data.message;
        }
      })
      .catch(err => {
        console.error("Login error:", err);
        document.getElementById("error").textContent = "Server error!";
      });
    });
  }
});


//Handle signup form via AJAX
document.addEventListener("DOMContentLoaded", () => {
  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(signupForm);

      fetch("../backend/signup.php", {  //FIXED PATH
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect; //go to index/checkout
        } else {
          document.getElementById("signupError").textContent = data.message;
        }
      })
      .catch(err => {
        console.error("Signup error:", err);
        document.getElementById("signupError").textContent = "Server error!";
      });
    });
  }
});

//for logout button
document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      fetch("../backend/logout.php")
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect; // go back to shop or login
          }
        })
        .catch(err => console.error("Logout error:", err));
    });
  }
});
