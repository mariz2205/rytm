// Handle LOGIN form via AJAX
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(loginForm);

    fetch("../backend/adminlogin.php", {
      method: "POST",
      body: formData,
      credentials: "include"
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect || "admin.html";
        } else {
          const loginMessage = document.getElementById("loginMessage");
            loginMessage.textContent = data.message;
            loginMessage.style.display = "block";

        }
      })
      .catch(err => console.error("Login error:", err));
  });
}