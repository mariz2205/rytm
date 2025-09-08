// Handle LOGIN form via AJAX
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(loginForm);

    fetch("../backend/login.php", {
      method: "POST",
      body: formData,
      credentials: "include" // 🔑 important for sessions
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect || "index.html";
        } else {
          document.getElementById("loginMsg").textContent = data.message;
        }
      })
      .catch(err => console.error("Login error:", err));
  });
}
