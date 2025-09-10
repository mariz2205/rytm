
  //session
  document.addEventListener("DOMContentLoaded", () => {
    fetch("../backend/session.php", {
      method: "GET",
      credentials: "include"
    })
      .then(res => res.json())

      .then(data => {
        if (data.loggedIn) {
          document.getElementById("sidebarName").textContent = data.fullname;
          document.getElementById("sidebarEmail").textContent = data.email;

          document.getElementById("logoutBtn").style.display = "block";
          document.getElementById("loginLink").style.display = "none";
        } else {
          document.getElementById("sidebarName").textContent = "Guest";
          document.getElementById("sidebarEmail").textContent = "Not Logged In";

          document.getElementById("logoutBtn").style.display = "none";
          document.getElementById("loginLink").style.display = "block";
        }
      })
      .catch(err => console.error("Session check error:", err));
  });



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

    // Loop products to product cards
    for (const p of products) {
      const card = document.createElement("div");
      card.className = "product-card";

      // product image
      const img = document.createElement("img");
      img.src = `../${p.image}`;
      img.alt = p.name;

      // product name
      const name = document.createElement("h3");
      name.textContent = p.name;

      // product price
      const price = document.createElement("p");
      price.textContent = `₱${Number(p.price).toLocaleString("en-PH", { minimumFractionDigits: 2 })}`;

      // actions (buttons container)
      const btnGroup = document.createElement("div");
      btnGroup.className = "product-actions";

      // view button
      const viewBtn = document.createElement("button");
      viewBtn.textContent = "View";
      viewBtn.className = "view-btn";
      viewBtn.addEventListener("click", () => {
        openModal(
          `../${p.image}`,
          p.name,
          Number(p.price).toLocaleString("en-PH", { minimumFractionDigits: 2 }),
          p.description || "No description available."
        );
      });

    // add to cart button
      const addBtn = document.createElement("button");
      addBtn.textContent = "Add to Cart";
      addBtn.className = "cart-btn";
      addBtn.addEventListener("click", async () => {
        await updateCart("add", p.id, 1);
        window.location.href = "cart.html"; // ✅ redirect to Cart page
      });

      // buy now button
      const buyBtn = document.createElement("button");
      buyBtn.textContent = "Buy Now";
      buyBtn.className = "buy-btn";
      buyBtn.addEventListener("click", async () => {
        await updateCart("add", p.id, 1); // add item first
        window.location.href = "checkout.php"; // go to checkout page
      });


      // put buttons inside button group
      btnGroup.appendChild(viewBtn);
      btnGroup.appendChild(addBtn);
      btnGroup.appendChild(buyBtn);

      // assemble card
      card.appendChild(img);
      card.appendChild(name);
      card.appendChild(price);
      card.appendChild(btnGroup);

      container.appendChild(card);
    }
  }

  // Open modal (product info, no buttons)
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


  //for LOGOUT button
  document.addEventListener("DOMContentLoaded", () => {
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", () => {
        fetch("../backend/logout.php", {
          method: "GET",
          credentials: "include"
        })
          .then(res => res.json())

          .then(data => {
            if (data.success) {
              window.location.href = data.redirect; //go back to shop or login
            }
          })
          .catch(err => console.error("Logout error:", err));
      });
    }
  });

